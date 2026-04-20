<?php

declare(strict_types=1);

namespace App\Application\MessageHandler;

use App\Application\Message\CreateUserMessage;
use App\Application\Port\Gateway\IpLookupGatewayInterface;
use App\Application\Port\Repository\UserRepositoryInterface;
use App\Domain\Entity\PhoneNumber;
use App\Domain\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateUserHandler
{
    /**
     * @param IpLookupGatewayInterface $ipLookupGateway
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        private readonly IpLookupGatewayInterface $ipLookupGateway,
        private readonly UserRepositoryInterface  $userRepository
    )
    {
    }

    /**
     * @param CreateUserMessage $message
     * @return void
     */
    public function __invoke(CreateUserMessage $message): void
    {
        $user = new User(
            $message->firstName,
            $message->lastName,
            $message->ip
        );

        if ($message->ip !== null) {
            $country = $this->ipLookupGateway->getCountry($message->ip);
            $user->setCountry($country);
        }

        foreach ($message->phoneNumbers as $number) {
            $user->addPhoneNumber(new PhoneNumber($number));
        }

        $this->userRepository->saveAndFlush($user);
    }
}
