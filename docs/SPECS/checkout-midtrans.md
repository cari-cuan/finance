# Checkout Midtrans (Order/Payment/Webhook)

## Tujuan

- User bisa beli paket subscription.
- Midtrans sebagai payment gateway.
- Status subscription hanya berubah berdasarkan **webhook Midtrans**.

## Model Data (MySQL)

### `orders`

- `id`
- `user_id`
- `plan_id`
- `base_price`
- `discount_amount`
- `final_price`
- `voucher_id` (nullable)
- `status` (enum: `draft`, `pending`, `paid`, `failed`, `expired`)
- `expires_at` (datetime)
- `created_at`, `updated_at`

### `payments`

- `id`
- `order_id`
- `provider` (enum: `midtrans`)
- `provider_order_id` (string, unique)
- `status` (enum: `pending`, `paid`, `failed`, `expired`)
- `raw_payload` (json)
- `created_at`, `updated_at`

### `midtrans_webhook_logs`

- `id`
- `provider_order_id`
- `event_type` (string)
- `payload` (json)
- `verified` (bool)
- `created_at`

## Rule Bisnis

### Harga Final

- Dihitung di backend dari plan + voucher.
- Harga final yang dikirim ke Midtrans harus sama dengan `orders.final_price`.

### Webhook sebagai Sumber Kebenaran

- Frontend status payment tidak dianggap valid.
- Hanya event Midtrans terverifikasi yang mengubah order/subscription.

## Flow

1. User pilih plan.
2. User input voucher (opsional).
3. Backend buat `order` status `pending`.
4. Backend request Snap token ke Midtrans.
5. User bayar.
6. Midtrans webhook -> backend verify signature -> update `payments` + `orders`.
7. Jika paid -> extend subscription.

## API (JSON)

### Create order

`POST /api/checkout/orders`

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
    "order": {
      "id": 77,
      "status": "pending",
      "base_price": 49000,
      "discount_amount": 4900,
      "final_price": 44100
    },
    "midtrans": {
      "snap_token": "...",
      "provider_order_id": "ORD-77-20260401"
    }
  },
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

### Get order status

`GET /api/checkout/orders/{id}`

Response:
```json
{
  "ok": true,
  "data": {
    "order": {
      "id": 77,
      "status": "paid",
      "final_price": 44100
    }
  },
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

### Midtrans webhook

`POST /api/webhooks/midtrans`

Payload: mengikuti standar Midtrans.

Response:
```json
{
  "ok": true,
  "data": {"received": true},
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

## UI (React)

- Screen `Upgrade/Checkout`:
  - plan cards
  - voucher input + apply
  - total ringkas
  - button "Bayar" -> open Midtrans Snap

