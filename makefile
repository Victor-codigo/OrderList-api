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


setup: ## Sets the application up
	@echo 'Installing symfony dependecies'
	@echo '------------------------------'
	composer install

	@echo 'Migrating database, dev and test enviroments'
	@echo '--------------------------------------------'
	bin/console doctrine:migrations:migrate --no-interaction
	bin/console doctrine:migrations:migrate --no-interaction --env=test

	@echo 'Generating public and private keys'
	@echo '--------------------------------------------'
	bin/console lexik:jwt:generate-keypair
