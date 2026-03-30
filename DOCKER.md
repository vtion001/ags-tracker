# AGS Break Tracker - Docker Deployment

Production-ready Docker setup for self-hosting the AGS Break Tracker.

## Quick Start

```bash
# 1. Clone and setup
git clone https://github.com/YOUR_USERNAME/agssupabase.git
cd agssupabase

# 2. Configure environment
cp .env.example .env  # Edit with your values
# Generate GOD_MODE_TOKEN: openssl rand -hex 32

# 3. Build and deploy
make deploy
```

## Services

| Service | Port | Description |
|---------|------|-------------|
| app | 80 | Laravel application |
| mysql | 3306 | MySQL 8.0 database |
| redis | 6379 | Redis 7 cache |

## Commands

```bash
make up          # Start all containers
make down         # Stop all containers
make restart      # Restart all containers
make logs         # View logs
make shell        # Open shell in app container
make migrate      # Run migrations
make seed         # Seed test data
make deploy       # Full clean deploy
make clean        # Remove everything
```

## URLs

- App: http://localhost
- God Mode: http://localhost/god

## Test Accounts

| Role | Email | Password |
|------|-------|---------|
| Admin | admin@ags.com | agent707 |
| Team Lead | sarah.tl@ags.com | agent707 |
| Agent | john.agent@ags.com | agent707 |

## Environment Variables

Key variables to configure in `.env`:

```bash
APP_KEY=          # Generate: php artisan key:generate
GOD_MODE_TOKEN=   # Generate: openssl rand -hex 32
REVERB_APP_KEY=   # Generate: openssl rand -hex 16
REVERB_APP_SECRET= # Generate: openssl rand -hex 32
DB_PASSWORD=      # MySQL password
```

## Production with HTTPS

For HTTPS, use a reverse proxy (Traefik, Nginx Proxy Manager):

```bash
# Option 1: Traefik
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# Option 2: Manual nginx proxy with certbot
```

## Backup & Restore

```bash
# Backup database
docker-compose exec mysql mysqldump -uagsuser -pagsprod2026 ags_tracker > backup.sql

# Restore database
docker-compose exec -T mysql mysql -uagsuser -pagsprod2026 ags_tracker < backup.sql
```

## Troubleshooting

```bash
# Check container status
docker-compose ps

# View app logs
docker-compose logs app

# Restart app
docker-compose restart app

# Clear caches
make cache-clear
```
