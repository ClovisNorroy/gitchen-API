<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: "menu_id")]
    private Collection $user_id;

    #[ORM\Column]
    private ?bool $is_locked = false;

    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: MenuDay::class, orphanRemoval: true, cascade: ["persist"])]
    private Collection $menuDays;

    public function __construct()
    {
        $this->user_id = new ArrayCollection();
        $this->menuDays = new ArrayCollection();
        for($i = 1 ; $i <=14 ; $i++){
            $menuDay = new MenuDay($i);
            $menuDay->setMenu($this);
            $this->menuDays->add($menuDay);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUserId(): Collection
    {
        return $this->user_id;
    }

    public function addUserId(User $userId): static
    {
        if (!$this->user_id->contains($userId)) {
            $this->user_id->add($userId);
        }

        return $this;
    }

    public function removeUserId(User $userId): static
    {
        $this->user_id->removeElement($userId);

        return $this;
    }

    public function isIsLocked(): ?bool
    {
        return $this->is_locked;
    }

    public function setIsLocked(bool $is_locked): static
    {
        $this->is_locked = $is_locked;

        return $this;
    }

    /**
     * @return Collection<int, MenuDay>
     */
    public function getMenuDays(): Collection
    {
        return $this->menuDays;
    }

    public function addMenuDay(MenuDay $menuDay): static
    {
        if (!$this->menuDays->contains($menuDay)) {
            $this->menuDays->add($menuDay);
            $menuDay->setMenu($this);
        }

        return $this;
    }

    public function removeMenuDay(MenuDay $menuDay): static
    {
        if ($this->menuDays->removeElement($menuDay)) {
            // set the owning side to null (unless already changed)
            if ($menuDay->getMenu() === $this) {
                $menuDay->setMenu(null);
            }
        }

        return $this;
    }
}
