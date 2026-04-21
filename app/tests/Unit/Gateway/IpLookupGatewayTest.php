<?php

declare(strict_types=1);

namespace App\Tests\Unit\Gateway;

use App\Infrastructure\Gateway\IpLookup\IpLookupGateway;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class IpLookupGatewayTest extends TestCase
{
    private HttpClientInterface&MockObject $httpClient;
    private LoggerInterface&MockObject $logger;
    private IpLookupGateway $gateway;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->gateway = new IpLookupGateway(
            $this->httpClient,
            $this->logger
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetCountryReturnsCountryForPublicIp(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $response->method('toArray')->willReturn([
            'country' => 'Ukraine',
        ]);

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $logger = $this->createMock(LoggerInterface::class);

        $gateway = new IpLookupGateway($client, $logger);

        $result = $gateway->getCountry('8.8.8.8');

        $this->assertSame('Ukraine', $result);
    }

    /**
     * @return void
     */
    public function testGetCountryReturnsNullForPrivateIp(): void
    {
        $this->httpClient->expects($this->never())->method('request');

        $result = $this->gateway->getCountry('192.168.1.1');

        $this->assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetCountryReturnsNullForLoopbackIp(): void
    {
        $this->httpClient->expects($this->never())->method('request');

        $result = $this->gateway->getCountry('127.0.0.1');

        $this->assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetCountryReturnsNullOnHttpException(): void
    {
        $this->httpClient
            ->method('request')
            ->willThrowException(new \RuntimeException('Connection refused'));

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('Could not find IP address country!', $this->arrayHasKey('ip'));

        $result = $this->gateway->getCountry('8.8.8.8');

        $this->assertNull($result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetCountryReturnsNullWhenCountryKeyMissing(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['ip' => '8.8.8.8']);

        $this->httpClient
            ->method('request')
            ->willReturn($response);

        $result = $this->gateway->getCountry('8.8.8.8');

        $this->assertNull($result);
    }
}
