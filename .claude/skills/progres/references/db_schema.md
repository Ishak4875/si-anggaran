# Database Schema

## packets table

Core table synced from SIHKA API. Used for all progress calculations.

```sql
CREATE TABLE packets (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  kdpaket VARCHAR(255) UNIQUE NOT NULL,           -- API packet code (e.g., "101.010.001.001")
  nama_paket VARCHAR(255),                        -- Package name
  kdsatker VARCHAR(10),                           -- Satker code (maps to satker slug via config/satker.php)
  pagu BIGINT,                                    -- Budget allocation (rupiah)
  realisasi BIGINT DEFAULT 0,                     -- Financial realization (rupiah)
  real_fisik DECIMAL(5,2) DEFAULT 0,              -- Physical realization (%, already 0-100, e.g., 87.91)
  ppk_id BIGINT UNSIGNED NULL,                    -- FK to ppks.id (null if unassigned)
  raw JSON,                                       -- Full API response object
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (ppk_id) REFERENCES ppks(id) ON DELETE SET NULL,
  INDEX (kdsatker),
  INDEX (ppk_id),
  INDEX (updated_at)
);
```

**Key fields for progress calculation:**
- `pagu`: sum per satker/PPK for budget total
- `realisasi`: sum per satker/PPK for financial absorption
- `real_fisik`: average (pagu-weighted) per satker/PPK for physical realization
- `kdsatker`: group by to filter satkers
- `ppk_id`: group by to filter PPKs
- `updated_at`: latest timestamp (for status message)

## ppks table

Reference list of 17 PPKs. Linked to packets via ppk_id.

```sql
CREATE TABLE ppks (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,                     -- Full name (e.g., "PPK Air Tanah dan Air Baku 1")
  satker_group VARCHAR(50),                       -- Satker slug (e.g., "balai", "pjpa") — auto-derived
  satker VARCHAR(100),                            -- Display string (e.g., "Balai") — auto-derived
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX (satker_group)
);
```

**Usage**: Query for ranking, lookup name for short-name mapping.

## Query Examples

### Calculate Keuangan % (Financial Absorption) per Satker

```sql
SELECT 
  p.kdsatker,
  ROUND((SUM(p.realisasi) / SUM(p.pagu) * 100), 2) AS progres_keuangan
FROM packets p
WHERE p.kdsatker IN ('694133', '694134', '694135', '694137', '694136')
GROUP BY p.kdsatker;
```

### Calculate Fisik % (Physical Realization, pagu-weighted) per Satker

```sql
SELECT 
  p.kdsatker,
  ROUND(SUM(p.real_fisik * p.pagu) / SUM(p.pagu), 2) AS progres_fisik
FROM packets p
WHERE p.kdsatker IN ('694133', '694134', '694135', '694137', '694136')
GROUP BY p.kdsatker;
```

### Aggregate for PPK Ranking

```sql
SELECT 
  ppk.id,
  ppk.name,
  COUNT(p.id) AS packet_count,
  ROUND((SUM(p.realisasi) / SUM(p.pagu) * 100), 2) AS progres_keuangan,
  ROUND(SUM(p.real_fisik * p.pagu) / SUM(p.pagu), 2) AS progres_fisik
FROM ppks ppk
LEFT JOIN packets p ON p.ppk_id = ppk.id
GROUP BY ppk.id, ppk.name
ORDER BY progres_keuangan DESC;
```

### Get Latest Sync Timestamp

```sql
SELECT updated_at FROM packets ORDER BY updated_at DESC LIMIT 1;
```

## Data Flow

1. **Sync** (via "Sinkron Data API" button):
   - API call to SIHKA endpoint
   - Laravel `PacketSyncService::sync()` wipes packets table (DELETE, not TRUNCATE)
   - Re-inserts all API rows
   - Preserves ppk_id assignments via kdpaket snapshot

2. **Aggregate** (script queries after sync):
   - Group packets by kdsatker → satker progress
   - Group packets by ppk_id → PPK progress
   - Calculate Keuangan % and Fisik % per formula
   - Rank descending by Keuangan %

3. **Format** (script formats to WhatsApp message):
   - Build message from aggregated data
   - Insert Ditjen SDA targets
   - Calculate deviasi
   - Apply emoji for rankings
   - Return formatted text
