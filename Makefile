.DEFAULT_GOAL := help
###.silent := true

.PHONY: help
help:
	@echo "Usage:"
	@echo "     make [command]"
	@echo
	@echo "Available commands:"
	@grep -h '^[^#[:space:]].*:' Makefile | \
	  grep -v '^default' |\
	  grep -v '^\.' |\
	  grep -v '=' |\
	  grep -v '^_' |\
	  sed 's/://' |\
	  xargs -n 1 echo ' -' |\
	  sort
	@echo

.PHONY: clean
clean:
	- rm -r vendor/*
	- rm -r public/build/*
	$(MAKE) docker-compose-down

.PHONY: host-shell-runner
host-shell-runner:
	docker compose exec runner bash

.PHONY: host-shell-builder
host-shell-builder:
	docker compose exec builder sh

.PHONY: host-docker-stats
host-docker-stats:
	@docker stats --format "{{.ID}} {{.CPUPerc}} {{.MemUsage}} {{.Name}}"

.PHONY: host-test-install
host-test-install:
	docker compose exec runner bash -c "APP_ENV=test composer install --prefer-dist || true"

.PHONY: host-test-phpunit
host-test-phpunit:
	docker compose exec runner bash -c "vendor/bin/phpunit --configuration phpunit.xml.dist --testdox"

.PHONY: host-test-phpstan
host-test-phpstan:
	docker compose exec runner bash -c "vendor/bin/phpstan analyse src --level 6"

.PHONY: host-test-cs-fixer
host-test-cs-fixer:
	docker compose exec runner bash -c "vendor/bin/php-cs-fixer fix --verbose --dry-run"

.PHONY: host-test-tsc
host-test-tsc:
	docker compose exec builder sh -c "yarn run tsc"

.PHONY: host-test-eslint
host-test-eslint:
	docker compose exec builder sh -c "yarn run eslint assets/"

.PHONY: host-test-jest
host-test-jest:
	docker compose exec builder sh -c "yarn run jest assets"

.PHONY: host-test-php
host-test-php:
	$(MAKE) host-test-install
	$(MAKE) host-test-phpunit
	$(MAKE) host-test-phpstan
	$(MAKE) host-test-cs-fixer

.PHONY: host-test-ts
host-test-ts:
	$(MAKE) host-test-tsc
	$(MAKE) host-test-eslint
	$(MAKE) host-test-jest

.PHONY: host-test
host-test:
	$(MAKE) host-test-php
	$(MAKE) host-test-ts

.PHONY: host-docker-logs
host-docker-logs:
	docker-compose logs -f

.PHONY: host-docker-loki-logs
host-docker-loki-logs:
	docker exec -it sylar-runner logcli --addr "http://sylar-loki:3100" query '{host=~ ".+"}' --tail

.PHONY: host-restart-worker
host-restart-worker:
	docker compose exec runner supervisorctl restart php-worker

.PHONY: host-restart-builder
host-restart-builder:
	docker compose exec builder supervisorctl restart typescript-watch
	docker compose exec builder supervisorctl restart codegen-watch

docker-compose-up:
	docker compose -f docker-compose.yaml up -d --build

docker-compose-up-dev:
	docker compose -f docker-compose.yaml -f docker-compose.debug.yaml up -d --build

docker-compose-up-dev-amd64:
	docker compose -f docker-compose.yaml -f docker-compose.debug.yaml -f docker-compose.amd64.yaml up -d --build

docker-compose-down:
	docker compose -f docker-compose.yaml -f docker-compose.debug.yaml -f docker-compose.amd64.yaml down --remove-orphans

.PHONY: cs-fixer-fix
cs-fixer-fix:
	vendor/bin/php-cs-fixer fix --verbose

