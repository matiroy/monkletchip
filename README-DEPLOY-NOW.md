# Deploy with IONOS Deploy Now

Use a **PHP project** so the app and API run on IONOS with your IONOS MariaDB.

---

## 1. Create the project in Deploy Now

1. In [IONOS Deploy Now](https://www.ionos.com/hosting/deploy-now), connect your GitHub repo.
2. When asked, choose **PHP project** (not static).
3. Set the **publish directory** to the repo root (e.g. `./`), so that `index.html`, `api/`, and `.htaccess` are deployed.

## 2. Database credentials

1. In GitHub: **Settings → Secrets and variables → Actions**, add:
   - `DB_HOST` = `db5019938968.hosting-data.io`
   - `DB_PORT` = `3306`
   - `DB_USER` = `dbu2298835`
   - `DB_PASSWORD` = your IONOS database password
   - `DB_NAME` = `dbs15396335` (or the name from your IONOS panel)

2. In the Deploy Now project, open **Runtime settings** and ensure the template step uses these secrets so the generated `.env` gets the values. The repo includes `.deploy-now/env.template`; if your project uses a different template path (e.g. `.deploy-now/<project-name>/env.template`), copy or adapt it there.

## 3. Deploy

Push to the connected branch. The site will be served at your Deploy Now URL. The game and `/api/*` will use the PHP API and your IONOS database. No `config.js` change needed (API is same-origin).
