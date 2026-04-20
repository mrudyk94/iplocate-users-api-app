<?php

declare(strict_types=1);

namespace App\Application\Message;

class CreateUserMessage
{
    /**
     * @param string $firstName
     * @param string $lastName
     * @param array $phoneNumbers
     * @param string|null $ip
     */
    public function __construct(
        public string $firstName,
        public string $lastName,
        public array $phoneNumbers,
        public ?string $ip,
    )
    {
    }
}
