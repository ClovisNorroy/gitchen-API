<?php

namespace App\Entity;

use App\Repository\MenuDayRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Config\Doctrine\Orm\EntityManagerConfig\EntityListeners\EntityConfig;

#[ORM\Entity(repositoryClass: MenuDayRepository::class)]
class MenuDay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $meal_number = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $meal = "";

    #[ORM\ManyToOne(inversedBy: 'menuDays')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Menu $menu = null;

    #[ORM\ManyToOne]
    private ?Recipe $recipe = null;

    public function __construct($mealNumber)
    {
        $this->meal_number = $mealNumber;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMealNumber(): ?int
    {
        return $this->meal_number;
    }

    public function setMealNumber(int $day_number): static
    {
        $this->meal_number = $day_number;

        return $this;
    }

    public function getMeal(): ?string
    {
        return $this->meal;
    }

    public function setMeal(?string $meal): static
    {
        $this->meal = $meal;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }

    public function getRecipe(): ?recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?recipe $recipe): static
    {
        $this->recipe = $recipe;

        return $this;
    }
}
