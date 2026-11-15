# AP3 — Sprout Scout
An Agriculture Information System.

A web-based Agriculture Information System demonstrating database normalization,
relational design, secure data management, CRUD operations, reporting, and
dashboard visualization.

## Objectives

- Manage farmer, farm, crop, planting, and harvest records.
- Provide dashboard summaries and reports for agricultural operations.
- Enforce authentication and role-based access (administrator/staff).
- Demonstrate normalized database design with lookup tables and audit fields.

## Scope

**In scope:** management of farmers, farms, crops, planting records, and harvest records; dashboard summaries; reports; authentication (administrator/staff); and lookup tables for soil type, irrigation type, crop category, season, planting status, and quality grade.

**Out of scope:** financial accounting, input inventory, weather integration, and native mobile applications.

## Modules

- Farmers
- Farms
- Crops
- Planting Records
- Harvest Records
- Dashboard (summary cards + charts)
- Reports (farmers & farms, crops per farm, plantings by status, harvests, harvested quantity, estimated value)
- Authentication (Administrator / Staff roles, with a Create Account page)

## User Guide

1. Open the app in your browser and sign in with your account.
2. Use the navigation bar (or the mobile menu on phones) to open Farmers, Farms, Crops, Planting, Harvest, or Reports.
3. Use **Add** to create records; **Edit** and **Delete** are available per row.
4. Dashboard shows totals and recent records; Reports provides filtered lists and summaries.
5. Administrators can access the Reports module.

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
