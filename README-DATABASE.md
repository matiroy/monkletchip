# Database setup (IONOS MariaDB)

The app connects to your IONOS MariaDB for progress and custom levels. The same config works **locally** and **when deployed**.

## 1. Create `.env`

Copy the example and add your database password:

```bash
cp .env.example .env
```

Edit `.env` and set `DB_PASSWORD` to the password from your IONOS database panel.  
(Other values are pre-filled from your IONOS details; change them only if your host/name differ.)

## 2. Run locally (PHP)

```bash
php -S localhost:8000 router.php
```

Open **http://localhost:8000**. The PHP API will use the IONOS database; progress and custom levels are also mirrored in `localStorage` as fallback.

**Optional – run with Node instead:** `npm install` then `npm start` (http://localhost:3000).

## 3. Deploy

**PHP (recommended):** Use IONOS Deploy Now as a PHP project; set the same variables as GitHub Secrets. See [README-DEPLOY-NOW.md](README-DEPLOY-NOW.md).

**Node:** Deploy this folder and run `npm start` on a host that supports Node (e.g. IONOS, Railway, Render).  
Set the **same** environment variables in the host’s dashboard:

- `DB_HOST` = `db5019938968.hosting-data.io`
- `DB_PORT` = `3306`
- `DB_USER` = `dbu2298835`
- `DB_PASSWORD` = your database password
- `DB_NAME` = `dbs15396335` (or the name shown in IONOS)

If the front-end is served by this same server, no extra config is needed. If you use a separate static host, point it at your deployed API URL and set `API_BASE` in the front-end to that URL (e.g. `https://your-app.example.com`).

## Behaviour

- **With server + DB:** Progress and custom levels are read/written to MariaDB and stay in sync across devices.
- **Without server (e.g. opening `index.html` only):** The app still works using `localStorage` only.
