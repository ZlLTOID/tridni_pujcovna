<?php

namespace App\Entity;

use App\Repository\ZakRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ZakRepository::class)]
#[ORM\Table(name: 'zak')]
class Zak
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private string $jmeno = '';

    #[ORM\Column]
    private string $prijmeni = '';

    #[ORM\ManyToOne(targetEntity: Trida::class, inversedBy: 'zaci')]
    #[ORM\JoinColumn(name: 'trida_id', nullable: false)]
    private Trida $trida;

    /** @var Collection<int, Zapujceni> */
    #[ORM\OneToMany(mappedBy: 'zak', targetEntity: Zapujceni::class)]
    private Collection $zapujceni;

    public function __construct()
    {
        $this->zapujceni = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJmeno(): string
    {
        return $this->jmeno;
    }

    public function setJmeno(string $jmeno): self
    {
        $this->jmeno = $jmeno;

        return $this;
    }

    public function getPrijmeni(): string
    {
        return $this->prijmeni;
    }

    public function setPrijmeni(string $prijmeni): self
    {
        $this->prijmeni = $prijmeni;

        return $this;
    }

    public function getTrida(): Trida
    {
        return $this->trida;
    }

    public function setTrida(Trida $trida): self
    {
        $this->trida = $trida;

        return $this;
    }

    public function celeJmeno(): string
    {
        return $this->jmeno . ' ' . $this->prijmeni;
    }
}
