<?php

namespace App\Repository;

use App\Entity\Zapujceni;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Zapujceni> */
class ZapujceniRepository extends ServiceEntityRepository
{
    use SqlRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zapujceni::class);
    }

    public function findById(int $id): ?Zapujceni
    {
        $sql = <<<'SQL'
            SELECT zp.*
            FROM zapujceni zp
            WHERE zp.id = :id
            SQL;

        /** @var Zapujceni|null */
        return $this->fetchOne($sql, Zapujceni::class, 'zp', ['id' => $id]);
    }

    /** @return Zapujceni[] */
    public function findActiveForTridaId(int $tridaId): array
    {
        $sql = <<<'SQL'
            SELECT zp.*
            FROM zapujceni zp
            INNER JOIN vec v ON v.id = zp.vec_id
            WHERE v.trida_id = :tridaId
              AND zp.aktivni = 1
            ORDER BY zp.datum_zapujceni DESC
            SQL;

        /** @var Zapujceni[] */
        return $this->fetchAll($sql, Zapujceni::class, 'zp', ['tridaId' => $tridaId]);
    }

    /** @return Zapujceni[] */
    public function findHistoryForTridaId(int $tridaId): array
    {
        $sql = <<<'SQL'
            SELECT zp.*
            FROM zapujceni zp
            INNER JOIN vec v ON v.id = zp.vec_id
            WHERE v.trida_id = :tridaId
              AND zp.aktivni = 0
            ORDER BY zp.datum_vraceni DESC, zp.datum_zapujceni DESC
            SQL;

        /** @var Zapujceni[] */
        return $this->fetchAll($sql, Zapujceni::class, 'zp', ['tridaId' => $tridaId]);
    }

    /** @return Zapujceni[] */
    public function findForTridaId(int $tridaId): array
    {
        $sql = <<<'SQL'
            SELECT zp.*
            FROM zapujceni zp
            INNER JOIN vec v ON v.id = zp.vec_id
            WHERE v.trida_id = :tridaId
            ORDER BY zp.aktivni DESC, zp.datum_zapujceni DESC
            SQL;

        /** @var Zapujceni[] */
        return $this->fetchAll($sql, Zapujceni::class, 'zp', ['tridaId' => $tridaId]);
    }

    public function countActiveForTridaId(int $tridaId): int
    {
        $sql = <<<'SQL'
            SELECT COUNT(*) AS pocet
            FROM zapujceni zp
            INNER JOIN vec v ON v.id = zp.vec_id
            WHERE v.trida_id = :tridaId
              AND zp.aktivni = 1
            SQL;

        return $this->fetchInt($sql, ['tridaId' => $tridaId]);
    }

    /** @return Zapujceni[] */
    public function findRecentForAdmin(int $limit = 50): array
    {
        $limit = max(1, $limit);
        $sql = <<<SQL
            SELECT zp.*
            FROM zapujceni zp
            ORDER BY zp.aktivni DESC, zp.datum_zapujceni DESC
            LIMIT {$limit}
            SQL;

        /** @var Zapujceni[] */
        return $this->fetchAll($sql, Zapujceni::class, 'zp');
    }
}
