<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

class FoodController
{
    public function __construct(
        #[Autowire(env: 'FOOD')]
        private readonly string $food,
    ) {
    }

    public function __invoke(): Response
    {
        return new Response($this->food, 200, ['Content-Type' => 'text/plain']);
    }
}
