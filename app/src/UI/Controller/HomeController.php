<?php

declare(strict_types=1);

namespace App\UI\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController
{
    #[Route('/', name: 'home')]
    public function __invoke(): Response
    {
        return new Response('<html><body>Symfony is working</body></html>');
    }
}
