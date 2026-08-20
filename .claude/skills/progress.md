---
name: progress
description: Generate dan kirim laporan progress e-Monitoring dengan screenshot ke WA
trigger: /progress
---

# Skill: Progress e-Monitoring Report

Skill ini mengotomatisasi pembuatan laporan progress e-Monitoring untuk BWS Sulawesi IV dengan langkah-langkah:

1. **Input Ditjen SDA Progress** — Tanya user berapa nilai Progres Keuangan & Fisik Ditjen SDA terkini
2. **Sinkron API** — Jalankan `php artisan packets:sync` untuk update data paket dari SIHKA API
3. **Update Dashboard** — Input nilai Ditjen SDA ke form "Ubah Progres Ditjen SDA"
4. **Capture Screenshot** — Ambil gambar dari fitur "Progres per PPK & Peringkat"
5. **Generate Laporan WA** — Buat pesan WhatsApp format lengkap dengan:
   - Greeting dinamis (Selamat Pagi/Siang/Sore/Malam) sesuai waktu saat ini
   - Status timestamp dari website
   - Progres Ditjen SDA, BWS, dan Deviasi
   - Ranking Satker (top 3 with 🥇🥈🥉)
   - Ranking PPK (top 3 best + bottom 1)
6. **Deliver** — Output pesan text (siap copy-paste) + gambar screenshot

## Workflow

### Step 1: Input Ditjen SDA Values
```
Pertanyaan ke user:
"Berapa nilai Progres Keuangan & Fisik Ditjen SDA saat ini?
- Progres Keuangan (%): [input]
- Progres Fisik (%): [input]"
```

### Step 2: API Sync
```bash
php artisan packets:sync
```
Wait untuk sync selesai dan tampilkan status.

### Step 3: Update Dashboard Values
- Navigate ke dashboard `/`
- Buka modal "Ubah Progres Ditjen SDA" (klik button di dashboard)
- Input nilai dari user ke form:
  - Progres Keuangan (Keu %)
  - Realisasi Fisik (Fis %)
- Click "Simpan"

### Step 4: Refresh & Capture
- Reload dashboard page
- Screenshot fitur "Progres per PPK & Peringkat" section (tabel dengan 17 PPK)

### Step 5: Query Data & Generate Message
```php
// Get Ditjen SDA progress (dari form input)
$ditjen_keu = [user input];
$ditjen_fis = [user input];

// Get BWS aggregates
$bws_keu = DashboardController::buildPpkProgres() average Keu %;
$bws_fis = DashboardController::buildPpkProgres() average Fis %;

// Get status timestamp dari dashboard (dari page)
$status_datetime = "20 Agustus 2026 ; 12:00 WIB"; // dari Status di page

// Get Satker rankings (5 satker by Progres Keuangan desc)
// Get PPK rankings (17 PPK by Progres Keuangan, sort top 3 best + bottom 1)

// Determine greeting based on current time
$hour = now()->setTimezone('Asia/Makassar')->hour;
$greeting = ($hour < 11) ? 'Selamat Pagi' : 
            (($hour < 15) ? 'Selamat Siang' : 
            (($hour < 18) ? 'Selamat Sore' : 'Selamat Malam'));
```

### Step 6: Format Message
```
[greeting] Bpk/Ibu

Mohon izin menyampaikan progres e-Monitoring status : [status_datetime]

🔘Progres K/F Ditjen SDA :
Keuangan  : [ditjen_keu] %
Fisik  : [ditjen_fis] %

🔘Progres K/F BWS Sul IV KDI :
Keuangan  : [bws_keu] %
Fisik  : [bws_fis] %

➡ Deviasi Progres K/F BWS Sul IV KDI thdp Ditjen SDA :
Keuangan  : [deviasi_keu] %
Fisik  : [deviasi_fis] %

Rincian Progres K/F Masing2 Satker di lingkungan BWS Sul IV KDI :
[5 satker dengan ranking, top 3 dengan emoji 🥇🥈🥉]

🔘 Progres K/F masing2 PPK di lingkungan BWS SUL IV KDI:
[17 PPK dengan ranking, top 3 best + bottom 1]

🔘 Progres Keu di bawah Ditjen SDA *berdasarkan iemon* :
[PPK/Paket dengan Progres Keu < Ditjen SDA Keu]

Demikian disampaikan, terima kasih..
Salam Damai Indonesia, Bahagia untuk Semua 💪💪💪
Cc. [contact dari system setting]
```

### Step 7: Output
- **Pesan WA text** — Display dalam code block (ready to copy-paste)
- **Screenshot** — Tampilkan gambar dari Step 4
- **Ready to send** — User bisa langsung paste ke WA dan attach gambar

## Data Sources

### Ditjen SDA Progress
- **Source**: User input
- **Field**: `progres_keuangan`, `real_fisik` di dashboard modal

### BWS Progress
- **Source**: DashboardController::buildPpkProgres()
- **Calculation**: Average of all PPK progress values

### Satker Rankings
- **Source**: `packets` table grouped by `satker_group`
- **Metric**: Progres Keuangan (realisasi / pagu × 100)
- **Order**: DESC (highest first)
- **Count**: 5 satker (balai, op, pjpa, pjsa, bendungan)

### PPK Rankings
- **Source**: `ppks` table joined with `packets`
- **Metric**: Progres Keuangan per PPK
- **Order**: DESC (highest to lowest)
- **Count**: 17 PPK total

### Status Timestamp
- **Source**: Dashboard page text "Status : [date] ; [time] WIB"
- **Extract**: Via JavaScript atau scrape from page

## Configuration

**System settings yang bisa dikonfigurasi:**
```php
// config/progress.php or .env
PROGRESS_CC_NAME=Bapak Kabalai  // Footer Cc name
PROGRESS_BWS_SHORTNAME=BWS Sul IV KDI  // Nama BWS di laporan
PROGRESS_TIMEZONE=Asia/Makassar  // Timezone untuk greeting
```

## Notes

- Greeting berubah otomatis sesuai waktu lokal (Asia/Makassar)
- Status timestamp ambil dari website (bukan waktu generate), untuk reflect kapan data terakhir sync
- Deviasi = BWS Keu % - Ditjen Keu % (positif = lebih baik dari target)
- Ranking menggunakan emoji: 🥇 (rank 1), 🥈 (rank 2), 🥉 (rank 3)
- Bottom performer (PPK dengan Keu terendah) ditampilkan terpisah di bawah

## Integration Points

1. **Dashboard API** — GET `/` untuk extract status timestamp
2. **Database Query** — Query PPK & Satker progress dari `packets`, `ppks`
3. **Browser Automation** — Screenshot halaman dashboard (Progres per PPK table)
4. **CLI Command** — Run `php artisan packets:sync`
5. **Form Input** — Interact dengan modal "Ubah Progres Ditjen SDA"

