<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\PhoneNumber;
use Doctrine\Persistence\ManagerRegistry;

class PhoneNumberRepository extends DoctrineEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PhoneNumber::class);
    }
}
