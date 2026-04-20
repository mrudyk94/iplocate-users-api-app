<?php

declare(strict_types=1);

namespace App\UI\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateUserInput
{
    public function __construct(
        #[Assert\NotBlank(message: 'First name cannot be empty')]
        #[Assert\Length(
            min: 3,
            max: 100,
            minMessage: 'First name must be at least {{ limit }} characters long',
            maxMessage: 'First name cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: "/^[\p{L}'\- ]+$/u",
            message: "First name can contain only letters, spaces, apostrophes and hyphens"
        )]
        #[Assert\Type('string')]
        public string $firstName,

        #[Assert\NotBlank(message: 'Last name cannot be empty')]
        #[Assert\Length(
            min: 3,
            max: 100,
            minMessage: 'Last name must be at least {{ limit }} characters long',
            maxMessage: 'Last name cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: "/^[\p{L}'\- ]+$/u",
            message: "Last name can contain only letters, spaces, apostrophes and hyphens"
        )]
        #[Assert\Type('string')]
        public string $lastName,

        #[Assert\NotBlank(message: 'Phone numbers cannot be empty')]
        #[Assert\Count(
            min: 1,
            minMessage: 'At least one phone number is required'
        )]
        #[Assert\All([
            new Assert\NotBlank(message: 'Phone number cannot be empty'),
            new Assert\Regex(
                pattern: '/^\+?[0-9]{10,15}$/',
                message: 'Phone number must be valid and contain 10-13 digits'
            )
        ])]
        #[Assert\Type('array')]
        public array  $phoneNumbers
    )
    {
    }
}
