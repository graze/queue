#!/bin/bash
set -ev

if [[ -f ./tests/report/coverage.clover ]]; then
    wget https://scrutinizer-ci.com/ocular.phar
    php ocular.phar code-coverage:upload --format=php-clover ./tests/report/coverage.clover
fi
