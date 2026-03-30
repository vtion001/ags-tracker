# Railway Deployment Guide for AGS Break Tracker

This guide walks you through deploying the AGS Break Tracker application to Railway.

---

## Prerequisites

- A [Railway](https://railway.app) account
- A GitHub repository with the AGS Break Tracker code
- (Optional) A custom domain

---

## Step 1: Fork or Push Code to GitHub

If not already done:

```bash
cd agssupabase
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/agssupabase.git
git push -u origin main
```

---

## Step 2: Create Railway Project

1. Go to [railway.app](https://railway.app) and sign in
2. Click **New Project** → **Deploy from GitHub repo**
3. Select your `agssupabase` repository
4. Railway will detect the `railway.json` and `Dockerfile.railway`

---

## Step 3: Provision MySQL Database

1. In your Railway project, click **New** → **Database** → **MySQL**
2. Wait for the database to be provisioned
3. Click on the MySQL database → **Variables** tab
4. Copy the following variables (Railway auto-generates these):
   - `MYSQL_HOST`
   - `MYSQL_PORT`
   - `MYSQL_DATABASE`
   - `MYSQL_ROOT_PASSWORD`

---

## Step 4: Provision Redis Database

1. Click **New** → **Database** → **Redis**
2. Wait for Redis to be provisioned
3. Click on Redis → **Variables** tab
4. Copy the connection URL

---

## Step 5: Configure Environment Variables

1. Go to your Railway project → **Variables** tab
2. Add the following variables (some from Railway, some you generate):

### Generated Variables

**Generate APP_KEY:**
```bash
php artisan key:generate --show
```
Add as `APP_KEY` value.

**Generate GOD_MODE_TOKEN:**
```bash
openssl rand -hex 32
```
Add as `GOD_MODE_TOKEN` value.

### From MySQL

| Variable | Value |
|----------|-------|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | (your MySQL host from Step 3) |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | `railway` |
| `DB_USERNAME` | `root` |
| `DB_PASSWORD` | (your MySQL password from Step 3) |

### From Redis

| Variable | Value |
|----------|-------|
| `REDIS_HOST` | (your Redis host from Step 4) |
| `REDIS_PORT` | `6379` |

### Fixed Values

| Variable | Value |
|----------|-------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | (your Railway URL, e.g., `https://ags-breaker-xxx.up.railway.app`) |
| `LOG_LEVEL` | `notice` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `BROADCAST_CONNECTION` | `reverb` |

### Reverb Variables

Generate Reverb credentials:
```bash
openssl rand -hex 16  # For APP_KEY
openssl rand -hex 32  # For APP_SECRET
```

| Variable | Value |
|----------|-------|
| `REVERB_APP_ID` | `1` (or any number) |
| `REVERB_APP_KEY` | (generated above) |
| `REVERB_APP_SECRET` | (generated above) |
| `REVERB_HOST` | (your Railway domain) |
| `REVERB_PORT` | `443` |
| `REVERB_SCHEME` | `https` |
| `REVERB_ALLOWED_ORIGINS` | (your Railway domain, e.g., `ags-breaker-xxx.up.railway.app`) |

### VITE Variables

| Variable | Value |
|----------|-------|
| `VITE_REVERB_APP_KEY` | `${REVERB_APP_KEY}` |
| `VITE_REVERB_HOST` | `${REVERB_HOST}` |
| `VITE_REVERB_PORT` | `${REVERB_PORT}` |
| `VITE_REVERB_SCHEME` | `${REVERB_SCHEME}` |

---

## Step 6: Deploy

1. Railway should auto-deploy after environment variables are set
2. If not, click **Deploy** button
3. Watch the deployment logs for any errors

---

## Step 7: Run Migrations

1. After first deployment, run migrations:
2. Go to your project → **Deployments** tab
3. Click on the latest deployment → **SSH**
4. Run:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

---

## Step 8: Verify Deployment

1. Open your Railway URL (e.g., `https://ags-breaker-xxx.up.railway.app`)
2. You should see the login page
3. Test God Mode: go to `/god` and login

---

## Test Accounts

Default seeded accounts (password: `agent707`):

| Role | Email |
|------|-------|
| Admin | admin@ags.com |
| Team Lead | sarah.tl@ags.com |
| Agent | john.agent@ags.com |
| Agent | jane.agent@ags.com |
| Agent | bob.agent@ags.com |

---

## Troubleshooting

### Deployment Fails

- Check **Logs** tab for errors
- Common issue: Missing environment variables
- Ensure `APP_KEY` is properly set

### Database Connection Error

- Verify `DB_HOST`, `DB_PORT`, `DB_PASSWORD` are correct
- Ensure MySQL is fully provisioned (green status)

### 500 Error on Page Load

- Check Laravel logs: `storage/logs/laravel.log`
- Verify all required environment variables are set
- Try running `php artisan config:clear`

### WebSocket Not Connecting

- Verify `REVERB_*` environment variables
- Check browser console for CORS errors
- Ensure `REVERB_ALLOWED_ORIGINS` includes your domain

---

## Updating the App

Simply push to GitHub and Railway will auto-deploy:

```bash
git add .
git commit -m "Your changes"
git push origin main
```

---

## Adding a Custom Domain

1. Go to **Settings** → **Networking**
2. Click **Add Domain**
3. Enter your domain (e.g., `breaks.yourcompany.com`)
4. Add the DNS records shown by Railway
5. Wait for SSL certificate to provision

---

## Support

- [Railway Documentation](https://docs.railway.app)
- [Laravel Documentation](https://laravel.com/docs)
