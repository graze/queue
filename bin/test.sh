#!/bin/bash

# Exit bash immediately if any command fails
set -e

echo "--- Installing dependencies"

composer install


echo "--- Linting"

composer run-script lint


echo "+++ Running unit tests"

vendor/bin/phpunit --testsuite unit


echo "+++ Running integration tests"

vendor/bin/phpunit --testsuite integration
