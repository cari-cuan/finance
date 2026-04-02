# Help Center / Docs In-App

## Tujuan

- Pengguna bisa memahami cara pakai tanpa harus chat support.
- Help Center bisa dibuka dari menu (ikon bantuan).
- Konten ringkas, mobile-first.

## Struktur Halaman

### 1) Getting Started

- Cara daftar/login.
- Cara input transaksi via chat.
- Cara konfirmasi OK/batal.

### 2) Cara Input (Contoh)

- Pengeluaran:
  - "beli cilok 5rb"
  - "bayar listrik 350rb"
- Pemasukan:
  - "gaji 5jt"
  - "bonus 300rb"

### 3) Fast Input

- Pilih pemasukan/pengeluaran
- Pilih kategori
- Pilih tanggal
- Pilih nominal
- Tambah deskripsi

### 4) Rekap & Laporan

- Cara buka detail bulan.
- Arti debit/kredit.
- Cara download Excel.
- Cara kirim email laporan.

### 5) Pembayaran, Paket, Voucher

- Cara upgrade.
- Cara pakai voucher.
- Status payment Midtrans (pending/paid/expired).

### 6) FAQ

- Kenapa transaksi belum tersimpan? (harus OK)
- Nominal tidak terbaca
- Kategori tidak sesuai
- Lisensi expired

## Konten Teknis Minimal

- Jelaskan aturan konfirmasi.
- Jelaskan timezone WIB.
- Jelaskan format laporan debit/kredit.

## UI (React)

- Page `HelpCenterPage`
- Komponen:
  - `Accordion` untuk FAQ
  - `ExampleChips` untuk copy contoh input
- Offline-friendly (konten statis).

## API (JSON)

Help center bisa statis (tanpa API). Jika ingin dinamis:

`GET /api/help/articles`

Response:
```json
{
  "ok": true,
  "data": {
    "items": [
      {"id": "getting-started", "title": "Getting Started", "content": "..."}
    ]
  },
  "meta": {"request_id": "...", "ts": "..."},
  "errors": []
}
```

