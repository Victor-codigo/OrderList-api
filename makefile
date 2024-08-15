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
TEXT=\033[0;32m
END=\033[0m

setup-dev: ## Sets the application up for development
	@echo "$(TITLE)Installing symfony dependecies$(END)"
	@echo "$(SEPARATOR)------------------------------$(END)"
	composer install

	@echo "$(TITLE)Generating public and private keys$(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console lexik:jwt:generate-keypair --overwrite

	@echo "$(TITLE)Migrating database, dev and test environments$(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console doctrine:database:create --env=dev
	bin/console doctrine:database:create --env=test
	bin/console doctrine:migrations:migrate --no-interaction --env=dev
	bin/console doctrine:migrations:migrate --no-interaction --env=test

	@echo "$(TITLE)Application ready for development.$(END)"

setup-deploy: ## Sets the application up for production deploy
	@echo "$(TITLE)Installing symfony dependecies$(END)"
	@echo "$(SEPARATOR)------------------------------$(END)"
	composer install

	-bin/console --env=prod
	@echo "$(TITLE)Ignore this error message$(END)"

	@echo "$(TITLE)Security: APP_SECRET $(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console secrets:set APP_SECRET --random=32 --quiet --env=prod

	@echo "$(TITLE)Security: SYSTEM_KEY $(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console secrets:set SYSTEM_KEY --random=32 --quiet --env=prod

	@echo "$(TITLE)Security: JWT_PASSPHRASE $(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console secrets:set JWT_PASSPHRASE --random=32 --quiet --env=prod

	@echo "$(TITLE)Generating public and private keys$(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console lexik:jwt:generate-keypair --overwrite --quiet --env=prod

	@echo "$(TITLE)Security: DATABASE_URL $(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	echo -n "mysql://${DB_USER}:${DB_PASSWORD}@${DB_HOST}:${DB_PORT}/${DB_NAME}?serverVersion=${DB_VERSION}&charset=utf8mb4" \
	| bin/console secrets:set DATABASE_URL - --quiet --env=prod

	echo -n "${MAILER_DSN}" | bin/console secrets:set MAILER_DSN - --quiet --env=prod

	@echo "$(TITLE)Removing Composer development dependecies$(END)"
	@echo "$(SEPARATOR)------------------------------$(END)"
	APP_ENV=prod composer update --no-dev --optimize-autoloader

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
	rm phpstan.dist.neon
	rm phpunit.xml.dist
	rm README.md
	rm rector.php
	rm -rf tools
	rm -rf tests
	rm -rf .docker
	rm -rf migrations
	rm -rf .git
	rm -rf .github
	rm makefile
	rm -rf var