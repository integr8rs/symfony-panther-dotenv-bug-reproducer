#!/usr/bin/env bash
#
# Builds a throwaway Docker container, runs the SYMFONY_DOTENV_VARS bug-reproduction tests, and
# tears everything down again - see `composer run reproduce`.
set -euo pipefail

cd "$(dirname "$0")/.."

cleanup() {
    docker compose down --remove-orphans >/dev/null 2>&1 || true
}
trap cleanup EXIT

echo "Building the app image and installing dependencies (this can take a while the first time)..."
docker compose build app >/dev/null 2>&1
docker compose run --rm -T app composer install --no-interaction --quiet >/dev/null 2>&1

echo
echo "Running the reproducer - one test passes, one is expected to FAIL (that failure is the bug):"
echo
docker compose run --quiet --rm app vendor/bin/phpunit || :
