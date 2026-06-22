<?php

namespace App\Repository;

use App\Entity\Trida;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Trida> */
class TridaRepository extends ServiceEntityRepository
{
    use SqlRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trida::class);
    }

    public function findById(int $id): ?Trida
    {
        $sql = <<<'SQL'
            SELECT t.*
            FROM trida t
            WHERE t.id = :id
            SQL;

        /** @var Trida|null */
        return $this->fetchOne($sql, Trida::class, 't', ['id' => $id]);
    }

    /** @return Trida[] */
    public function findAllWithRelationsForAdmin(): array
    {
        $sql = <<<'SQL'
            SELECT t.*
            FROM trida t
            ORDER BY t.nazev ASC
            SQL;

        /** @var Trida[] */
        return $this->fetchAll($sql, Trida::class, 't');
    }

    /** @return Trida[] */
    public function findForUcitelId(int $ucitelId): array
    {
        $sql = <<<'SQL'
            SELECT t.*
            FROM trida t
            WHERE t.ucitel_id = :ucitelId
            ORDER BY t.nazev ASC
            SQL;

        /** @var Trida[] */
        return $this->fetchAll($sql, Trida::class, 't', ['ucitelId' => $ucitelId]);
    }
}
