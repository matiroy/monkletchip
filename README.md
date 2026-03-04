# Laser Labyrinth

Repo for my kid. A small puzzle game with progress and custom levels stored in a database. The app runs on **PHP** (IONOS Deploy Now or local).

## Quick start (PHP)

1. **Copy env and set your DB password**
   ```bash
   cp .env.example .env
   ```
   Edit `.env` and set `DB_PASSWORD` (from your IONOS database panel).

2. **Run with PHP**
   ```bash
   php -S localhost:8000 router.php
   ```
   Open **http://localhost:8000**. The game and API use your IONOS MariaDB.

## Deploy to IONOS (PHP project)

1. Connect this repo in [IONOS Deploy Now](https://www.ionos.com/hosting/deploy-now) and choose **PHP project**.
2. Add GitHub Secrets: `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, `DB_NAME` (see [README-DEPLOY-NOW.md](README-DEPLOY-NOW.md)).
3. Push; the site and `/api/*` run on PHP with your database.

See **[README-DEPLOY-NOW.md](README-DEPLOY-NOW.md)** for full Deploy Now steps and **[README-DATABASE.md](README-DATABASE.md)** for database details.

## Structure

| Path | Role |
|------|------|
| `index.html` | Game UI (static) |
| `config.js` | API base URL (`''` = same origin) |
| `api/index.php` | REST API (progress + custom levels) |
| `router.php` | PHP built-in server router (local dev) |
| `.htaccess` | Apache: route `/api/*` to `api/index.php` |
| `.env` | DB credentials (create from `.env.example`) |

**Windows (PHP not in PATH):** Run `npm run php` to start the server using the project’s PHP path.
