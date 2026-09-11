#!/usr/bin/env bash
#
# Builds a throwaway Docker container, runs the SYMFONY_DOTENV_VARS bug-reproduction tests, and
# tears everything down again - see `composer run reproduce`.
set -euo pipefail

cd "$(dirname "$0")/.."

# Real, host-level env vars, forwarded into the app container via docker-compose.yml's
# environment section. Neither is declared in phpunit.dist.xml; DRINK also isn't in .env, but
# SEASON is (with a different value). See DotenvVarsTest for why that distinction matters.
export DRINK=coffee
export SEASON=summer

cleanup() {
    docker compose down --remove-orphans >/dev/null 2>&1 || true
}
trap cleanup EXIT

echo "Building the app image and installing dependencies (this can take a while the first time)..."
docker compose build app >/dev/null 2>&1
docker compose run --rm -T app composer update --no-interaction --quiet >/dev/null 2>&1

echo
echo "Running the reproducer - one test passes, one is expected to FAIL (that failure is the bug):"
echo
# Docker/Podman Compose's own banner and container-lifecycle chatter go to stderr, while the
# containerized command's real output (phpunit's) goes to stdout - so this hides the former
# without touching the latter.
docker compose run --rm app vendor/bin/phpunit 2>/dev/null
