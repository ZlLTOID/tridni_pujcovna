<?php

namespace App\Entity;

use App\Repository\TridaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TridaRepository::class)]
#[ORM\Table(name: 'trida')]
class Trida
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private string $nazev = '';

    #[ORM\ManyToOne(targetEntity: Ucitel::class, inversedBy: 'tridy')]
    #[ORM\JoinColumn(name: 'ucitel_id', nullable: false)]
    private Ucitel $ucitel;

    /** @var Collection<int, Zak> */
    #[ORM\OneToMany(mappedBy: 'trida', targetEntity: Zak::class, orphanRemoval: true)]
    private Collection $zaci;

    /** @var Collection<int, Vec> */
    #[ORM\OneToMany(mappedBy: 'trida', targetEntity: Vec::class, orphanRemoval: true)]
    private Collection $veci;

    public function __construct()
    {
        $this->zaci = new ArrayCollection();
        $this->veci = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNazev(): string
    {
        return $this->nazev;
    }

    public function setNazev(string $nazev): self
    {
        $this->nazev = $nazev;

        return $this;
    }

    public function getUcitel(): Ucitel
    {
        return $this->ucitel;
    }

    public function setUcitel(Ucitel $ucitel): self
    {
        $this->ucitel = $ucitel;

        return $this;
    }

    /** @return Collection<int, Zak> */
    public function getZaci(): Collection
    {
        return $this->zaci;
    }

    /** @return Collection<int, Vec> */
    public function getVeci(): Collection
    {
        return $this->veci;
    }

    public function getPocetZaku(): int
    {
        return $this->zaci->count();
    }

    public function getPocetVeci(): int
    {
        return $this->veci->filter(fn (Vec $vec) => !$vec->isSmazano())->count();
    }

    public function ucitelCeleJmeno(): string
    {
        return $this->ucitel->celeJmeno();
    }
}
