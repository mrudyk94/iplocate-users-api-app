<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateway\IpLookup;

use App\Application\Port\Gateway\IpLookupGatewayInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class IpLookupGateway implements IpLookupGatewayInterface
{
    private const string COUNTRY_LOOKUP_PATH = 'lookup';

    /**
     * @param HttpClientInterface $ipLookupApiClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly HttpClientInterface $ipLookupApiClient,
        private readonly LoggerInterface $logger,
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getCountry(string $ip): ?string
    {
        if ($this->isPrivateOrReservedIp($ip)) {
            return null;
        }

        try {
            $response = $this->sendRequest(
                'GET',
                self::COUNTRY_LOOKUP_PATH . '/' . urlencode($ip)
            );

            $data = $response->toArray();

            return $data['country'] ?? null;
        } catch (\Throwable $e) {
            $this->logger->warning('Не вдалося знайти країну IP-адреси', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Чи можна цей IP використовувати для зовнішніх сервісів
     * @param string $ip
     * @return bool
     */
    private function isPrivateOrReservedIp(string $ip): bool
    {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * Посилання запитів
     * @param string $method
     * @param $path
     * @param mixed $options
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    private function sendRequest(string $method, $path, mixed $options = []): ResponseInterface
    {
        return $this->ipLookupApiClient->request($method, $path, $options);
    }
}
