# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

SI-Anggaran — a budget monitoring dashboard for **BWS Sulawesi IV** (Indonesian water-resources agency). It pulls activity-package ("paket") budget/realization data from an external API, groups it into 5 satker (work units), and reports progress per satker and per PPK (commitment officer), with ranking. UI language is Indonesian; AdminLTE 4 template (Bootstrap 5) served from `public/template/`.

Laravel 11, PHP 8.2, MySQL. Blade views (no SPA). Vite builds `resources/` assets but the app currently loads AdminLTE/Bootstrap/ApexCharts from CDN in `resources/views/layout/v_layout.blade.php`.

## Commands

```bash
php artisan serve                      # run dev server (http://127.0.0.1:8000)
php artisan migrate                    # apply migrations
php artisan migrate:fresh --seed       # rebuild schema; runs DatabaseSeeder (Pagu revisions only)
php artisan db:seed --class=PpkSeeder  # seed the 17 reference PPKs (NOT in DatabaseSeeder)
php artisan packets:sync               # fetch packets from the SIHKA API into DB (see below)
php artisan test                       # run PHPUnit (or: ./vendor/bin/phpunit)
php artisan test --filter=SomeTest     # run a single test
npm run dev                            # Vite dev (only if editing resources/ assets)
npm run build                          # Vite production build
```

Database is **MySQL `db_anggaran`** (Laragon). Laragon ships MariaDB, which rejects Laravel 11's default `utf8mb4_0900_ai_ci` — `.env` therefore sets `DB_COLLATION=utf8mb4_unicode_ci` (keep it).

## Data flow (the core to understand)

External API (`config/services.php` → `sihka`, credentials in `.env`: `SIHKA_URL/ENDPOINT/KEY`, sent as `x-key` header) → `App\Services\PacketSyncService::sync()` → `packets` table → `DashboardController` aggregates → Blade.

`packets:sync` and the dashboard "Sinkron Data API" button both call `PacketSyncService`. It is a **full snapshot**: delete all rows, re-insert. Critical details:

- Uses `Packet::query()->delete()` **not `truncate()`** — TRUNCATE causes an implicit commit in MySQL that breaks the surrounding transaction.
- kdsatker → satker slug via `App\Support\Satker` / `config/satker.php`. Rows whose kdsatker maps to `'lainnya'` (not one of the 5 satker) are **skipped entirely** (e.g. `694101`).
- `ppk_id` assignments are **preserved across the wipe by `kdpaket`** (snapshot of existing non-null ppk_id before delete, re-applied on insert). New packets from the API arrive with `ppk_id = null`.
- The full raw API object is stored in `packets.raw` (JSON) so no field is lost.

## Satker & PPK model

- **5 satker** defined in `config/satker.php` (`groups`, keyed by slug: `balai, op, pjpa, pjsa, bendungan`). `op` maps kdsatker `694136` only. `App\Support\Satker` resolves slug↔name↔kdsatker.
- **PPKs** live in the `ppks` table (managed via the "Kelola PPK" page, `PpkController`). `ppks.satker` is one of 5 fixed display strings (`Ppk::SATKER_OPTIONS`); `ppks.satker_group` (slug) is auto-derived on save via `Ppk::SATKER_SLUG` in the model's `booted()` hook — used to filter PPK choices per satker.
- `packets.ppk_id` → `ppks.id` is the single source of truth linking packets to PPKs. Assigned per-packet on the satker detail page (`/satker/{slug}`) via a modal; options are restricted to that satker's PPKs (server-side validated in `DashboardController::assignPpk`).
- Initial `ppk_id` backfill came from an external Excel file, parsed to `database/data/paket_ppk_map.json`, reconciled to `ppks` rows via an alias map inside migration `..._link_packets_to_ppks` (Excel used Roman numerals / abbreviations like "PPK Atab 1"; `ppks` uses "PPK Air Tanah dan Air Baku 1"). See the memory note `ppk-mapping-source.md` for how to regenerate if the Excel changes. Routine syncs do NOT re-read the Excel.

## Progress metric conventions (must match across the app)

- **Progres Keuangan (Keu %)** = ΣrealisasI / Σpagu × 100 (financial absorption).
- **Realisasi Fisik (Fis %)** = Σ(`real_fisik` × pagu) / Σpagu — **pagu-weighted average**. `real_fisik` is already a percentage (e.g. `87.91`), so **do not multiply by 100**.
- **Ranking** (dashboard "Progres per PPK & Peringkat") is by Keu % descending — satkers ranked among 5, PPKs ranked among all PPKs.

`DashboardController::buildPpkProgres()` computes the hierarchical satker→PPK table; `buildUnmappedPpk()` detects packets with `ppk_id = null` grouped per satker (shown at the bottom of the dashboard). Monetary values in that table are displayed in thousands ("Rp.000").

## Conventions & gotchas

- Views are named `v_*.blade.php`; layout partials in `resources/views/layout/`. The sidebar (`layout.v_sidebar`) gets its data from a View Composer in `AppServiceProvider::boot()`.
- **Do not auto-format `.blade.php` files.** A formatter once mangled `@if`/`@else` directives (URL-encoded them) and broke the dashboard. `.vscode/settings.json` disables format-on-save for Blade — keep it.
- App runs in UTC (`APP_TIMEZONE=UTC`); timestamps are converted to Asia/Makassar (WITA) only at display time in the views.
- The "manage" pages (Pagu revisions, PPK) follow one of two patterns: `PaguController` uses bulk delete-and-recreate on save; `PpkController` uses per-row modals (Tambah/Perbarui/Hapus). Match the surrounding page's pattern when extending.
- No test suite beyond Laravel's example tests.
