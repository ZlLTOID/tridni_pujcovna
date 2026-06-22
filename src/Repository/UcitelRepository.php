<?php

namespace App\Repository;

use App\Entity\Ucitel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Ucitel> */
class UcitelRepository extends ServiceEntityRepository
{
    use SqlRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ucitel::class);
    }

    public function findById(int $id): ?Ucitel
    {
        $sql = <<<'SQL'
            SELECT u.*
            FROM ucitel u
            WHERE u.id = :id
            SQL;

        /** @var Ucitel|null */
        return $this->fetchOne($sql, Ucitel::class, 'u', ['id' => $id]);
    }

    public function findByUsername(string $username): ?Ucitel
    {
        $sql = <<<'SQL'
            SELECT u.*
            FROM ucitel u
            WHERE u.username = :username
            SQL;

        /** @var Ucitel|null */
        return $this->fetchOne($sql, Ucitel::class, 'u', ['username' => $username]);
    }

    public function countAll(): int
    {
        $sql = <<<'SQL'
            SELECT COUNT(*) AS pocet
            FROM ucitel
            SQL;

        return $this->fetchInt($sql);
    }

    /** @return Ucitel[] */
    public function findAllOrdered(): array
    {
        $sql = <<<'SQL'
            SELECT u.*
            FROM ucitel u
            ORDER BY u.role DESC, u.prijmeni ASC, u.jmeno ASC
            SQL;

        /** @var Ucitel[] */
        return $this->fetchAll($sql, Ucitel::class, 'u');
    }
}
