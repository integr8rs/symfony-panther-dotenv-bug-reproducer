<?php

declare(strict_types=1);

namespace TestSuite\Panther;

use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

/**
 * Reproduces the bug: overriding FAVORITE_COLOR for the Panther-managed web server is silently
 * ignored, unless SYMFONY_DOTENV_VARS is also reset. See ISSUE.md for the full root-cause
 * explanation. Both scenarios run through the exact same test body - only the env differs (see
 * envConfigurations() below), which is the whole bug in one diff.
 */
final class FavoriteColorDotenvVarsTest extends PantherTestCase
{
    #[DataProvider('envConfigurations')]
    public function testFavoriteColorOverride(string $expectedFavoriteColor, array $env): void
    {
        $client = self::createFavoriteColorClient($env);
        $client->request('GET', '/favorite-color');
        $actualColor = $client->findElement(WebDriverBy::xpath('html'))->getText();

        self::assertNotSame(
            'blue', // the value from .env - seeing it here means the override was silently ignored
            $actualColor,
            \sprintf(
                'The controller still returned the .env default ("blue") instead of the overridden FAVORITE_COLOR ("%s") - the override was silently ignored.',
                $expectedFavoriteColor,
            ),
        );
        self::assertSame(
            $expectedFavoriteColor,
            $actualColor,
            'The controller did not return the overridden FAVORITE_COLOR value.',
        );
    }

    public static function envConfigurations(): iterable
    {
        yield 'override works when SYMFONY_DOTENV_VARS is reset' => [
            'purple',
            ['FAVORITE_COLOR' => 'purple', 'SYMFONY_DOTENV_VARS' => ''],
        ];

        yield 'override is silently ignored without resetting SYMFONY_DOTENV_VARS (the bug)' => [
            'orange',
            ['FAVORITE_COLOR' => 'orange'],
        ];
    }

    /**
     * Starts the Panther-managed web server with exactly the given env vars - nothing added or
     * forced on top - so the assertion above is testing precisely what it says it's testing.
     *
     * Panther's ServerExtension (bootstrapped in phpunit.dist.xml) keeps a single web
     * server/client alive for the whole suite for speed, so without an explicit stopWebServer()
     * here, the second data set would silently reuse the first one's already-running server -
     * with its env, not its own. Forcing a restart per call keeps the two scenarios isolated.
     */
    private static function createFavoriteColorClient(array $env): PantherClient
    {
        self::stopWebServer();

        return self::createPantherClient(['env' => $env]);
    }
}
