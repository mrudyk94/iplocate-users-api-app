<?php

declare(strict_types=1);

namespace App\Application\Port\Gateway;

interface IpLookupGatewayInterface
{
    /**
     * Визначення країни по IP адреси
     * @param string $ip
     * @return string|null
     */
    public function getCountry(string $ip): ?string;
}
