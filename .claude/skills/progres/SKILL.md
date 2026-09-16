---
name: progres
description: Generate WhatsApp e-Monitoring progress report for BWS Sulawesi IV. Use this whenever the user wants to create/send a progress report, check current budget absorption and physical realization metrics, generate Ditjen SDA deviasi, or compile ranking of satker/PPK performance. Triggered by /progres command.
compatibility: Requires browser automation (Chrome/Edge), MySQL access, network connectivity to Laravel app
---

# Generate e-Monitoring Progress Report

Generate a formatted WhatsApp progress report for e-Monitoring that includes:
- Ditjen SDA target progress (Keuangan % & Fisik %)
- BWS aggregate performance (from synced API packets)
- Deviasi calculation (BWS vs Ditjen SDA)
- Satker ranking (top 3 with 🥇🥈🥉)
- PPK ranking (all 17 with top 3 emoji)
- Bottom performer (if exists below Ditjen target)

## Workflow

1. **Collect credentials** — prompt user for login email & password
2. **Login to dashboard** — open browser, navigate `/login`, authenticate
3. **Get Ditjen SDA targets** — prompt user for Keuangan % and Fisik % targets
4. **Sync API data** — click "Sinkron Data API" button on dashboard, wait for completion
5. **Aggregate from database** — query `packets` table locally, calculate progress per satker & PPK
6. **Format message** — generate WhatsApp-ready text message with rankings
7. **Display & download** — show message (ready to copy), navigate dashboard, point user to "Unduh Gambar" button for screenshot

## Key Formulas (must match dashboard exactly)

- **Progres Keuangan (%)** = (Σ realisasi / Σ pagu) × 100
- **Realisasi Fisik (%)** = Σ(real_fisik × pagu) / Σ pagu — **pagu-weighted average**; do NOT multiply by 100 again
- **Ranking** = by Progres Keuangan descending (satkers among 5, PPKs among all 17)
- **Deviasi Keuangan** = BWS Keu % − Ditjen SDA Keu %
- **Deviasi Fisik** = BWS Fis % − Ditjen SDA Fis %

## Message Format

Structure exactly as shown (this is the official e-Monitoring template):

```
Selamat [Greeting] Bpk/Ibu

Mohon izin menyampaikan progres e-Monitoring status :  [DD Mon YYYY] ; [HH:mm] WITA

🔘Progres K/F Ditjen SDA :
Keuangan  : [X.XX] %
Fisik  : [X.XX] %

🔘Progres K/F BWS Sul IV KDI :
Keuangan  : [X.XX] %
Fisik  : [X.XX] %

➡ Deviasi Progres K/F BWS Sul IV KDI thdp Ditjen SDA :
Keuangan  : [+/−X.XX] %
Fisik  : [+/−X.XX] %

Rincian Progres K/F Masing2 Satker di lingkungan BWS Sul IV KDI :
[For each satker in order of Keu % descending, with emoji: 🥇 for 1st, 🥈 for 2nd, 🥉 for 3rd]
🔹[Satker Name] : [Keu]% / [Fis]%[EMOJI]

🔘 Progres K/F masing2 PPK di lingkungan BWS SUL IV KDI:
[For each PPK in order of Keu % descending, numbered 1-17, with emoji for top 3]
[N]. [PPK Short Name] : [Keu]% / [Fis]%[EMOJI]

[IF exists PPK below Ditjen SDA Keu target]
🔘  Progres Keu di bawah Ditjen SDA *berdasarkan iemon* :
[Lowest Keu % PPK name and value]

Demikian disampaikan, terima kasih..
Salam Damai Indonesia, Bahagia untuk Semua 💪💪💪
Cc. Bapak Kabalai
```

## Configuration

- **App URL**: http://127.0.0.1:8000
- **Timezone**: Asia/Makassar (WITA)
- **Greeting logic**: 0-10h→Pagi, 11-14h→Siang, 15-17h→Sore, 18-23h→Malam
- **Satker list** (5 total, ordered): Balai, PJPA, PJSA, SNVT Bendungan, OP PSDA
- **PPK mapping**: See references/config.md for short name conversions (e.g., "PPK Air Tanah dan Air Baku 1" → "PPK Atab I")
- **CC name**: Bapak Kabalai (customizable via config)

## Database Access

Script assumes local MySQL access to `db_anggaran` database:
- **Table**: `packets` (kdsatker, ppk_id, pagu, realisasi, real_fisik, updated_at)
- **Table**: `ppks` (id, name, satker_group)
- Connection via `.env` credentials (DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE)

## Browser Navigation

After message generation, open dashboard to help user capture screenshot:
1. Navigate to http://127.0.0.1:8000/dashboard
2. Wait for page load
3. Highlight/point to "Unduh Gambar" button (top-right area of dashboard)
4. Instruct user: "Click 'Unduh Gambar' to automatically download the screenshot"

## Implementation Steps

Use the bundled scripts in order:

1. **login.py** — Interactive login with email/password, returns auth session cookie
2. **sync_and_query.py** — Click sync button, wait for completion, query database, return aggregated JSON
3. **format_message.py** — Take aggregated JSON + Ditjen targets, format WhatsApp message, return text

See references/ for satker/PPK mappings and database schema.

## Error Handling

- **Login fails**: Retry with fresh credentials, check if user account exists
- **Sync timeout**: Wait up to 60s for API response, warn user if slow
- **Database query fails**: Fall back to manual entry (prompt user for totals) or re-sync
- **Browser navigation fails**: Provide manual instructions to user for clicking buttons

## Output

Return to user:
1. **Formatted message** (text, ready to copy-paste to WhatsApp)
2. **Open dashboard** with instruction to screenshot
3. **Confirmation**: "Message is ready. Open the dashboard, click 'Unduh Gambar' when ready, paste message to WhatsApp."
