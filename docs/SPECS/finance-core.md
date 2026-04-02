# Core Pencatatan Keuangan (Technical Spec)

## Tujuan

- Menyimpan transaksi pemasukan/pengeluaran dengan timestamp (WIB).
- Menyediakan rekap bulanan + dashboard ringkas.
- Menjadi sumber data utama untuk export Excel dan laporan.

## Model Data (MySQL)

### `categories`

- `id`
- `user_id`
- `type` (enum: `income`, `expense`)
- `name` (string)
- `icon` (string, optional)
- `color` (string, optional)
- `active` (bool)
- `created_at`, `updated_at`

Rules:
- Semua kategori wajib punya `type`.
- Index: `(user_id, type, active)`.

### `transactions`

- `id`
- `user_id`
- `type` (enum: `income`, `expense`)
- `amount` (decimal 15,2)
- `category_id` (nullable)
- `description` (text, **wajib non-kosong** secara bisnis)
- `transaction_at` (datetime, WIB)
- `created_at`, `updated_at`

Index:
- `(user_id, transaction_at)`
- `(user_id, type, transaction_at)`
- `(user_id, category_id, transaction_at)`

## Rule Bisnis

- `amount > 0`.
- `description` tidak boleh kosong:
  - Jika hasil parser kosong, fallback ke nama kategori atau "Transaksi".
- Konfirmasi OK/batal sebelum insert (lihat spec chat).

## Query Agregasi (Dashboard/Reports)

### Ringkasan Bulan Aktif

- `income_sum` = SUM(amount) where type=income and month(transaction_at)=bulan aktif
- `expense_sum` = SUM(amount) where type=expense and month(transaction_at)=bulan aktif
- `balance` = income_sum - expense_sum

### Grafik Bulanan (N bulan)

- group by `YYYY-MM`:
  - SUM income
  - SUM expense

### Rekap Bulanan

- list bulan desc:
  - `month_key`, `income`, `expense`, `balance`

### Detail Bulan (Laporan)

- filter month
- order by `transaction_at asc`
- output debit/kredit per baris

## API (JSON)

### Create transaction (langsung, tanpa chat)

`POST /api/transactions`

Request:
```json
{
  "type": "expense",
  "amount": 50000,
  "category_id": 7,
  "description": "Baju",
  "transaction_at": "2026-04-01T05:06:00+07:00"
}
```

Response:
```json
{
  "ok": true,
  "data": {
    "transaction": {
      "id": 123,
      "type": "expense",
      "amount": 50000,
      "category": {"id": 7, "name": "Belanja"},
      "description": "Baju",
      "transaction_at": "2026-04-01T05:06:00+07:00"
    }
  },
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

### Dashboard summary

`GET /api/dashboard?month=2026-04`

Response:
```json
{
  "ok": true,
  "data": {
    "month": "2026-04",
    "summary": {
      "income": 100000000,
      "expense": 50000,
      "balance": 99950000
    },
    "chart": [
      {"month": "2025-11", "income": 5000000, "expense": 1200000},
      {"month": "2025-12", "income": 6000000, "expense": 2000000}
    ],
    "top_expense_categories": [
      {"category": "Belanja", "total": 50000}
    ],
    "recent_transactions": [
      {
        "id": 123,
        "type": "expense",
        "amount": 50000,
        "category": "Belanja",
        "description": "Baju",
        "transaction_at": "2026-04-01T05:06:00+07:00"
      }
    ]
  },
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

### Monthly recap list

`GET /api/recap/months?limit=24`

Response:
```json
{
  "ok": true,
  "data": {
    "months": [
      {"month": "2026-04", "income": 100000000, "expense": 50000, "balance": 99950000},
      {"month": "2026-03", "income": 0, "expense": 0, "balance": 0}
    ]
  },
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

### Month detail (debit/kredit)

`GET /api/recap/months/2026-04/detail`

Response:
```json
{
  "ok": true,
  "data": {
    "month": "2026-04",
    "rows": [
      {
        "transaction_at": "2026-04-01T05:06:00+07:00",
        "description": "Gaji",
        "category": "Gaji",
        "debit": 0,
        "credit": 20000,
        "running_balance": 20000
      },
      {
        "transaction_at": "2026-04-01T05:07:00+07:00",
        "description": "Baju",
        "category": "Belanja",
        "debit": 50000,
        "credit": 0,
        "running_balance": -30000
      }
    ],
    "totals": {"debit": 50000, "credit": 20000, "balance": -30000}
  },
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

