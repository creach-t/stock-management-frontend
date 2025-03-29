<?php

namespace App\Model;

class StockUpdate
{
    const OPERATION_ADD = 'ADD';
    const OPERATION_REMOVE = 'REMOVE';
    const OPERATION_SET = 'SET';
    
    private ?int $productId = null;
    private ?int $quantityChange = null;
    private ?string $operationType = self::OPERATION_ADD;

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
        return $this->operationType;
    }

    public function setOperationType(?string $operationType): self
    {
        if (in_array($operationType, [self::OPERATION_ADD, self::OPERATION_REMOVE, self::OPERATION_SET])) {
            $this->operationType = $operationType;
        }
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
        ];
    }
    
    /**
     * Retourne la liste des types d'opérations disponibles
     */
    public static function getOperationTypes(): array
    {
        return [
            self::OPERATION_ADD => 'Ajouter',
            self::OPERATION_REMOVE => 'Retirer',
            self::OPERATION_SET => 'Définir',
        ];
    }
}