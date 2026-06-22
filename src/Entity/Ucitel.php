<?php

namespace App\Entity;

use App\Repository\UcitelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UcitelRepository::class)]
#[ORM\Table(name: 'ucitel')]
class Ucitel implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private string $username = '';

    #[ORM\Column(name: 'password_hash')]
    private string $passwordHash = '';

    #[ORM\Column]
    private string $jmeno = '';

    #[ORM\Column]
    private string $prijmeni = '';

    #[ORM\Column]
    private string $role = 'ucitel';

    /** @var Collection<int, Trida> */
    #[ORM\OneToMany(mappedBy: 'ucitel', targetEntity: Trida::class)]
    private Collection $tridy;

    public function __construct()
    {
        $this->tridy = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->passwordHash;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): self
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->role === 'admin' ? ['ROLE_ADMIN'] : ['ROLE_UCITEL'];
    }

    public function eraseCredentials(): void
    {
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

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /** @return Collection<int, Trida> */
    public function getTridy(): Collection
    {
        return $this->tridy;
    }

    public function celeJmeno(): string
    {
        return $this->jmeno . ' ' . $this->prijmeni;
    }

    public function jeAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getPocetTrid(): int
    {
        return $this->tridy->count();
    }
}
