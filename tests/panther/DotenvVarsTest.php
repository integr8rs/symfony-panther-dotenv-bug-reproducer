<?php

declare(strict_types=1);

namespace TestSuite\Panther;

use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

/**
 * Reproduces the bug: overriding an env var for the Panther-managed web server is silently
 * ignored whenever that var was actually loaded by Dotenv from .env in an ancestor process (and
 * so ended up listed in SYMFONY_DOTENV_VARS) - unless SYMFONY_DOTENV_VARS is also reset. See
 * ISSUE.md for the full root-cause explanation.
 *
 * Each test below overrides one env var via Panther's 'env' option and asserts the override took
 * effect. COLOR gets two tests - one without resetting SYMFONY_DOTENV_VARS (fails: the bug) and
 * one that also resets it (passes: the documented workaround) - while ANIMAL and FOOD each get
 * one, since for them the override always works regardless of the reset (see each test's own doc
 * comment for why). Wherever a var also has a known default from .env and/or phpunit.dist.xml,
 * the test asserts upfront that the response isn't that default, before asserting it's the
 * override - so a failure clearly shows which default leaked through instead.
 */
final class DotenvVarsTest extends PantherTestCase
{
    /**
     * COLOR is declared in .env, so Dotenv tracks it in SYMFONY_DOTENV_VARS and silently reverts
     * the override back to .env's value on every request. This test is expected to FAIL - that
     * failure is the bug.
     */
    public function testColorOverrideIsSilentlyIgnored(): void
    {
        $client = self::createClientWithEnv(['COLOR' => 'purple']);
        $client->request('GET', '/color');
        $actualColor = $client->findElement(WebDriverBy::xpath('html'))->getText();

        self::assertNotSame(
            'blue', // the value declared in .env
            $actualColor,
            'The controller returned .env\'s default value ("blue") instead of the overridden one.',
        );
        self::assertSame(
            'purple',
            $actualColor,
            'The controller did not return the overridden COLOR value - the override was ignored.',
        );
    }

    /**
     * Same override as testColorOverrideIsSilentlyIgnored(), but also resetting
     * SYMFONY_DOTENV_VARS - the documented workaround. Clearing it means Dotenv no longer finds
     * COLOR listed as already-loaded, so it leaves the override alone. This test passes.
     */
    public function testColorOverrideWorksWhenSymfonyDotenvVarsIsReset(): void
    {
        $client = self::createClientWithEnv(['COLOR' => 'purple', 'SYMFONY_DOTENV_VARS' => '']);
        $client->request('GET', '/color');
        $actualColor = $client->findElement(WebDriverBy::xpath('html'))->getText();

        self::assertNotSame(
            'blue', // the value declared in .env
            $actualColor,
            'The controller returned .env\'s default value ("blue") instead of the overridden one.',
        );
        self::assertSame(
            'purple',
            $actualColor,
            'The controller did not return the overridden COLOR value - the override was ignored.',
        );
    }

    /**
     * ANIMAL is declared in .env AND in phpunit.dist.xml's <env> - the latter is applied before
     * tests/bootstrap.php's Dotenv::bootEnv() call ever sees .env, so Dotenv finds it already set
     * and never tracks it in SYMFONY_DOTENV_VARS. The override should always take effect.
     */
    public function testAnimalOverrideWorks(): void
    {
        $client = self::createClientWithEnv(['ANIMAL' => 'giraffe']);
        $client->request('GET', '/animal');
        $actualAnimal = $client->findElement(WebDriverBy::xpath('html'))->getText();

        self::assertNotSame(
            'lion', // the value declared in .env
            $actualAnimal,
            'The controller returned .env\'s default value ("lion") instead of the overridden one.',
        );
        self::assertNotSame(
            'cat', // the value declared in phpunit.dist.xml
            $actualAnimal,
            'The controller returned phpunit.dist.xml\'s default value ("cat") instead of the overridden one.',
        );
        self::assertSame(
            'giraffe',
            $actualAnimal,
            'The controller did not return the overridden ANIMAL value - the override was ignored.',
        );
    }

    /**
     * FOOD is declared only in phpunit.dist.xml's <env>, never in .env at all - Dotenv never even
     * considers it, so there's nothing to skip or track in the first place. The override should
     * always take effect.
     */
    public function testFoodOverrideWorks(): void
    {
        $client = self::createClientWithEnv(['FOOD' => 'sushi']);
        $client->request('GET', '/food');
        $actualFood = $client->findElement(WebDriverBy::xpath('html'))->getText();

        self::assertNotSame(
            'pizza', // the value declared in phpunit.dist.xml
            $actualFood,
            'The controller returned phpunit.dist.xml\'s default value ("pizza") instead of the overridden one.',
        );
        self::assertSame(
            'sushi',
            $actualFood,
            'The controller did not return the overridden FOOD value - the override was ignored.',
        );
    }

    /**
     * Starts the Panther-managed web server with exactly the given env vars.
     *
     * Panther's ServerExtension (bootstrapped in phpunit.dist.xml) keeps a single web
     * server/client alive for the whole suite for speed.
     * Forcing a restart per call keeps the three tests isolated from each other.
     */
    private static function createClientWithEnv(array $env): PantherClient
    {
        self::stopWebServer();

        return self::createPantherClient(['env' => $env]);
    }
}
