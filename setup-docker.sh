#!/bin/bash
# =====================================================
# AGS Break Tracker - Docker Setup Script
# =====================================================

set -e

echo "==============================================="
echo "AGS Break Tracker - Docker Setup"
echo "==============================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo "Creating .env file from template..."
    cp .env.example .env 2>/dev/null || echo "Warning: .env.example not found"
fi

# Generate GOD_MODE_TOKEN if not set
if grep -q "CHANGE_ME" .env 2>/dev/null; then
    echo "Generating God Mode token..."
    GOD_TOKEN=$(openssl rand -hex 32 2>/dev/null)
    if [ -n "$GOD_TOKEN" ]; then
        sed -i.bak "s/CHANGE_ME_TO_SOMETHING_SECURE/$GOD_TOKEN/g" .env
        rm -f .env.bak
        echo -e "${GREEN}God Mode token generated!${NC}"
    fi
fi

# Generate APP_KEY if not set
if grep -q "base64:Tk8Z" .env 2>/dev/null; then
    echo "Generating APP_KEY..."
    # The key is already set in the .env file
fi

# Build Docker images
echo ""
echo "Building Docker images..."
docker-compose build

# Start services
echo ""
echo "Starting services..."
docker-compose up -d

# Wait for MySQL to be ready
echo ""
echo "Waiting for MySQL to be ready..."
sleep 15

# Check MySQL health
for i in {1..30}; do
    if docker-compose exec -T mysql mysqladmin ping -h localhost --silent 2>/dev/null; then
        echo -e "${GREEN}MySQL is ready!${NC}"
        break
    fi
    echo "Waiting for MySQL... ($i/30)"
    sleep 2
done

# Run migrations
echo ""
echo "Running database migrations..."
docker-compose exec -T app php artisan migrate --force

# Seed database
echo ""
echo "Seeding database..."
docker-compose exec -T app php artisan db:seed --force || true

# Clear caches
echo ""
echo "Clearing caches..."
docker-compose exec -T app php artisan cache:clear || true
docker-compose exec -T app php artisan config:clear || true

echo ""
echo "==============================================="
echo -e "${GREEN}Setup Complete!${NC}"
echo "==============================================="
echo ""
echo "App URL:      http://localhost"
echo "God Mode:     http://localhost/god"
echo ""
echo "Test Accounts:"
echo "  Admin:       admin@ags.com / agent707"
echo "  Team Lead:   sarah.tl@ags.com / agent707"
echo "  Agent:       john.agent@ags.com / agent707"
echo ""
echo "Useful Commands:"
echo "  make logs     - View logs"
echo "  make shell    - Open shell"
echo "  make restart  - Restart services"
echo ""
