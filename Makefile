# Laravel Boilerplate Docker Makefile

.PHONY: help build up down restart setup-domain setup-ssl setup-ssl-ca shell logs

# Default domain
DOMAIN ?= laravel.test

# Detect docker compose command
DOCKER_COMPOSE := $(shell command -v docker-compose 2> /dev/null)
ifndef DOCKER_COMPOSE
    DOCKER_COMPOSE := docker compose
endif

help: ## Show this help message
	@echo 'usage: make [target] [DOMAIN=yourdomain.test]'
	@echo ''
	@echo 'targets:'
	@egrep '^(.+)\:\ ##\ (.+)' $(MAKEFILE_LIST) | column -t -c 2 -s ':#'

build: ## Build docker containers
	$(DOCKER_COMPOSE) build

up: ## Start docker containers
	$(DOCKER_COMPOSE) up -d

down: ## Stop docker containers
	$(DOCKER_COMPOSE) down

restart: ## Restart docker containers
	$(DOCKER_COMPOSE) restart

setup-domain: ## Setup custom domain (usage: make setup-domain DOMAIN=myapp.test)
	@chmod +x ./docker/bin/setup-domain
	@./docker/bin/setup-domain $(DOMAIN)

setup-ssl: ## Generate SSL certificate for domain
	@chmod +x ./docker/bin/setup-ssl
	@./docker/bin/setup-ssl $(DOMAIN)

setup-ssl-ca: ## Install certificate authority on host
	@chmod +x ./docker/bin/setup-ssl-ca
	@./docker/bin/setup-ssl-ca

shell: ## Access application shell
	$(DOCKER_COMPOSE) exec laravel.test bash

shell-nginx: ## Access nginx shell
	$(DOCKER_COMPOSE) exec nginx sh

dev: ## Run development server inside container
	$(DOCKER_COMPOSE) exec laravel.test npm run dev

install-deps: ## Install composer and npm dependencies
	$(DOCKER_COMPOSE) exec laravel.test composer install
	$(DOCKER_COMPOSE) exec laravel.test npm install

fix-node-modules: ## Fix node modules for ARM64
	@chmod +x ./docker/bin/fix-node-modules.sh
	@./docker/bin/fix-node-modules.sh

logs: ## View container logs
	$(DOCKER_COMPOSE) logs -f

logs-nginx: ## View nginx logs
	$(DOCKER_COMPOSE) logs -f nginx

clean: ## Clean up volumes and containers
	$(DOCKER_COMPOSE) down -v

fresh-start: clean build up setup-domain ## Fresh start with custom domain setup
	@echo "Fresh installation complete!"
	@echo "Access your application at: https://$(DOMAIN)"
