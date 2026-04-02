# Account & Subscription (Technical Spec)

## Tujuan

- Setiap data keuangan terikat ke **user account**.
- Akses aplikasi dibatasi oleh **subscription/lisensi**.
- Mendukung monetisasi (Midtrans + voucher) dan admin monitoring.

## Scope MVP

- Register, login, logout, reset password.
- Role: `user`, `admin`.
- Subscription status: `active`, `expired`, `suspended`.
- Middleware:
  - `auth`: wajib login.
  - `licensed`: wajib subscription aktif.
  - `admin`: akses admin panel.

## Model Data (MySQL)

### `users`

- `id` (bigint)
- `name` (string)
- `email` (string unique)
- `password` (hashed)
- `role` (enum: `user`, `admin`)
- `status` (enum: `active`, `suspended`) — ini untuk blokir akun
- `timezone` (string, default `Asia/Jakarta`) — opsional
- `created_at`, `updated_at`

### `plans`

- `id`
- `code` (string unique) contoh: `basic_1m`, `pro_3m`
- `name`
- `duration_months` (int)
- `price` (decimal)
- `is_active` (bool)
- `created_at`, `updated_at`

### `subscriptions`

- `id`
- `user_id`
- `plan_id` (nullable) — plan terakhir
- `status` (enum: `active`, `expired`, `suspended`)
- `starts_at` (datetime)
- `ends_at` (datetime)
- `created_at`, `updated_at`

Aturan:
- User boleh punya tepat 1 subscription aktif (paling mudah). Jika ada renew, update row yang sama.

## Rule Bisnis

### Status Akses

- `user.status = suspended` => blok semua akses.
- `subscription.status = active` dan `ends_at >= now()` => akses ok.
- Lainnya => redirect ke halaman paywall/upgrade.

### Timezone

- Default backend timezone `Asia/Jakarta`.
- Semua timestamp disimpan sebagai datetime (MySQL) dan diperlakukan sebagai WIB.

## UI (React + Inertia, Mobile-First)

### Screen: Auth

- `/login`: email + password.
- `/register`: name + email + password.
- `/forgot-password`: request link reset.

### Screen: Paywall

- Jika `licensed` gagal: tampilkan halaman:
  - status subscription
  - CTA: pilih paket
  - input voucher
  - tombol bayar (Midtrans)

## API (JSON) yang Dipakai

### Get current user

`GET /api/me`

Response:
```json
{
  "ok": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Azhar",
      "email": "azhar@example.com",
      "role": "user",
      "status": "active",
      "timezone": "Asia/Jakarta"
    },
    "subscription": {
      "status": "active",
      "plan": {"code": "basic_1m", "name": "Basic 1 Bulan"},
      "ends_at": "2026-05-01T00:00:00+07:00"
    }
  },
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

### Check license gate

`GET /api/license/status`

Response:
```json
{
  "ok": true,
  "data": {
    "licensed": true,
    "reason": null,
    "ends_at": "2026-05-01T00:00:00+07:00"
  },
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

