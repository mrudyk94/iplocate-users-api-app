<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Application\Port\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends DoctrineEntityRepository implements UserRepositoryInterface
{
    private const array ALLOWED_SORT_FIELDS = ['firstName', 'lastName', 'createdAt', 'updateAt', 'country'];

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

    /**
     * {@inheritDoc}
     */
    public function findAllSorted(string $sortField, string $sortDirection): ?array
    {
        if (!\in_array($sortField, self::ALLOWED_SORT_FIELDS, true)) {
            $sortField = 'createdAt';
        }

        $direction = strtoupper($sortDirection) === 'ASC' ? 'ASC' : 'DESC';

        return $this->createQueryBuilder('u')
            ->leftJoin('u.phoneNumbers', 'p')
            ->addSelect('p')
            ->orderBy('u.'.$sortField, $direction)
            ->getQuery()
            ->getResult();
    }
}
