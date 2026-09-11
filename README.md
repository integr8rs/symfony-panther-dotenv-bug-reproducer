# Symfony Dotenv/Panther reproducer

Reproducer project for a bug reported on the GitHub issue tracker (link: TODO).

## Branches

One branch per currently supported Symfony version:

| Branch | Symfony version | PHP required |
|--------|------------------|--------------|
| `6.4`  | 6.4.*            | 8.1+         |
| `7.4`  | 7.4.*            | 8.3+         |
| `8.1`  | 8.1.*            | 8.4+         |

Check out the branch matching the version you want to reproduce against before running it.

## Running the reproducer

The easiest way - no local PHP/Composer/Chrome/chromedriver needed, just Docker:

```
bin/reproduce.sh
```

This builds a throwaway container, installs dependencies, runs the tests, and tears the container down again afterward.

Expect **3 passing, 1 failing** test - the failure *is* the reproduction of the bug.

### Alternative ways to run it

Drive Docker Compose yourself (e.g. to keep the container around and poke at it):

```
docker compose up -d --build
docker compose exec app vendor/bin/phpunit
```

Or, on a machine that already has the PHP version required by this branch (see above), Composer, Chrome/Chromium and a matching chromedriver on the `PATH`:

```
composer update
vendor/bin/phpunit
```
