# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AGS Break Tracker is a Laravel application for tracking employee breaks (15m/60m), monitoring overbreaks, and managing support tickets. It uses MySQL/Redis in Docker, supports TOTP 2FA, and integrates with Slack and ElevenLabs for alerts.

## Tech Stack

- **Framework**: Laravel 11 (PHP 8.2+)
- **Database**: MySQL 8.0 (local dev), PostgreSQL (Render/Railway production)
- **Cache**: Redis 7
- **Auth**: Laravel Breeze (session-based) + Google OAuth (Socialite) + Google2FA for TOTP
- **Frontend**: Laravel Breeze (Blade + Tailwind) + Vite
- **Real-time**: Laravel Reverb (optional; production uses `BROADCAST_CONNECTION=log`)
- **External APIs**: Slack, ElevenLabs

## Architecture

### User Status & Onboarding Flow

Users have a `status` field: `pending` or `active`. New agents start as `pending` and require admin approval. Team leads are auto-approved to `active` after onboarding. Admins/TLs manage pending users via `/onboarding/pending`.

- `pending`: Initial state after registration (or onboarding for agents), cannot start breaks
- `active`: Can start/end breaks normally

The onboarding flow has 4 steps: role selection → profile → emergency contact → security (TOTP setup). Agents can skip optional steps. Team leads skip approval since they're auto-activated.

### User Roles & Relationships

Three roles via `User::role`: `admin`, `tl` (team lead), `agent`
- **Admins**: Cannot start breaks, can view all tickets, manage pending users
- **Team Leads**: View team breaks via `tl_email` relationship, manage team tickets
- **Agents**: Start/end breaks, view own history only

Users belong to a `Team` via `team_id` and have a `tl_email` pointing to their team lead's email.

### Break System Lifecycle

```
Agent starts break → ActiveBreak created → BreakStarted event broadcast
                                                          ↓
                                              PrivateChannel 'team.{tlEmail}'
                                              PrivateChannel 'admin'
                                                          ↓
Agent ends break → BreakHistory created → BreakEnded event
                    └─ over_minutes calculated
                         ↓
                    If overbreak → BreakOverbreakAlert event + Cache audio alert
```

- Two break types: `15m` (15 min) and `60m` (1 hour)
- Two categories: `break` and `lunch`
- `BreakService` is the single source of truth for break logic

### Broadcasting Channels

Private channels defined in `routes/channels.php`:
- `admin`: All admins
- `team.{tlEmail}`: Team lead and admins for that team

### Support Ticket System

Tickets are user-created support requests. Admins/TLs can access `/admin/tickets` for management. Comments support both user and admin replies.

### Google OAuth Flow

Google OAuth (`GoogleController@callback`) creates users in `pending` status with `role=agent`. If email already exists with a password but no Google ID, it rejects linking (user must login locally first). After OAuth login, users with incomplete onboarding are redirected to `/onboarding/role`.

## Common Commands

```bash
# Docker development
make up          # Start all containers
make down        # Stop containers
make logs        # View all logs
make logs-app    # App logs only
make shell       # Shell into app container
make migrate     # Run migrations
make seed        # Seed test data

# Laravel
php artisan route:list           # List routes
php artisan about                 # App info
php artisan tinker              # REPL

# Alert commands
php artisan alerts:check-overbreaks  # Check active breaks for overages, send alerts

# Testing
php artisan test                          # All tests
php artisan test --testsuite=Unit          # Unit tests only
php artisan test --testsuite=Feature       # Feature tests only
php artisan test tests/Feature/Auth/       # Specific test directory
php artisan test --filter=TestName         # Filter by test name
```

## Key Models & Services

**Models** (`app/Models/`):
- `User` — role, department, team_id, tl_email, totp_*, status, onboarding_completed
- `Team` — has many users, has many team leads via tl_email
- `ActiveBreak` — current break (deleted on end)
- `BreakHistory` — completed breaks with duration/overbreak data
- `SupportTicket` / `SupportTicketComment` — ticket system
- `TrustedDevice` — TOTP trusted devices

**Services** (`app/Services/`):
- `BreakService` — start/end breaks, team queries, stats (compliance, peer stats)
- `SlackService` — webhook notifications
- `ElevenLabsService` — voice alert generation

**Events** (`app/Events/`):
- `BreakStarted` — broadcasts to admin + team channels
- `BreakEnded` — broadcasts to admin + team channels
- `BreakOverbreakAlert` — triggers voice/Slack alerts

## Routes

- `/` → redirects to `/dashboard`
- `/auth/google` — Google OAuth redirect
- `/auth/google/callback` — Google OAuth callback
- `/dashboard` — Main break tracking (agents see own, TLs see team)
- `/overbreaks` — Live overbreak monitoring dashboard
- `/tickets` — User support tickets
- `/admin/tickets` — Admin/TL ticket management
- `/profile` — User profile editing
- `/totp/setup` — Two-factor authentication setup
- `/onboarding/*` — Multi-step onboarding flow (role, profile, emergency, security)
- `/god` — Development bypass login (disabled in production)
- `/dev-login` — Development login with TOTP skip

## Environment Variables

Key `.env` variables:
- `APP_KEY` — Laravel app key
- `APP_URL` — Application URL (used for OAuth callbacks, CORS)
- `DB_*` — Database connection
- `REDIS_*` — Redis connection
- `GOD_MODE_TOKEN` — Token for /god bypass login
- `REVERB_*` — Real-time broadcasting (VITE_* prefix for frontend)
- `SLACK_WEBHOOK_URL` — Slack notifications
- `ELEVENLABS_*` — Voice alerts

## Deployment

- **Docker**: `docker-compose.yml` with app, mysql, redis services
- **Production**: Uses `docker-compose.prod.yml` for HTTPS reverse proxy
- **Railway/Render**: `Dockerfile`, `render.yaml`

### Render Deployment Notes

- `render.yaml` specifies `./Dockerfile.railway` explicitly (not the default `./Dockerfile`)
- `libpq-dev` required in Alpine for `pdo_pgsql` extension
- `/public/build` must NOT be gitignored for Vite assets
- `entrypoint.sh` uses `migrate --force` (NOT `migrate:fresh`)
- Sessions table already in `0001_01_01_000000_create_users_table.php` — no separate migration needed
- `SESSION_SECURE_COOKIE=true` required for CSRF cookies on HTTPS

### PHPUnit Testing

`phpunit.xml` configures in-memory SQLite (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`) plus array drivers for cache/session/queue. This avoids external dependencies during tests.

## Directory Structure

```
app/
  Console/Commands/     # Artisan commands (alerts:check-overbreaks, etc.)
  Events/              # BreakStarted, BreakEnded, BreakOverbreakAlert
  Http/
    Controllers/       # BreakController, OverbreakController, TicketController, etc.
    Middleware/         # AdminOrTeamLeadMiddleware
  Models/               # User, ActiveBreak, BreakHistory, Team, SupportTicket, etc.
  Services/            # BreakService, SlackService, ElevenLabsService
bootstrap/             # Laravel bootstrap
config/                 # App configuration
database/
  factories/           # Model factories for testing
  migrations/          # Schema (users, active_breaks, break_history, tickets, teams)
  seeders/             # DatabaseSeeder with test accounts
docker/                 # nginx.conf, mysql.cnf, redis.conf, supervisord.conf, entrypoint.sh
routes/                 # web.php, auth.php, channels.php, console.php
tests/                  # Feature and Unit tests
```

## Test Accounts

After seeding: `admin@ags.com`, `sarah.tl@ags.com`, `john.agent@ags.com` (password: `agent707`)
