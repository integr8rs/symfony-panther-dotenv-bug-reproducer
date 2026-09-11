<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/season', name: 'season')]
class SeasonController
{
    public function __construct(
        #[Autowire(env: 'SEASON')]
        private readonly string $season,
    ) {
    }

    public function __invoke(): Response
    {
        return new Response($this->season, 200, ['Content-Type' => 'text/plain']);
    }
}
