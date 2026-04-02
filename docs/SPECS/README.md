# Spesifikasi Per Feature (MVP)

Dokumen ini berisi spesifikasi detail per feature untuk MVP. Fokus: **mobile-first**, data per akun, bisa dijual (subscription), checkout Midtrans + voucher, export Excel + email report, admin monitoring.

## Daftar Dokumen

1. [Account & Subscription](./account-subscription.md)
2. [Layout & Navigation (Mobile-First)](./layout-mobile-first.md)
3. [Core Pencatatan Keuangan](./finance-core.md)
4. [Chat Input (Parser, Konfirmasi, Fast Input)](./chat-input.md)
5. [Checkout Midtrans (Order/Payment/Webhook)](./checkout-midtrans.md)
6. [Voucher & Discount](./voucher-discount.md)
7. [Export Excel & Email Report](./excel-email-report.md)
8. [Admin Panel (Monitoring)](./admin-panel.md)
9. [Help Center / Docs In-App](./help-center.md)
10. [Auto Backup (Post-MVP)](./auto-backup.md)

## TODO MVP (9 item)

Berikut 9 TODO yang akan dikerjakan berurutan (sesuai prioritas):

1. Bangun auth, role, dan subscription gate (licensed)
2. Implementasi core keuangan: transaksi, kategori, rekap, dashboard
3. Implementasi chat input + konfirmasi OK/batal + fast input
4. Bangun checkout Midtrans + webhook + order/payment
5. Buat admin panel untuk monitoring user, order, subscription
6. Implementasi voucher/discount + aturan penggunaan
7. Export laporan bulanan Excel + kirim email
8. Buat Help Center/Docs in-app + halaman FAQ
9. Auto-backup berkala + opsi link Google Drive

## Konvensi API (JSON)

Walau UI utama memakai React + Inertia (props), untuk interaksi realtime (chat, apply voucher, create order, dll) gunakan JSON API dengan envelope berikut:

```json
{
  "ok": true,
  "message": "optional string",
  "data": {},
  "meta": {
    "request_id": "uuid",
    "ts": "2026-04-01T05:06:00+07:00"
  },
  "errors": []
}
```

Jika error:

```json
{
  "ok": false,
  "message": "Human readable error",
  "data": null,
  "meta": {"request_id": "uuid", "ts": "..."},
  "errors": [
    {"code": "VALIDATION_ERROR", "field": "amount", "detail": "Amount is required"}
  ]
}
```

