<?php

namespace App\Repository;

use App\Entity\Zak;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Zak> */
class ZakRepository extends ServiceEntityRepository
{
    use SqlRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zak::class);
    }

    public function findById(int $id): ?Zak
    {
        $sql = <<<'SQL'
            SELECT z.*
            FROM zak z
            WHERE z.id = :id
            SQL;

        /** @var Zak|null */
        return $this->fetchOne($sql, Zak::class, 'z', ['id' => $id]);
    }

    /** @return Zak[] */
    public function findByTridaId(int $tridaId): array
    {
        $sql = <<<'SQL'
            SELECT z.*
            FROM zak z
            WHERE z.trida_id = :tridaId
            ORDER BY z.prijmeni ASC, z.jmeno ASC
            SQL;

        /** @var Zak[] */
        return $this->fetchAll($sql, Zak::class, 'z', ['tridaId' => $tridaId]);
    }
}
