<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

class FavoriteColorController
{
    public function __construct(
        #[Autowire(env: 'FAVORITE_COLOR')]
        private readonly string $favoriteColor,
    ) {
    }

    public function __invoke(): Response
    {
        return new Response($this->favoriteColor, 200, ['Content-Type' => 'text/plain']);
    }
}
