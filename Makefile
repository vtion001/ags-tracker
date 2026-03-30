# =====================================================
# AGS Break Tracker - Docker Makefile
# =====================================================

.PHONY: help build up down restart logs shell migrate seed ssl-clean

# Default target
help:
	@echo "AGS Break Tracker - Docker Commands"
	@echo "=================================="
	@echo "make build      - Build Docker images"
	@echo "make up         - Start all containers"
	@echo "make down       - Stop all containers"
	@echo "make restart    - Restart all containers"
	@echo "make logs       - View logs (all services)"
	@echo "make logs-app   - View app logs only"
	@echo "make shell      - Open shell in app container"
	@echo "make migrate    - Run database migrations"
	@echo "make seed       - Seed database with test data"
	@echo "make tinker     - Open Laravel Tinker"
	@echo "make restart-app - Rebuild and restart app only"
	@echo "make clean      - Remove all containers and volumes"
	@echo ""

# Build Docker images
build:
	docker-compose build --no-cache

build-fast:
	docker-compose build

# Start all containers
up:
	docker-compose up -d
	@echo "Waiting for services to be ready..."
	@sleep 10
	@docker-compose ps

# Stop all containers
down:
	docker-compose down

# Restart all containers
restart: down up

# View all logs
logs:
	docker-compose logs -f

# View app logs only
logs-app:
	docker-compose logs -f app

# Open shell in app container
shell:
	docker-compose exec app sh

# Open MySQL CLI
mysql:
	docker-compose exec mysql mysql -uagsuser -pagsprod2026 ags_tracker

# Run migrations
migrate:
	docker-compose exec -T app php artisan migrate --force

# Rollback migrations
migrate-rollback:
	docker-compose exec -T app php artisan migrate:rollback --force

# Seed database
seed:
	docker-compose exec -T app php artisan db:seed --force

# Open Laravel Tinker
tinker:
	docker-compose exec app php artisan tinker

# Rebuild and restart app only
restart-app:
	docker-compose build app && docker-compose up -d app

# Clear all caches
cache-clear:
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

# Optimize for production
optimize:
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

# Remove all containers and volumes (CLEAN slate)
clean:
	docker-compose down -v --remove-orphans
	@echo "All containers and volumes removed."

# Full deploy (clean build + migrate + seed)
deploy: clean build up migrate seed
	@echo ""
	@echo "Deployment complete!"
	@echo "App: http://localhost"
	@echo "God Mode: http://localhost/god"
	@echo ""
	@echo "Test accounts:"
	@echo "  Admin: admin@ags.com / agent707"
	@echo "  TL: sarah.tl@ags.com / agent707"
	@echo "  Agent: john.agent@ags.com / agent707"

# SSL certificate (for production with custom domain)
ssl-cert:
	@echo "To enable HTTPS, add your domain to .env and use a reverse proxy"
	@echo "Recommended: Traefik or Nginx Proxy Manager with auto-SSL"
