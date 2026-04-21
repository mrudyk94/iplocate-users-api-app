<?php

declare(strict_types=1);

namespace App\Application\Port\Service;

interface UserServiceInterface
{
    /**
     * Створення користувача
     * @param string $firstName
     * @param string $lastName
     * @param array $phoneNumbers
     * @param string $ip
     * @return void
     */
    public function createUser(string $firstName, string $lastName, array $phoneNumbers, string $ip): void;

    /**
     * Отримати список всіх користувачів у сортованому вигляді
     * @param string $sortField
     * @param string $sortOrder
     * @return array|null
     */
    public function getUsersListSorted(string $sortField, string $sortOrder): ?array;

    /**
     * Видалення користувача
     * @param int $userId
     * @return void
     */
    public function deleteUser(int $userId): void;
}
