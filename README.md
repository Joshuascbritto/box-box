# box-box

A Formula 1 podium prediction app.

In F1, *"box, box"* is the team radio call instructing a driver to pit *now* —
the moment of decision, the call committed. The app's mechanic mirrors this:
once you submit your podium prediction, the call is in.

**Tagline:** Make the call.

## What it does

Users predict the full podium (P1, P2, P3) and the number of DNFs for upcoming
F1 races. A leaderboard ranks predictors across the season by accuracy.

## Stack

- **Backend** — Laravel 11 + PostgreSQL, Sanctum bearer-token auth
- **Frontend** — React + Vite + TypeScript (added in Stage 4)
- **Auth** — Passwordless magic links via email
- **Architecture** — Decoupled JSON API + separate SPA

## Project layout

```
box-box/
├── backend/             Laravel 11 API
├── frontend/            React + Vite SPA  (Stage 4)
├── docker-compose.yml   Local Postgres 16
└── README.md
```

## Local development

### 1. Start Postgres

```bash
docker compose up -d
```

This starts Postgres 16 on `localhost:5432` with database/user/password all set
to `boxbox` (matching `backend/.env`).

### 2. Backend

```bash
cd backend
composer install         # only needed once
cp .env.example .env     # if .env doesn't already exist
php artisan key:generate # only needed if APP_KEY is empty
php artisan migrate      # creates tables
php artisan db:seed      # seeds 2025 grid + placeholder races
php artisan serve        # http://localhost:8000
```

Smoke test:

```bash
curl http://localhost:8000/api/health
# → {"status":"ok","time":"..."}
```

### 3. Frontend

Built in Stage 4. Will live in `frontend/` and run on `http://localhost:5173`.

## Deployment

See [DEPLOY.md](DEPLOY.md) for the Fly.io + Vercel + Resend walkthrough.

## Stages

- [x] **Stage 1** — Laravel skeleton, models, migrations, seeders
- [x] **Stage 2** — Magic-link auth
- [x] **Stage 3** — Predictions API + scoring service + tests
- [x] **Stage 4** — React frontend with Blueprint design system
- [x] **Stage 5** — Deployment configs (Dockerfile, fly.toml, Vercel)

## License

Private project.
