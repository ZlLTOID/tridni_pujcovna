<?php

namespace App\Entity;

use App\Repository\ZapujceniRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ZapujceniRepository::class)]
#[ORM\Table(name: 'zapujceni')]
class Zapujceni
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'datum_zapujceni')]
    private \DateTimeImmutable $datumZapujceni;

    #[ORM\Column(name: 'datum_vraceni', nullable: true)]
    private ?\DateTimeImmutable $datumVraceni = null;

    #[ORM\Column(nullable: true)]
    private ?string $poznamka = null;

    #[ORM\Column]
    private bool $aktivni = true;

    #[ORM\ManyToOne(targetEntity: Vec::class, inversedBy: 'zapujceni')]
    #[ORM\JoinColumn(name: 'vec_id', nullable: false)]
    private Vec $vec;

    #[ORM\ManyToOne(targetEntity: Zak::class, inversedBy: 'zapujceni')]
    #[ORM\JoinColumn(name: 'zak_id', nullable: false)]
    private Zak $zak;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatumZapujceni(): \DateTimeImmutable
    {
        return $this->datumZapujceni;
    }

    public function setDatumZapujceni(\DateTimeImmutable $datumZapujceni): self
    {
        $this->datumZapujceni = $datumZapujceni;

        return $this;
    }

    public function getDatumVraceni(): ?\DateTimeImmutable
    {
        return $this->datumVraceni;
    }

    public function setDatumVraceni(?\DateTimeImmutable $datumVraceni): self
    {
        $this->datumVraceni = $datumVraceni;

        return $this;
    }

    public function getPoznamka(): ?string
    {
        return $this->poznamka;
    }

    public function setPoznamka(?string $poznamka): self
    {
        $this->poznamka = $poznamka;

        return $this;
    }

    public function isAktivni(): bool
    {
        return $this->aktivni;
    }

    public function setAktivni(bool $aktivni): self
    {
        $this->aktivni = $aktivni;

        return $this;
    }

    public function getVec(): Vec
    {
        return $this->vec;
    }

    public function setVec(Vec $vec): self
    {
        $this->vec = $vec;

        return $this;
    }

    public function getZak(): Zak
    {
        return $this->zak;
    }

    public function setZak(Zak $zak): self
    {
        $this->zak = $zak;

        return $this;
    }

    public function getVecNazev(): string
    {
        return $this->vec->getNazev();
    }

    public function zakCeleJmeno(): string
    {
        return $this->zak->celeJmeno();
    }

    public function getTridaNazev(): string
    {
        return $this->vec->getTrida()->getNazev();
    }

    public function ucitelCeleJmeno(): string
    {
        return $this->vec->getTrida()->getUcitel()->celeJmeno();
    }

    public function vratit(): void
    {
        $this->datumVraceni = new \DateTimeImmutable();
        $this->aktivni = false;
        $this->vec->setZapujcena(false);
    }
}
