<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/drink', name: 'drink')]
class DrinkController
{
    public function __construct(
        #[Autowire(env: 'DRINK')]
        private readonly string $drink,
    ) {
    }

    public function __invoke(): Response
    {
        return new Response($this->drink, 200, ['Content-Type' => 'text/plain']);
    }
}
