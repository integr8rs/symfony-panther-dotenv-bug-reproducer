<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

class AnimalController
{
    public function __construct(
        #[Autowire(env: 'ANIMAL')]
        private readonly string $animal,
    ) {
    }

    public function __invoke(): Response
    {
        return new Response($this->animal, 200, ['Content-Type' => 'text/plain']);
    }
}
