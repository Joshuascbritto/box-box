# Deployment — box-box

Targets:
- **Backend** → Fly.io (Laravel + Postgres)
- **Frontend** → Vercel
- **Email** → Resend (free tier)

This guide assumes you have:
- A Fly.io account with `flyctl` installed and authenticated (`fly auth login`)
- A Vercel account with `vercel` installed and authenticated (`vercel login`)
- A Resend account with a verified sender domain
- The repo pushed to GitHub

---

## 1 — Backend on Fly.io

### 1.1 Launch the app

From `backend/`:

```bash
fly launch --no-deploy --copy-config --name box-box-api
```

When prompted:
- Choose a region near you (São Paulo `gru`, US East `iad`, EU West `cdg`, etc.). Update `primary_region` in `fly.toml` to match.
- Skip Postgres / Redis attach (we'll do Postgres in the next step).
- Don't deploy yet.

### 1.2 Provision Postgres

Fly's managed Postgres has been simplified — pick **one** of these:

**Option A — Fly Postgres (Fly's managed Postgres):**

```bash
fly postgres create --name box-box-db --region gru
fly postgres attach --app box-box-api box-box-db
```

`attach` writes `DATABASE_URL` and individual `DB_*` vars to your app secrets.

**Option B — Neon free tier (recommended for tight budgets):**

1. Create a project at [neon.tech](https://neon.tech).
2. Copy the connection details from the Neon dashboard.
3. Set them as Fly secrets:

```bash
fly secrets set \
  DB_HOST=ep-xxx.aws.neon.tech \
  DB_PORT=5432 \
  DB_DATABASE=neondb \
  DB_USERNAME=neondb_owner \
  DB_PASSWORD=YOUR_PASSWORD \
  DB_SSLMODE=require
```

### 1.3 Generate APP_KEY

Locally, generate a fresh key (don't commit):

```bash
php artisan key:generate --show
```

Copy the resulting `base64:...` string and set it as a Fly secret:

```bash
fly secrets set APP_KEY="base64:..."
```

### 1.4 Required runtime secrets

Set everything else:

```bash
fly secrets set \
  APP_URL="https://box-box-api.fly.dev" \
  FRONTEND_URL="https://box-box.vercel.app" \
  SANCTUM_STATEFUL_DOMAINS="box-box.vercel.app" \
  ADMIN_EMAILS="you@example.com" \
  MAIL_MAILER=resend \
  RESEND_KEY="re_xxx_from_resend_dashboard" \
  MAIL_FROM_ADDRESS="no-reply@yourdomain.app" \
  MAIL_FROM_NAME="box-box"
```

> **Note** — the Resend transport requires the `resend/resend-laravel` package.
> Add it before deploying: `composer require resend/resend-laravel`.
> If you're not ready to wire Resend yet, set `MAIL_MAILER=log` for now.

### 1.5 First deploy

```bash
fly deploy
```

The Dockerfile entrypoint runs `php artisan migrate --force` automatically on
every boot, so migrations apply on the first deploy. Watch logs to confirm:

```bash
fly logs
```

### 1.6 Seed initial data

```bash
fly ssh console
php artisan db:seed
exit
```

### 1.7 Smoke test

```bash
curl https://box-box-api.fly.dev/api/health
# → {"status":"ok","time":"..."}
```

---

## 2 — Frontend on Vercel

### 2.1 Import the repo

From the project root:

```bash
cd frontend
vercel
```

Or via the Vercel dashboard: **Add New → Project → Import** the GitHub repo, set
the project root to `frontend/`. Vercel detects Vite automatically (`vercel.json`
already pins this).

### 2.2 Set the API URL

In the Vercel project settings → Environment Variables:

```
VITE_API_URL = https://box-box-api.fly.dev
```

Apply to all environments (Production, Preview, Development).

### 2.3 Trigger a deploy

```bash
vercel --prod
```

Verify the deployed URL serves the app.

---

## 3 — Wire CORS and Sanctum

The backend already accepts `FRONTEND_URL` as an allowed CORS origin
(see `config/cors.php`). After Vercel assigns your production URL:

```bash
fly secrets set FRONTEND_URL="https://box-box.vercel.app"
fly secrets set SANCTUM_STATEFUL_DOMAINS="box-box.vercel.app"
fly deploy   # Restart picks up new secrets
```

If you set up a custom domain (e.g. `box-box.app`), update both secrets and
Resend's verified sender domain.

---

## 4 — Email deliverability (Resend)

1. Sign up at [resend.com](https://resend.com).
2. Add and verify your sending domain (DKIM/SPF records to your DNS).
3. Generate an API key, set as `RESEND_KEY` Fly secret.
4. Set `MAIL_FROM_ADDRESS` to an address on your verified domain
   (e.g. `no-reply@box-box.app`).
5. Test by requesting a magic link from the deployed frontend.

If using a custom domain, also add SPF + DKIM in your DNS:
- SPF: `v=spf1 include:resend.com ~all`
- DKIM: Resend gives you 3 CNAMEs to add — copy them verbatim.

---

## 5 — Common pitfalls

| Pitfall | Symptom | Fix |
|---|---|---|
| `APP_KEY` not set | "No application encryption key has been specified." | `fly secrets set APP_KEY="base64:..."` |
| Migrations not run on first deploy | API returns 500 with "relation X does not exist" | The entrypoint runs migrations automatically — check `fly logs` for migrate errors. Re-run manually via `fly ssh console`. |
| CORS error in browser | Preflight fails, browser console shows blocked origin | Set both `FRONTEND_URL` and add the Vercel URL to CORS allow-list. Re-deploy. |
| Sanctum 419 / CSRF errors | Login or auth requests fail with 419 | Bearer-token flow shouldn't hit CSRF. If using cookies, check `SANCTUM_STATEFUL_DOMAINS` matches the exact frontend host (no scheme, no path). |
| Magic-link emails in spam | Users don't receive emails | Verify domain in Resend, add SPF + DKIM, ensure `MAIL_FROM_ADDRESS` is on the verified domain. |
| Postgres SSL required (Neon) | "FATAL: SSL required" | Set `DB_SSLMODE=require` and add `'sslmode' => env('DB_SSLMODE')` to `config/database.php`'s pgsql connection if needed. |

---

## 6 — Deployment checklist

Before flipping the switch:

- [ ] `fly launch` and `fly postgres create` (or Neon) completed
- [ ] `APP_KEY` secret set on Fly
- [ ] `APP_URL`, `FRONTEND_URL`, `SANCTUM_STATEFUL_DOMAINS`, `ADMIN_EMAILS` set
- [ ] Resend domain verified, `RESEND_KEY` + `MAIL_FROM_ADDRESS` set
- [ ] `composer require resend/resend-laravel` added (if using Resend)
- [ ] `fly deploy` succeeds, `/api/health` returns `200`
- [ ] `php artisan db:seed` run via `fly ssh console`
- [ ] Vercel project deployed, `VITE_API_URL` env var set to Fly URL
- [ ] Frontend smoke test — schedule page loads
- [ ] Magic-link login flow works end-to-end (request → email → verify → /me)
- [ ] Submit a prediction as the admin user
- [ ] As admin, hit `POST /api/admin/races/{id}/result` to record a result
- [ ] Leaderboard reflects scored prediction
- [ ] Both deployed URLs documented in the project README
