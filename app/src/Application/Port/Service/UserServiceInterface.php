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


}
