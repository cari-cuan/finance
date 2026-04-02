# Voucher & Discount

## Tujuan

- Mendukung diskon saat checkout.
- Bisa untuk campaign, referral, dan pricing fleksibel.
- Validasi ketat di backend.

## Model Data (MySQL)

### `vouchers`

- `id`
- `code` (string unique)
- `type` (enum: `percent`, `fixed`)
- `value` (decimal)
- `active` (bool)
- `starts_at` (datetime)
- `ends_at` (datetime)
- `max_uses` (int, nullable)
- `max_uses_per_user` (int, nullable)
- `min_amount` (decimal, nullable)
- `plan_scope` (enum: `all`, `selected`)
- `notes` (text, nullable)
- `created_at`, `updated_at`

### `voucher_plan`

Pivot jika `plan_scope=selected`:

- `voucher_id`
- `plan_id`

### `voucher_redemptions`

- `id`
- `voucher_id`
- `user_id`
- `order_id`
- `redeemed_at`

## Rule Bisnis

- Voucher valid jika:
  - `active=true`
  - now dalam range `starts_at..ends_at`
  - `max_uses` belum habis (jika ada)
  - `max_uses_per_user` belum habis untuk user itu (jika ada)
  - `min_amount` terpenuhi (jika ada)
  - plan masuk scope (all/selected)

### Perhitungan Diskon

- `percent`: `discount = floor(base_price * percent/100)`
- `fixed`: `discount = min(value, base_price)`
- `final_price = max(base_price - discount, 0)`

## API (JSON)

### Validate/apply voucher (preview)

`POST /api/vouchers/apply`

Request:
```json
{
  "plan_code": "basic_1m",
  "voucher_code": "HEMAT10"
}
```

Response:
```json
{
  "ok": true,
  "data": {
    "base_price": 49000,
    "discount_amount": 4900,
    "final_price": 44100,
    "voucher": {
      "code": "HEMAT10",
      "type": "percent",
      "value": 10
    }
  },
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

Jika tidak valid:

```json
{
  "ok": false,
  "message": "Voucher tidak valid atau sudah habis",
  "data": null,
  "meta": {"request_id": "...", "ts": "..."},
  "errors": [{"code": "VOUCHER_INVALID", "field": "voucher_code", "detail": "..."}]
}
```

## UI (React)

- Input voucher + tombol Apply.
- Tampilkan:
  - harga awal
  - diskon
  - harga akhir
- Jika voucher invalid, tampilkan error message.

