<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Application\Message\CreateUserMessage;
use App\Application\Port\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use App\Application\MessageHandler\CreateUserMessageHandler;
use App\Application\Port\Gateway\IpLookupGatewayInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateUserMessageHandlerTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private IpLookupGatewayInterface&MockObject $ipLookupGateway;
    private UserRepositoryInterface $userRepository;
    private CreateUserMessageHandler $handler;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->ipLookupService = $this->createMock(IpLookupGatewayInterface::class);
        $this->handler = new CreateUserMessageHandler(
            $this->entityManager,
            $this->ipLookupGateway,
            $this->userRepository,
        );
    }

    /**
     * @return void
     */
    public function testHandlerPersistsUserWithResolvedCountry(): void
    {
        $message = new CreateUserMessage(
            firstName: 'John',
            lastName: 'Doe',
            phoneNumbers: ['+380971234567'],
            ip: '8.8.8.8',
        );

        $this->ipLookupService
            ->expects($this->once())
            ->method('getCountry')
            ->with('8.8.8.8')
            ->willReturn('Ukraine');

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (User $user) {
                return $user->getFirstName() === 'John'
                    && $user->getLastName() === 'Doe'
                    && $user->getCountry() === 'Ukraine'
                    && $user->getIp() === '8.8.8.8'
                    && $user->getPhoneNumbers()->count() === 1;
            }));

        $this->entityManager->expects($this->once())->method('flush');

        ($this->handler)($message);
    }

    /**
     * @return void
     */
    public function testHandlerSkipsIpLookupWhenIpIsNull(): void
    {
        $message = new CreateUserMessage(
            firstName: 'Jane',
            lastName: 'Doe',
            phoneNumbers: ['+380971234567'],
            ip: null,
        );

        $this->ipLookupService->expects($this->never())->method('getCountry');

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(fn (User $user) => $user->getCountry() === null));

        $this->entityManager->expects($this->once())->method('flush');

        ($this->handler)($message);
    }

    /**
     * @return void
     */
    public function testHandlerAddsAllPhoneNumbers(): void
    {
        $message = new CreateUserMessage(
            firstName: 'John',
            lastName: 'Doe',
            phoneNumbers: ['+380971234567', '+380631234567', '+380501234567'],
            ip: null,
        );

        $capturedUser = null;
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
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
        $message = new CreateUserMessage(
            firstName: 'John',
            lastName: 'Doe',
            phoneNumbers: ['+380971234567'],
            ip: '8.8.8.8',
        );

        $this->ipLookupService->method('getCountry')->willReturn(null);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(fn (User $user) => $user->getCountry() === null));

        ($this->handler)($message);
    }
}
