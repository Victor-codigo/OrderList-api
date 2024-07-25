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

setup-dev: ## Sets the application up
	@echo "$(TITLE)Installing symfony dependecies$(END)"
	@echo "$(SEPARATOR)------------------------------$(END)"
	composer install

	@echo "$(TITLE)Migrating database, dev and test environments$(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console doctrine:database:create --env=dev
	bin/console doctrine:database:create --env=test
	bin/console doctrine:migrations:migrate --no-interaction --env=dev
	bin/console doctrine:migrations:migrate --no-interaction --env=test

	@echo "$(TITLE)Generating public and private keys$(END)"
	@echo "$(SEPARATOR)--------------------------------------------$(END)"
	bin/console lexik:jwt:generate-keypair
