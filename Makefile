.DEFAULT_GOAL := help

install:
	@composer install

phpcsfixer-audit: vendor ## Run php-cs-fixer audit
	@php ./vendor/bin/php-cs-fixer fix --diff --dry-run --no-interaction --ansi --verbose

phpcsfixer-fix: vendor ## Run php-cs-fixer fix
	@php ./vendor/bin/php-cs-fixer fix --verbose

phpstan: vendor ## Run phpstan audit
	@php ./vendor/bin/phpstan analyze

help: ## List of all commands
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

.PHONY: install phpcsfixer-audit phpcsfixer-fix help
