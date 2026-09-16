# Configuration & Mappings

## Satker List (5 total)

Ordered by display name. Match kdsatker from packets table:

| Slug | Display Name | kdsatker codes | Sequence |
|------|--------------|----------------|----------|
| balai | Balai | 694133 | 1 |
| pjpa | PJPA | 694134 | 2 |
| pjsa | PJSA | 694135 | 3 |
| bendungan | SNVT Bendungan | 694137 | 4 |
| op | OP PSDA | 694136 | 5 |

**Mapping source**: `config/satker.php` in Laravel app

## PPK Mapping (17 total)

PPK names in database → Short names for WhatsApp message:

| Full Name (in ppks table) | Short Name (for message) |
|---------------------------|--------------------------|
| PPK Air Tanah dan Air Baku 1 | PPK Atab I |
| PPK Air Tanah dan Air Baku 2 | PPK Atab II |
| PPK Daya Dukung Lingkungan 1 | PPK DDL I |
| PPK Daya Dukung Lingkungan 2 | PPK DDL II |
| PPK Pengembangan Kawasan 1 | PPK PK I |
| PPK Pengembangan Kawasan 2 | PPK PK II |
| PPK Pengembangan Kawasan 3 | PPK PK III |
| PPK Pengembangan Kawasan 4 | PPK PK IV |
| PPK Operasional 1 | PPK Op I |
| PPK Operasional 2 | PPK Op II |
| PPK Pelayanan Sumber Daya Air 1 | PPK PSDA I |
| PPK Pelayanan Sumber Daya Air 2 | PPK PSDA II |
| PPK Perencanaan 1 | PPK Peren I |
| PPK Perencanaan 2 | PPK Peren II |
| PPK Bendungan 1 | PPK Bend I |
| PPK Bendungan 2 | PPK Bend II |
| PPK Bendungan 3 | PPK Bend III |

**Mapping source**: `WhatsAppMessageFormatter::shortPpkName()` in app services

## Message Configuration

```
Greeting timezone: Asia/Makassar (WITA)
CC Name: Bapak Kabalai
BWS Name: BWS Sul IV KDI
App Name: e-Monitoring
Status update source: packets.updated_at (latest)
Date format: DD Mon YYYY (e.g., "20 Aug 2026")
Time format: HH:mm WITA (e.g., "14:30 WITA")
```

## Database Configuration

**.env variables** (from Laravel app):
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_anggaran
DB_USERNAME=root
DB_PASSWORD=(check Laragon)
```

Default Laragon MariaDB:
- Host: 127.0.0.1
- Port: 3306
- User: root
- Password: (empty or check settings)
- Database: db_anggaran

## API Configuration

**SIHKA API endpoint** (from `config/services.php`):
```
Base URL: from .env SIHKA_URL
Endpoint: from .env SIHKA_ENDPOINT
API Key: from .env SIHKA_KEY (sent as x-key header)
```

These are used only by the sync button click; script does not call API directly.

## Emoji & Symbols

- 🔘 = bullet point for sections
- 🔹 = sub-bullet for satker list
- 🥇 = 1st place (Keu % top satker)
- 🥈 = 2nd place
- 🥉 = 3rd place
- ➡ = arrow for deviasi section
- 💪 = closing motivation (3 times)

## Validation Rules

- Ditjen SDA Keu %: 0-100, decimal to 2 places
- Ditjen SDA Fis %: 0-100, decimal to 2 places
- Calculated values: always 2 decimal places
- Deviasi: can be positive or negative, show +/− prefix
