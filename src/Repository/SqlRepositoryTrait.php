<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

trait SqlRepositoryTrait
{
    abstract protected function getEntityManager(): EntityManagerInterface;

    /**
     * @param array<string, mixed> $params
     * @return list<object>
     */
    protected function fetchAll(string $sql, string $entityClass, string $alias, array $params = []): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata($entityClass, $alias);

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        foreach ($params as $name => $value) {
            $query->setParameter($name, $value);
        }

        return $query->getResult();
    }

    /**
     * @param array<string, mixed> $params
     */
    protected function fetchOne(string $sql, string $entityClass, string $alias, array $params = []): ?object
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata($entityClass, $alias);

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        foreach ($params as $name => $value) {
            $query->setParameter($name, $value);
        }

        return $query->getOneOrNullResult();
    }

    /**
     * @param array<string, mixed> $params
     */
    protected function fetchInt(string $sql, array $params = []): int
    {
        return (int) $this->getEntityManager()->getConnection()->executeQuery($sql, $params)->fetchOne();
    }

}
