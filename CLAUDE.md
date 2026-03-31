# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AGS Break Tracker is a Laravel application for tracking employee breaks (15m/60m), monitoring overbreaks, and managing support tickets. It uses MySQL/Redis in Docker, supports TOTP 2FA, and integrates with Slack and ElevenLabs for alerts.

## Tech Stack

- **Framework**: Laravel 11 (PHP 8.2+)
- **Database**: MySQL 8.0 (local dev), PostgreSQL (Render/Railway production)
- **Cache**: Redis 7
- **Auth**: JWT (php-open-source-saver/jwt-auth) + Google2FA for TOTP
- **Frontend**: Laravel Breeze (Blade + Tailwind) + Vite
- **Real-time**: Laravel Reverb
- **External APIs**: Slack, ElevenLabs

## Key Architecture

### Roles & Access Control
Three roles via `User::role`: `admin`, `tl` (team lead), `agent`
- Admins cannot start breaks
- Team leads can view their team's breaks via `tl_email` relationship
- Agents start/end breaks and view their own history

### Break System
- `BreakService` manages break lifecycle
- Two break types: `15m` (15 minutes) and `60m` (1 hour)
- Two categories: `break` and `lunch`
- `ActiveBreak` → `BreakHistory` on end (with overbreak calculation)
- Events: `BreakStarted`, `BreakEnded`, `BreakOverbreakAlert`

### Models
- `User` — Extended with role, department, TOTP, profile fields
- `ActiveBreak` — Currently active breaks
- `BreakHistory` — Completed breaks with overbreak tracking
- `SupportTicket` / `SupportTicketComment` — Ticket system
- `TrustedDevice` — TOTP trusted devices

### Services
- `BreakService` — Core break logic, stats, team queries
- `SlackService` — Slack webhook notifications
- `ElevenLabsService` — Voice alert integration

## Common Commands

```bash
# Development (local without Docker)
composer dev          # Run all dev services (server, queue, logs, vite)

# Testing
php artisan test                 # Run all tests
php artisan test --testsuite=Unit      # Unit tests only
php artisan test tests/Feature/Auth/    # Specific test file

# Docker development
make up          # Start all containers
make down        # Stop containers
make logs        # View logs
make shell       # Shell into app container
make migrate     # Run migrations
make seed        # Seed test data

# Laravel
php artisan route:list           # List routes
php artisan about                 # App info
php artisan tinker              # REPL
```

## Environment

Key `.env` variables:
- `APP_KEY` — Laravel app key
- `DB_*` — Database connection (MySQL)
- `REDIS_*` — Redis connection
- `GOD_MODE_TOKEN` — Token for /god bypass login
- `SLACK_*` / `ELEVENLABS_*` — External integrations
- `REVERB_*` — Real-time/websocket config

## Routes

- `/dashboard` — Main break tracking dashboard
- `/overbreaks` — Overbreak monitoring
- `/tickets` — Support ticket system
- `/admin/tickets` — Admin-only ticket management
- `/god` — Development bypass login (disabled in production)
- `/dev-login` — Development login with TOTP skip

## Broadcasting / Real-time

- `BROADCAST_CONNECTION=log` in production (Render) - events logged but not broadcast
- `resources/js/echo.js` has conditional Echo initialization - only connects when `VITE_REVERB_HOST` is set
- If real-time is needed, deploy Laravel Reverb server separately or use Pusher

Tests use `tests/Feature/` and `tests/Unit/` directories. PHPUnit configured in `phpunit.xml` with in-memory array drivers for cache/session/queue and BROADCAST_CONNECTION=log to avoid WebSocket errors.

## Directory Structure

```
app/
  Console/Commands/     # Artisan commands (ImportSupabaseData, CheckOverbreaks, SetupTotp)
  Events/               # BreakStarted, BreakEnded, BreakOverbreakAlert
  Http/
    Controllers/        # BreakController, OverbreakController, TicketController, Admin*, Auth*, Alert*
    Middleware/         # AdminOrTeamLeadMiddleware
    Requests/           # Form request validation
  Models/               # User, ActiveBreak, BreakHistory, SupportTicket, TrustedDevice
  Services/             # BreakService, SlackService, ElevenLabsService
bootstrap/              # Laravel bootstrap
config/                 # App configuration
database/
  factories/            # Model factories for testing
  migrations/           # Database schema
  seeders/              # Database seeders
routes/                 # web.php, auth.php, console.php, channels.php
tests/                  # Feature and Unit tests
docker/                 # nginx.conf, mysql.cnf, redis.conf, entrypoint.sh
```

## Deployment

- **Docker**: `docker-compose.yml` with app, mysql, redis services
- **Railway/Render**: `Dockerfile.railway`, `render.yaml`, `railway.json`
- **Production**: Uses `docker-compose.prod.yml` for HTTPS reverse proxy setup

### Render Deployment Notes

- **Dockerfile used**: Render defaults to `./Dockerfile` (not `Dockerfile.railway`)
- **Ensure `libpq-dev` is installed** for Alpine images so `pdo_pgsql` extension builds
- **Vite assets**: `/public/build` must NOT be gitignored for production - required for `ViteManifestNotFoundException` fix
- **Sessions migration**: Already included in `0001_01_01_000000_create_users_table.php` - do NOT add a separate sessions migration
- **entrypoint.sh**: Use `migrate --force` NOT `migrate:fresh` (fresh destroys all data!)

### PHPUnit Testing

- `phpunit.xml` must have `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:` uncommented
- Without this, tests try to connect to MySQL from `.env`

Default test accounts after seeding: `admin@ags.com`, `sarah.tl@ags.com`, `john.agent@ags.com` (password: `agent707`)
