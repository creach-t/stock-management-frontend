<?php

namespace App\Model;

class Product
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $description = null;
    private ?float $price = null;
    private ?int $quantity = null;
    private ?string $sku = null;
    private ?int $categoryId = null;
    private ?string $categoryName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): self
    {
        $this->sku = $sku;
        return $this;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function setCategoryId(?int $categoryId): self
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function setCategoryName(?string $categoryName): self
    {
        $this->categoryName = $categoryName;
        return $this;
    }

    /**
     * Indique si le produit est à faible stock (moins de 10 unités)
     */
    public function isLowStock(): bool
    {
        return $this->quantity !== null && $this->quantity < 10;
    }

    /**
     * Crée une instance de Product à partir d'un tableau de données
     */
    public static function fromArray(array $data): self
    {
        $product = new self();
        
        if (isset($data['id'])) {
            $product->setId($data['id']);
        }
        
        if (isset($data['name'])) {
            $product->setName($data['name']);
        }
        
        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        
        if (isset($data['price'])) {
            $product->setPrice((float) $data['price']);
        }
        
        if (isset($data['quantity'])) {
            $product->setQuantity((int) $data['quantity']);
        }
        
        if (isset($data['sku'])) {
            $product->setSku($data['sku']);
        }
        
        if (isset($data['categoryId'])) {
            $product->setCategoryId((int) $data['categoryId']);
        }
        
        if (isset($data['category']) && isset($data['category']['name'])) {
            $product->setCategoryName($data['category']['name']);
        }
        
        return $product;
    }

    /**
     * Convertit l'instance en tableau pour l'API
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'price' => $this->getPrice(),
            'quantity' => $this->getQuantity(),
            'sku' => $this->getSku(),
            'categoryId' => $this->getCategoryId(),
        ];
        
        if ($this->getId() !== null) {
            $data['id'] = $this->getId();
        }
        
        return $data;
    }
}