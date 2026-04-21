<?php

declare(strict_types=1);

namespace App\Tests\Mock;

use App\Application\Port\Gateway\IpLookupGatewayInterface;

final class NullIpLookupGateway implements IpLookupGatewayInterface
{
    public function getCountry(string $ip): ?string
    {
        return null;
    }
}
