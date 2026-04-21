<?php

declare(strict_types=1);

namespace App\Application\Port\Repository;

use App\Domain\Entity\User;
use App\Domain\ValueObject\MobilePhone;

interface UserRepositoryInterface extends EntityRepositoryInterface
{
    /**
     * Отримати користувача по ID
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    /**
     * Отримати користувача по номерах телефону
     * @param array $phoneNumbers
     * @return User|null
     */
    public function findByPhoneNumber(array $phoneNumbers): ?User;

    /**
     * Отримання списку користувачів у відсортованому вигляді
     * @param string $sortField
     * @param string $sortDirection
     * @return array|null
     */
    public function findAllSorted(string $sortField, string $sortDirection): ?array;
}
