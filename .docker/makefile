#!/bin/bash

# -------------------------------------------
# CONSTANTS
# -------------------------------------------
export USER_ID = $(shell id -u)
export GROUP_ID = $(shell id -g)
PATH_ENV_FILE = ./build/env/.env
# -------------------------------------------

include ${PATH_ENV_FILE}

# -------------------------------------------
# FUNCTIONS
# -------------------------------------------

# Executes docket-compose
dockerCompose = \
	docker compose \
	-f ./build/docker-compose.yml \
	--env-file ${PATH_ENV_FILE} \
	-p ${APP_NAME} \
	$(1)

executeInPhpContainer = \
	$(call dockerCompose, \
	exec \
	--user=${USER_ID}:${GROUP_ID} \
	$(1))

executeInPhpContainerAsRoot = \
	$(call dockerCompose, \
	exec \
	-u 0 \
	$(1))
# -------------------------------------------

# -------------------------------------------



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

up: ## Rise up the containers
	$(call dockerCompose, up -d)
	$(call dockerCompose, ps)

build-no-cache:	## Builds containers without cache
	$(call dockerCompose, build --no-cache)



build-cache: ## Builds containers with cache
	$(call dockerCompose, build)

down: ## Set down containers
	$(call dockerCompose, down)

start: ## Starts containers
	$(call dockerCompose, start)

stop: ## Stops containers
	$(call dockerCompose, stop)

restart: ## Restart containers
	$(call dockerCompose, restart)

ps: ## List containers
	$(call dockerCompose, ps)

logs: ## Show logs
	$(call dockerCompose, logs)

down-build-up: ## Set down, builds and rise up containers
	$(call dockerCompose, down)
	$(call dockerCompose, build)
	$(call dockerCompose, up -d)
	$(call dockerCompose, ps)

build-up: ## Builds and rise up containers
	$(call dockerCompose, build)
	$(call dockerCompose, up -d)
	$(call dockerCompose, ps)

bash: ## Execute bash in php container
	$(call executeInPhpContainer, php bash)

root: ## Execute bash in php container as root
	$(call executeInPhpContainerAsRoot, php bash)
