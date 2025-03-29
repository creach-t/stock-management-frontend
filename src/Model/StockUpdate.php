<?php

namespace App\Model;

class StockUpdate
{
    // Constantes pour les types d'opération (correspondent exactement à l'enum Java OperationType dans StockUpdateDTO.java)
    const OPERATION_ADD = 'ADD';
    const OPERATION_REMOVE = 'REMOVE';
    const OPERATION_SET = 'SET';
    
    private ?int $productId = null;
    private ?int $quantityChange = null;
    private ?string $operationType = self::OPERATION_ADD;
    private ?string $notes = null;

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(?int $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    public function getQuantityChange(): ?int
    {
        return $this->quantityChange;
    }

    public function setQuantityChange(?int $quantityChange): self
    {
        $this->quantityChange = $quantityChange;
        return $this;
    }

    public function getOperationType(): ?string
    {
        // L'API Spring attend une valeur d'enum exacte (ex: ADD, REMOVE, SET)
        return strtoupper($this->operationType);
    }

    public function setOperationType(?string $operationType): self
    {
        if ($operationType && in_array(strtoupper($operationType), [self::OPERATION_ADD, self::OPERATION_REMOVE, self::OPERATION_SET])) {
            $this->operationType = strtoupper($operationType);
        }
        return $this;
    }
    
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * Crée une instance de StockUpdate à partir d'un tableau de données
     */
    public static function fromArray(array $data): self
    {
        $stockUpdate = new self();
        
        if (isset($data['productId'])) {
            $stockUpdate->setProductId((int) $data['productId']);
        }
        
        if (isset($data['quantityChange'])) {
            $stockUpdate->setQuantityChange((int) $data['quantityChange']);
        }
        
        if (isset($data['operationType'])) {
            $stockUpdate->setOperationType($data['operationType']);
        }
        
        if (isset($data['notes'])) {
            $stockUpdate->setNotes($data['notes']);
        }
        
        return $stockUpdate;
    }

    /**
     * Convertit l'instance en tableau pour l'API
     */
    public function toArray(): array
    {
        return [
            'productId' => $this->getProductId(),
            'quantityChange' => $this->getQuantityChange(),
            'operationType' => $this->getOperationType(),
            'notes' => $this->getNotes() ?? 'Mise à jour depuis l\'interface'
        ];
    }
    
    /**
     * Retourne la liste des types d'opérations disponibles pour l'affichage dans le formulaire
     * Les clés sont les valeurs techniques (ADD, REMOVE, SET) et les valeurs sont les libellés à afficher
     */
    public static function getOperationTypes(): array
    {
        return [
            self::OPERATION_ADD => 'ADD',
            self::OPERATION_REMOVE => 'REMOVE',
            self::OPERATION_SET => 'SET',
        ];
    }
}