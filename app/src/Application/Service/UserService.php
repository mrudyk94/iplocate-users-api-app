<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Message\CreateUserMessage;
use App\Application\Port\Repository\UserRepositoryInterface;
use App\Application\Port\Service\UserServiceInterface;
use App\Domain\Entity\User;
use DomainException;
use Symfony\Component\Messenger\MessageBusInterface;

class UserService implements UserServiceInterface
{
    /**
     * @param UserRepositoryInterface $userRepository
     * @param MessageBusInterface $messageBus
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBusInterface $messageBus
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function createUser(string $firstName, string $lastName, array $phoneNumbers, ?string $ip): void
    {
        // Перевіряємо чи вже існує користувач
        $user = $this->userRepository->findByPhoneNumber($phoneNumbers);

        // Користувач вже існує — створення заборонено
        if ($user) {
            throw new DomainException('Користувач з одним із вказаних номерів телефону вже існує. Створення дубліката заборонено!');
        }

        $this->messageBus->dispatch(new CreateUserMessage(
            firstName: $firstName,
            lastName: $lastName,
            phoneNumbers: $phoneNumbers,
            ip: '194.62.139.90',
        ));
    }
}
