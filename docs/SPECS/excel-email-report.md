# Export Excel & Email Report Bulanan

## Tujuan

- User bisa download laporan bulanan dalam format Excel.
- User bisa kirim laporan bulanan ke email (manual untuk MVP).
- Format rapi debit/kredit per baris.

## Format Excel (1 file per bulan)

Sheet: `Laporan YYYY-MM`

### Header

- Nama user
- Periode bulan
- Tanggal dibuat

### Tabel

| Tanggal | Jam | Keterangan | Kategori | Debit (Keluar) | Kredit (Masuk) | Saldo Berjalan |
|---|---|---|---|---:|---:|---:|

Rules:
- `expense` -> Debit terisi, Kredit 0.
- `income` -> Kredit terisi, Debit 0.
- Saldo berjalan dihitung urut `transaction_at ASC`.

### Footer

- Total debit
- Total kredit
- Balance bulan

## Model Data (MySQL)

### `report_exports`

- `id`
- `user_id`
- `period` (string `YYYY-MM`)
- `type` (enum: `download`, `email`)
- `status` (enum: `queued`, `success`, `failed`)
- `file_path` (string, nullable)
- `error_message` (text, nullable)
- `created_at`

## API (JSON)

### Download report

`GET /api/reports/monthly/2026-04.xlsx`

Response:
- file stream (binary)

### Generate report (optional)

`POST /api/reports/monthly/generate`

Request:
```json
{ "period": "2026-04" }
```

Response:
```json
{
  "ok": true,
  "data": {
    "period": "2026-04",
    "download_url": "/api/reports/monthly/2026-04.xlsx"
  },
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

### Email report

`POST /api/reports/monthly/email`

Request:
```json
{ "period": "2026-04" }
```

Response:
```json
{
  "ok": true,
  "data": {"queued": true},
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

## Email Templates (MVP)

### Monthly Report Email

- Subject: `Laporan Keuangan April 2026`
- Body ringkas:
  - total pemasukan
  - total pengeluaran
  - sisa
- Attachment: `laporan-2026-04.xlsx`

## UI (React)

- Di halaman Rekap:
  - pilih bulan
  - tombol Download
  - tombol Kirim Email

