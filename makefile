#!/bin/bash

# -------------------------------------------
# TARGETS
# -------------------------------------------
help: ## Show help
	@echo -e '\e[33mUsage:\e[0m'
	@echo '  make [target]'
	@echo
	@echo -e '\e[33m Targets:'
	@awk 'BEGIN {FS = ":.*##"; printf "\033[36m\033[0m"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[32m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)
# -------------------------------------------

TITLE=\n\033[1;32m
SEPARATOR=\033[1;32m
END=\033[0m

setup-dev: ## Sets the application up for development
	@echo "$(TITLE)Installing symfony dependecies$(END)"
	@echo "$(SEPARATOR)------------------------------$(END)"
	composer install

	@echo "$(TITLE)Generating public and private keys$(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console lexik:jwt:generate-keypair

	@echo "$(TITLE)Migrating database, dev and test environments$(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console doctrine:database:create --env=dev
	bin/console doctrine:database:create --env=test
	bin/console doctrine:migrations:migrate --no-interaction --env=dev
	bin/console doctrine:migrations:migrate --no-interaction --env=test

setup-prod: ## Sets the application up for production
	@echo "$(TITLE)Installing symfony dependecies$(END)"
	@echo "$(SEPARATOR)------------------------------$(END)"
	composer install

	@echo "$(TITLE)Generating public and private keys$(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console lexik:jwt:generate-keypair

	@echo "$(TITLE)Security: APP_SECRET $(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console secrets:set APP_SECRET --random=32 --env=prod

	@echo "$(TITLE)Security: DB_USER $(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console secrets:set DB_USER --env=prod

	@echo "$(TITLE)Security: DB_PASSWORD $(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console secrets:set DB_PASSWORD --env=prod

	@echo "$(TITLE)Security: DB_HOST $(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console secrets:set DB_HOST --env=prod

	@echo "$(TITLE)Security: DB_PORT $(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console secrets:set DB_PORT --env=prod

	@echo "$(TITLE)Security: DB_VERSION $(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console secrets:set DB_VERSION --env=prod

	@echo "$(TITLE)Migrating database, dev and test environments$(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console doctrine:database:create --env=prod
	bin/console doctrine:migrations:migrate --no-interaction --env=prod

	@echo "$(TITLE)Removing Composer development dependecies$(END)"
	@echo "$(SEPARATOR)------------------------------$(END)"
	composer update --no-dev --optimize-autoloader

	@echo "$(TITLE)Optimizing environment variables$(END)"
	@echo "$(SEPARATOR)------------------------------$(END)"
	composer dump-env prod

	@echo "$(TITLE)Removing development files$(END)"
	@echo "$(SEPARATOR)------------------------------$(END)"
	rm .env
	rm .env.dev
	rm .env.test
	rm .env.prod
	rm .gitignore
	rm .php-cs-fixer.dist.php
	rm phpstan.neon
	rm phpunit.xml.dist
	rm phpunit.result.cache
	rm README.md
	rm rector.php
