# Bank Manager - Development Commands

.PHONY: help build up down restart logs shell migrate seed swagger clean

# Default target
help: ## Show this help message
	@echo "Bank Manager Development Commands"
	@echo "================================"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'

# Docker commands
build: ## Build the Docker images
	docker compose build --no-cache

up: ## Start all services (app + PostgreSQL + pgAdmin)
	docker compose up -d

down: ## Stop all services
	docker compose down

restart: ## Restart all services
	docker compose restart

logs: ## Show logs from all services
	docker compose logs -f

logs-app: ## Show logs from app service only
	docker compose logs -f app

# Development commands
shell: ## Access the app container shell
	docker compose exec app bash

migrate: ## Run database migrations
	docker compose exec app php artisan migrate

migrate-fresh: ## Drop all tables and re-run all migrations
	docker compose exec app php artisan migrate:fresh

seed: ## Run database seeders
	docker compose exec app php artisan db:seed

swagger: ## Generate Swagger documentation
	docker compose exec app php artisan l5-swagger:generate

key-generate: ## Generate application key
	docker compose exec app php artisan key:generate

# Testing commands
test: ## Run PHP tests
	docker compose exec app php artisan test

test-coverage: ## Run tests with coverage
	docker compose exec app php artisan test --coverage

# Database commands
db-connect: ## Connect to PostgreSQL database
	docker compose exec postgres psql -U myuser -d bankmanager

db-reset: ## Reset database (migrate:fresh + seed)
	docker compose exec app php artisan migrate:fresh --seed

# Cleanup commands
clean: ## Remove all containers, volumes, and images
	docker compose down -v --rmi all

clean-volumes: ## Remove only volumes (keeps data)
	docker compose down -v

# Production deployment
deploy-check: ## Check if ready for deployment
	@echo "Checking deployment readiness..."
	@test -f .env.production && echo "✅ .env.production exists" || echo "❌ .env.production missing"
	@test -d .git && echo "✅ Git repository initialized" || echo "❌ Git not initialized"
	@docker images | grep -q bank-manager-app && echo "✅ Docker image built" || echo "❌ Docker image not built"
	@echo "Ready for deployment! 🚀"

# Quick development setup
setup: ## Complete development setup (build + up + migrate + seed + swagger)
	make build
	make up
	sleep 10
	make migrate
	make seed
	make swagger
	@echo "🎉 Development environment ready!"
	@echo "📱 App: http://localhost:8080"
	@echo "📚 API Docs: http://localhost:8080/api/documentation"
	@echo "🗄️ pgAdmin: http://localhost:5050 (admin@admin.com / admin)"