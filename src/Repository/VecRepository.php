<?php

namespace App\Repository;

use App\Entity\Vec;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Vec> */
class VecRepository extends ServiceEntityRepository
{
    use SqlRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vec::class);
    }

    public function findById(int $id): ?Vec
    {
        $sql = <<<'SQL'
            SELECT v.*
            FROM vec v
            WHERE v.id = :id
            SQL;

        /** @var Vec|null */
        return $this->fetchOne($sql, Vec::class, 'v', ['id' => $id]);
    }

    /** @return Vec[] */
    public function findForTridaWithActiveLoan(int $tridaId): array
    {
        $sql = <<<'SQL'
            SELECT v.*
            FROM vec v
            WHERE v.trida_id = :tridaId
            ORDER BY v.nazev ASC
            SQL;

        /** @var Vec[] */
        return $this->fetchAll($sql, Vec::class, 'v', ['tridaId' => $tridaId]);
    }

    /** @return Vec[] */
    public function findForTridaPicker(int $tridaId): array
    {
        $sql = <<<'SQL'
            SELECT v.*
            FROM vec v
            WHERE v.trida_id = :tridaId
            ORDER BY v.zapujcena ASC, v.nazev ASC
            SQL;

        /** @var Vec[] */
        return $this->fetchAll($sql, Vec::class, 'v', ['tridaId' => $tridaId]);
    }
}
