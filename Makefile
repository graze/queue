SHELL = /bin/sh

DOCKER ?= $(shell which docker)
DOCKER_REPOSITORY := graze/php-alpine:7.1-test
VOLUME := /opt/graze/queue
VOLUME_MAP := -v $$(pwd):${VOLUME}
DOCKER_RUN_BASE := ${DOCKER} run --rm -t ${VOLUME_MAP} -w ${VOLUME}
DOCKER_RUN := ${DOCKER_RUN_BASE} ${DOCKER_REPOSITORY}

.PHONY: install composer clean help run
.PHONY: test lint lint-fix test-unit test-integration test-matrix test-coverage test-coverage-html test-coverage-clover

.SILENT: help

# Building

install: ## Download the dependencies then build the image :rocket:.
	make 'composer-install --optimize-autoloader --ignore-platform-reqs'

composer-%: ## Run a composer command, `make "composer-<command> [...]"`.
	${DOCKER} run -t --rm \
        -v $$(pwd):/app:delegated \
        -v ~/.composer:/tmp:delegated \
        composer --no-interaction --prefer-dist $* $(filter-out $@,$(MAKECMDGOALS))

# Testing

test: ## Run the unit and integration testsuites.
test: lint test-unit test-integration

lint: ## Run phpcs against the code.
	${DOCKER_RUN} vendor/bin/phpcs -p --warning-severity=0 -s src/ tests/

lint-fix: ## Run phpcsf and fix possible lint errors.
	${DOCKER_RUN} vendor/bin/phpcbf -p -s src/ tests/

test-unit: ## Run the unit testsuite.
	${DOCKER_RUN} vendor/bin/phpunit --colors=always --testsuite unit

test-integration: ## Run the integration testsuite.
	${DOCKER_RUN} vendor/bin/phpunit --colors=always --testsuite integration

test-matrix: ## Run the unit tests against multiple targets.
	make DOCKER_REPOSITORY="php:5.6-alpine" test
	make DOCKER_REPOSITORY="php:7.0-alpine" test
	make DOCKER_REPOSITORY="php:7.1-alpine" test
	make DOCKER_REPOSITORY="php:7.2-alpine" test
	make DOCKER_REPOSITORY="hhvm/hhvm:latest" test

test-coverage: ## Run all tests and output coverage to the console.
	${DOCKER_RUN} phpdbg7 -qrr vendor/bin/phpunit --coverage-text

test-coverage-html: ## Run all tests and output coverage to html.
	${DOCKER_RUN} phpdbg7 -qrr vendor/bin/phpunit --coverage-html=./tests/report/html

test-coverage-clover: ## Run all tests and output clover coverage to file.
	${DOCKER_RUN} phpdbg7 -qrr vendor/bin/phpunit --coverage-clover=./tests/report/coverage.clover

# Help

help: ## Show this help message.
	echo "usage: make [target] ..."
	echo ""
	echo "targets:"
	fgrep --no-filename "##" $(MAKEFILE_LIST) | fgrep --invert-match $$'\t' | sed -e 's/: ## / - /'
