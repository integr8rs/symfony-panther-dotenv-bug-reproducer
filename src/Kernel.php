<?php

namespace App;

use App\Controller\AnimalController;
use App\Controller\ColorController;
use App\Controller\FoodController;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => '%env(APP_SECRET)%',
        ]);

        $container->services()
            ->set(ColorController::class)
            ->autowire()
            ->public();

        $container->services()
            ->set(AnimalController::class)
            ->autowire()
            ->public();

        $container->services()
            ->set(FoodController::class)
            ->autowire()
            ->public();
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->add('color', '/color')
            ->controller(ColorController::class);

        $routes->add('animal', '/animal')
            ->controller(AnimalController::class);

        $routes->add('food', '/food')
            ->controller(FoodController::class);
    }
}
