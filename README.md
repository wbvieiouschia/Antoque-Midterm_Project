# AP3 — Sprout Scout

*Watch your fields grow.* An Agriculture Information System.

**Database:** Supabase PostgreSQL  |  **Application:** PHP + Tailwind CSS

A web-based Agriculture Information System demonstrating database normalization,
relational design, secure data management, CRUD operations, reporting, and
dashboard visualization.

## Modules

- Farmers
- Farms
- Crops
- Planting Records
- Harvest Records
- Dashboard (summary cards + charts)
- Reports (farmers & farms, crops per farm, plantings by status, harvests, harvested quantity, estimated value)
- Authentication (Administrator / Staff roles, with a Create Account page)

## Setup

The app supports **two database backends** and auto-detects which to use:

### Option A: SQLite (no setup required — default)

A pre-built SQLite database is included at `database/agri.db`. Just run:

```bash
php -S localhost:8000
```

Then open http://localhost:8000 in your browser. Login with `admin@example.com` / `admin123`.

To rebuild the SQLite database from scratch:

```bash
php database/build_sqlite.php
```

### Option B: Supabase PostgreSQL

Follow the detailed instructions in `~/Documents/supabase-setup-ap3.md` to:
- Create a Supabase project
- Run `database/schema.sql` in the Supabase SQL Editor
- Get your Project URL and anon key

If you want accounts created from the Create Account page, also run `database_app_user.sql` in the SQL Editor. (SQLite creates this table automatically.)

Then create your `.env` file:

```bash
cp env.example .env
# Edit .env and paste your Supabase URL and anon key
```

The app will automatically use Supabase when `.env` is configured. Otherwise it falls back to SQLite.

## Project structure

```
index.php            Dashboard
login.php            Sign in
register.php         Create account (new accounts are Staff)
pages/               farmers, farms, crops, planting, harvest, reports
includes/            config.php (system name, planting statuses), auth, database, header, footer, models
api/search.php       Live search endpoint
js/live_search.js    Live search script
models/              Entity models
database/            SQLite build script, schema.sql
docs/                ERD and normalization
```

To rename the system, edit `APP_NAME` in `includes/config.php`.

## Credentials (demo)

| Role | Email | Password |
|------|-------|----------|
| Administrator | admin@example.com | admin123 |
| Staff | staff@example.com | staff123 |

New accounts created from `register.php` are always **Staff**. To make someone an
Administrator, change their `role` to `administrator` in the `app_user` table, then
have them sign in again.


## Deliverables

- Working source code (this repo)
- Supabase PostgreSQL database with tables, constraints, relationships, lookup data
- ERD and normalized design (UNF → 1NF → 2NF → 3NF) — see `docs/`
- Project documentation — see `docs/`
- Screenshots of working modules
- Test results