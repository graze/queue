SHELL = /bin/sh

.PHONY: install composer clean help
.PHONY: test test-unit test-intergration test-matrix

.SILENT: help

install: ## Download the depenedencies then build the image :rocket:.
	make 'composer-install --optimize-autoloader --ignore-platform-reqs'
	docker build --tag graze/queue:latest .

composer-%: ## Run a composer command, `make "composer-<command> [...]"`.
	docker run -t --rm \
	-v $$(pwd):/app \
	-v ~/.composer:/root/composer \
	-v ~/.ssh:/root/.ssh:ro \
	composer/composer --ansi --no-interaction $*

test: ## Run the unit and intergration testsuites.
test: lint test-matrix test-intergration

lint: ## Run phpcs against the code.
	docker run --rm -t -v $$(pwd):/opt/graze/queue graze/queue \
	composer lint --ansi

test-unit: ## Run the unit testsuite.
	docker run --rm -t -v $$(pwd):/opt/graze/queue graze/queue \
	composer test:unit --ansi

test-intergration: ## Run the integration testsuite.
	docker run --rm -t -v $$(pwd):/opt/graze/queue graze/queue \
	composer test:integration --ansi

test-matrix:
	docker run --rm -t -v $$(pwd):/opt/graze/queue -w /opt/graze/queue php:5.6-cli \
	vendor/bin/phpunit --testsuite unit
	docker run --rm -t -v $$(pwd):/opt/graze/queue -w /opt/graze/queue php:7.0-cli \
	vendor/bin/phpunit --testsuite unit

clean: ## Clean up any images.
	docker rmi graze/queue:latest

help: ## Show this help message.
	echo "usage: make [target] ..."
	echo ""
	echo "targets:"
	fgrep --no-filename "##" $(MAKEFILE_LIST) | fgrep --invert-match $$'\t' | sed -e 's/: ## / - /'
