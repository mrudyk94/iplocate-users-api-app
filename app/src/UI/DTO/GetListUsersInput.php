<?php

declare(strict_types=1);

namespace App\UI\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetListUsersInput
{
    public function __construct(
        #[Assert\NotBlank(message: 'Sort field must not be blank!')]
        public string $sort = 'createdAt',

        #[Assert\Choice(
            choices: ['ASC', 'DESC'],
            message: 'Order must be ASC or DESC!'
        )]
        public string $order = 'DESC',
    ) {
    }
}
