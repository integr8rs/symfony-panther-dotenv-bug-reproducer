<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

class ColorController
{
    public function __construct(
        #[Autowire(env: 'COLOR')]
        private readonly string $color,
    ) {
    }

    public function __invoke(): Response
    {
        return new Response($this->color, 200, ['Content-Type' => 'text/plain']);
    }
}
