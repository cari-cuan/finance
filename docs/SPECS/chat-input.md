# Chat Input (Parser, Konfirmasi, Fast Input)

## Tujuan

- Chat sebagai metode input utama.
- Sistem membaca natural language, tetapi **tidak menyimpan** sebelum user konfirmasi.
- Mendukung fast input (type/kategori/tanggal/nominal) yang mengisi chat composer.

## Konsep State: Pending

Karena alur konfirmasi, kita simpan state sementara:

### `pending_chat_actions`

- `id`
- `user_id`
- `type` (enum: `transaction_confirm`, `category_question`, dll)
- `payload` (json)
- `expires_at` (datetime)
- `created_at`

Payload `transaction_confirm` minimal:

```json
{
  "type": "expense",
  "amount": 5000,
  "category_id": 4,
  "category_name": "Makanan",
  "description": "Cilok",
  "transaction_at": "2026-04-01T05:06:00+07:00",
  "source_text": "beli cilok 5rb"
}
```

## Parser Rules (MVP)

- `amount`: parse rb/k/jt/juta.
- `type`:
  - default `expense`.
  - jika ada keyword pemasukan (`gaji`, `bonus`, `masuk`, dll) => `income`.
- `category`:
  - match nama kategori user.
  - fallback mapping keyword sederhana.
- `description`:
  - hapus filler kata (beli/bayar/pengeluaran/pemasukan/tanggal/nominal).
  - wajib non-kosong; fallback ke kategori.
- `transaction_at`:
  - jika ada tanggal (DD/MM/YYYY) => gunakan tanggal itu + jam sekarang.
  - jika tidak ada tanggal => sekarang (WIB).

## Flow Chat

### A) Input transaksi

1. User kirim teks.
2. Backend parsing -> menghasilkan draft.
3. Backend simpan `pending_chat_actions`.
4. Bot balas ringkasan + quick reply: OK/batal.
5. User:
   - OK => pending disimpan ke `transactions`.
   - batal => pending dihapus.

### B) Jika data tidak lengkap

- Jika `amount` tidak ada => bot tanya nominal.
- Jika `type` ambigu (opsional) => bot tanya pemasukan/pengeluaran.
- Jika kategori tidak ditemukan => tanya kategori (post-MVP bisa multi-step).

## API (JSON)

### Process chat

`POST /api/chat/process`

Request:
```json
{ "text": "beli cilok 5rb 1/4/2026" }
```

Response (pending confirm):
```json
{
  "ok": true,
  "data": {
    "reply": {
      "text": "Saya membaca data ini sebagai...\nKetik OK untuk simpan atau batal.",
      "quick_replies": ["OK", "batal"]
    },
    "pending": {
      "pending_id": "uuid",
      "kind": "transaction_confirm",
      "draft": {
        "type": "expense",
        "amount": 5000,
        "category": {"id": 4, "name": "Makanan"},
        "description": "Cilok",
        "transaction_at": "2026-04-01T05:06:00+07:00"
      }
    }
  },
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

### Confirm pending

`POST /api/chat/pending/confirm`

Request:
```json
{ "pending_id": "uuid" }
```

Response:
```json
{
  "ok": true,
  "data": {"saved": true, "transaction_id": 123},
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

### Cancel pending

`POST /api/chat/pending/cancel`

Request:
```json
{ "pending_id": "uuid" }
```

Response:
```json
{
  "ok": true,
  "data": {"canceled": true},
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

## UI (React)

### Chat Composer

- Input text
- Button send
- Quick reply chips
- Typing indicator

### Fast Input Panel

- Toggle `income/expense`
- Category chips (searchable post-MVP)
- Date picker (year/month/day)
- Preset nominal + custom nominal step

