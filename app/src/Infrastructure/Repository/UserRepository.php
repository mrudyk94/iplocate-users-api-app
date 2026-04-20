<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Application\Port\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends DoctrineEntityRepository implements UserRepositoryInterface
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?User
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * {@inheritDoc}
     */
    public function findByPhoneNumber(array $phoneNumbers): ?User
    {
        return $this->createQueryBuilder('u')
            ->join('u.phoneNumbers', 'p')
            ->andWhere('p.number IN (:phones)')
            ->setParameter('phones', $phoneNumbers)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
