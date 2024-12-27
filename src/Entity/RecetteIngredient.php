<?php

namespace App\Entity;

use App\Repository\RecetteIngredientRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecetteIngredientRepository::class)]
class RecetteIngredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'recetteIngredients')]
    private ?Recette $recetteId = null;

    #[ORM\ManyToOne(inversedBy: 'recetteIngredients')]
    private ?Ingredient $ingredientId = null;

    #[ORM\Column]
    private ?int $quantite = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecetteId(): ?Recette
    {
        return $this->recetteId;
    }

    public function setRecetteId(?Recette $recetteId): static
    {
        $this->recetteId = $recetteId;

        return $this;
    }

    public function getIngredientId(): ?Ingredient
    {
        return $this->ingredientId;
    }

    public function setIngredientId(?Ingredient $ingredientId): static
    {
        $this->ingredientId = $ingredientId;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }
}
