<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Message\CreateUserMessage;
use App\Application\Port\Repository\UserRepositoryInterface;
use App\Application\Port\Service\UserServiceInterface;
use DomainException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
            throw new DomainException('A user with one of the specified phone numbers already exists. Creating a duplicate is prohibited!');
        }

        $this->messageBus->dispatch(new CreateUserMessage(
            firstName: $firstName,
            lastName: $lastName,
            phoneNumbers: $phoneNumbers,
            ip: $ip,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getUsersListSorted(string $sortField, string $sortOrder): ?array
    {
        $users = $this->userRepository->findAllSorted($sortField, $sortOrder);
        if(!$users) {
            return $users;
        }

        return array_map(
            static fn ($user) => [
                'id' => (string) $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'ip' => $user->getIp(),
                'country' => $user->getCountry(),
                'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'phoneNumbers' => $user->getPhoneNumbers()
                    ->map(static fn ($p) => $p->getNumber())
                    ->toArray(),
            ],
            $users
        );
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(int $userId): void
    {
        // Перевірка, чи користувач якого хочемо видалити є в базі даних
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new DomainException(sprintf('User with ID %d not found', $userId));
        }

        // Видаляємо користувача
        $this->userRepository->deleteAndFlash($user);
    }
}
