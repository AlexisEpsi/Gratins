<?php

namespace App\Entity;

use App\Repository\FavorisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavorisRepository::class)]
class Favoris
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'favoris')]
    private ?User $userId = null;

    #[ORM\ManyToOne(inversedBy: 'favoris')]
    private ?Recette $recetteId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
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
}
