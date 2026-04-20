<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Application\Port\Repository\PhoneNumberRepositoryInterface;
use App\Domain\Entity\PhoneNumber;
use App\Domain\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

class PhoneNumberRepository extends DoctrineEntityRepository implements PhoneNumberRepositoryInterface
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PhoneNumber::class);
    }
}
