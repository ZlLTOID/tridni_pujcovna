<?php

namespace App\Entity;

use App\Repository\VecRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VecRepository::class)]
#[ORM\Table(name: 'vec')]
class Vec
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private string $nazev = '';

    #[ORM\Column(nullable: true)]
    private ?string $foto = null;

    #[ORM\Column]
    private bool $zapujcena = false;

    #[ORM\Column(name: 'datum_smazani', nullable: true)]
    private ?\DateTimeImmutable $datumSmazani = null;

    #[ORM\ManyToOne(targetEntity: Trida::class, inversedBy: 'veci')]
    #[ORM\JoinColumn(name: 'trida_id', nullable: false)]
    private Trida $trida;

    /** @var Collection<int, Zapujceni> */
    #[ORM\OneToMany(mappedBy: 'vec', targetEntity: Zapujceni::class)]
    private Collection $zapujceni;

    public function __construct()
    {
        $this->zapujceni = new ArrayCollection();
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

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function setFoto(?string $foto): self
    {
        $this->foto = $foto;

        return $this;
    }

    public function isZapujcena(): bool
    {
        return $this->zapujcena;
    }

    public function jeZapujcena(): bool
    {
        return $this->isZapujcena();
    }

    public function setZapujcena(bool $zapujcena): self
    {
        $this->zapujcena = $zapujcena;

        return $this;
    }

    public function getDatumSmazani(): ?\DateTimeImmutable
    {
        return $this->datumSmazani;
    }

    public function isSmazano(): bool
    {
        return $this->datumSmazani !== null;
    }

    public function smazat(): void
    {
        $this->datumSmazani = new \DateTimeImmutable();
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

    public function getAktivniZapujceni(): ?Zapujceni
    {
        foreach ($this->zapujceni as $zapujceni) {
            if ($zapujceni->isAktivni()) {
                return $zapujceni;
            }
        }

        return null;
    }

    public function tooltipZapujceni(): string
    {
        $aktivni = $this->getAktivniZapujceni();
        if (!$aktivni) {
            return '';
        }

        return 'Tuto věc si půjčil ' . $aktivni->getZak()->celeJmeno()
            . ' dne ' . $aktivni->getDatumZapujceni()->format('d.m.Y H:i');
    }
}
