# Admin Panel (Monitoring)

## Tujuan

- Admin dapat memonitor user, subscription, order/payment, voucher, dan job report.
- Support action: suspend user, extend subscription manual.

## Role & Access

- User role `admin` saja.
- Route group dengan middleware `auth` + `admin`.

## Fitur Admin MVP

### 1) Admin Dashboard

- KPI ringkas:
  - total user
  - user aktif vs expired
  - order paid bulan ini
  - revenue bulan ini

### 2) Users

- List users:
  - nama, email
  - status user (active/suspended)
  - subscription status + ends_at
  - last login (opsional)
- Actions:
  - suspend/unsuspend
  - extend subscription (tambah X hari/bulan)

### 3) Orders & Payments

- List orders:
  - status
  - plan
  - base/discount/final
  - created_at
- Detail payment:
  - midtrans provider_order_id
  - status
  - payload webhook terakhir

### 4) Vouchers

- CRUD voucher
- Monitoring pemakaian voucher

### 5) Reports/Jobs

- Log export/email report
- status success/failed

## API (JSON) (Admin)

### List users

`GET /api/admin/users?status=active&licensed=1&page=1`

Response:
```json
{
  "ok": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Azhar",
        "email": "azhar@example.com",
        "status": "active",
        "subscription": {"status": "active", "ends_at": "2026-05-01T00:00:00+07:00"}
      }
    ],
    "pagination": {"page": 1, "per_page": 20, "total": 1}
  },
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

### Suspend user

`POST /api/admin/users/{id}/suspend`

Response:
```json
{ "ok": true, "data": {"suspended": true}, "meta": {"request_id": "...", "ts": "..."}, "errors": [] }
```

### Extend subscription

`POST /api/admin/users/{id}/subscription/extend`

Request:
```json
{ "extend_months": 1 }
```

Response:
```json
{
  "ok": true,
  "data": {"ends_at": "2026-06-01T00:00:00+07:00"},
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

## UI (React)

- Tetap mobile-first:
  - admin dashboard berupa card
  - list user/order dengan filter dropdown + search input
  - detail via modal/bottom sheet

