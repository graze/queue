SHELL = /bin/sh

.PHONY: install composer clean help
.PHONY: test test-unit test-intergration test-coverage test-matrix

.SILENT: help

install: ## Download the depenedencies then build the image :rocket:.
	make 'composer-install --optimize-autoloader --ignore-platform-reqs'
	docker build --tag graze/graze-queue:latest .

composer-%: ## Run a composer command, `make "composer-<command> [...]"`.
	docker run -it --rm \
	-v $$(pwd):/app \
	-v ~/.composer:/root/composer \
	-v ~/.ssh/id_rsa:/root/.ssh/id_rsa:ro \
	-v ~/.ssh/known_hosts:/root/.ssh/known_hosts:ro \
	composer/composer:master --no-interaction $*


test: ## Run the unit and intergration testsuites.
test: test-unit test-intergration

test-unit: ## Run the unit testsuite.
	docker run --rm -t graze/graze-queue -v $$(pwd):/opt/graze/queue \
	vendor/bin/phpunit --testsuite unit

test-intergration: ## Run the integration testsuite.
	docker run --rm -t graze/graze-queue -v $$(pwd):/opt/graze/queue \
	vendor/bin/phpunit --testsuite integration

test-coverage: ## Run the testsuites with coverage enabled.
	docker run --rm -t graze/graze-queue -v $$(pwd):/opt/graze/queue \
	vendor/bin/phpunit --coverage-text --testsuite unit
	docker run --rm -t graze/graze-queue -v $$(pwd):/opt/graze/queue \
	vendor/bin/phpunit --coverage-text --testsuite integration

test-matrix:
	docker run --rm -t -v $$(pwd):/opt/graze/queue -w /opt/graze/queue php:5.5-cli \
	vendor/bin/phpunit --testsuite unit
	docker run --rm -t -v $$(pwd):/opt/graze/queue -w /opt/graze/queue php:5.6-cli \
	vendor/bin/phpunit --testsuite unit
	docker run --rm -t -v $$(pwd):/opt/graze/queue -w /opt/graze/queue php:7.0-cli \
	vendor/bin/phpunit --testsuite unit


clean: ## Clean up any images.
	docker rmi graze/graze-queue:latest

help: ## Show this help message.
	echo "usage: make [target] ..."
	echo ""
	echo "targets:"
	fgrep --no-filename "##" $(MAKEFILE_LIST) | fgrep --invert-match $$'\t' | sed -e 's/## //'
