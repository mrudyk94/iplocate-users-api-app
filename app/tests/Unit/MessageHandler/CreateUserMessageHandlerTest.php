<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Application\Message\CreateUserMessage;
use App\Application\MessageHandler\CreateUserMessageHandler;
use App\Application\Port\Gateway\IpLookupGatewayInterface;
use App\Application\Port\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateUserMessageHandlerTest extends TestCase
{
    private IpLookupGatewayInterface&MockObject $ipLookupGateway;
    private UserRepositoryInterface&MockObject $userRepository;
    private CreateUserMessageHandler $handler;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->ipLookupGateway = $this->createMock(IpLookupGatewayInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);

        $this->handler = new CreateUserMessageHandler(
            $this->ipLookupGateway,
            $this->userRepository,
        );
    }

    /**
     * @return void
     */
    public function testHandlerPersistsUserWithResolvedCountry(): void
    {
        $firstName = 'Іван';
        $lastName = 'Шевченко';

        $message = new CreateUserMessage(
            firstName: $firstName,
            lastName: $lastName,
            phoneNumbers: ['+380971234567'],
            ip: '8.8.8.8',
        );

        $this->ipLookupGateway
            ->expects($this->once())
            ->method('getCountry')
            ->with('8.8.8.8')
            ->willReturn('Ukraine');

        $this->userRepository
            ->expects($this->once())
            ->method('saveAndFlush')
            ->with($this->callback(function (User $user) {
                return $user->getFirstName() === 'Іван'
                    && $user->getLastName() === 'Шевченко'
                    && $user->getCountry() === 'Ukraine'
                    && $user->getIp() === '8.8.8.8'
                    && $user->getPhoneNumbers()->count() === 1;
            }));

        ($this->handler)($message);
    }

    /**
     * @return void
     */
    public function testHandlerSkipsIpLookupWhenIpIsNull(): void
    {
        $firstName = 'Іван';
        $lastName = 'Шевченко';

        $message = new CreateUserMessage(
            firstName: $firstName,
            lastName: $lastName,
            phoneNumbers: ['+380971234567'],
            ip: null,
        );

        $this->ipLookupGateway
            ->expects($this->never())
            ->method('getCountry');

        $this->userRepository
            ->expects($this->once())
            ->method('saveAndFlush')
            ->with($this->callback(fn (User $user) => $user->getCountry() === null));

        ($this->handler)($message);
    }

    /**
     * @return void
     */
    public function testHandlerAddsAllPhoneNumbers(): void
    {
        $firstName = 'Іван';
        $lastName = 'Шевченко';

        $capturedUser = null;

        $message = new CreateUserMessage(
            firstName: $firstName,
            lastName: $lastName,
            phoneNumbers: [
                '+380971234567',
                '+380631234567',
                '+380501234567'
            ],
            ip: null,
        );

        $this->userRepository
            ->expects($this->once())
            ->method('saveAndFlush')
            ->with($this->callback(function (User $user) use (&$capturedUser) {
                $capturedUser = $user;
                return true;
            }));

        ($this->handler)($message);

        $this->assertNotNull($capturedUser);
        $this->assertCount(3, $capturedUser->getPhoneNumbers());
    }

    /**
     * @return void
     */
    public function testHandlerSetsNullCountryWhenLookupReturnsNull(): void
    {
        $firstName = 'Іван';
        $lastName = 'Шевченко';

        $message = new CreateUserMessage(
            firstName: $firstName,
            lastName: $lastName,
            phoneNumbers: ['+380971234567'],
            ip: '8.8.8.8',
        );

        $this->ipLookupGateway
            ->method('getCountry')
            ->willReturn(null);

        $this->userRepository
            ->expects($this->once())
            ->method('saveAndFlush')
            ->with($this->callback(fn (User $user) => $user->getCountry() === null));

        ($this->handler)($message);
    }
}
