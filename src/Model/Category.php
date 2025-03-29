<?php

namespace App\Model;

class Category
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $description = null;

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

    /**
     * Crée une instance de Category à partir d'un tableau de données
     */
    public static function fromArray(array $data): self
    {
        $category = new self();
        
        if (isset($data['id'])) {
            $category->setId($data['id']);
        }
        
        if (isset($data['name'])) {
            $category->setName($data['name']);
        }
        
        if (isset($data['description'])) {
            $category->setDescription($data['description']);
        }
        
        return $category;
    }

    /**
     * Convertit l'instance en tableau pour l'API
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
        ];
        
        if ($this->getId() !== null) {
            $data['id'] = $this->getId();
        }
        
        return $data;
    }
}