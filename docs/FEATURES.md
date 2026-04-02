# Fitur Aplikasi Keuangan (Mobile-First)

Dokumen ini menjelaskan fitur aplikasi dan membedakan antara fitur **Pengguna** dan **Admin**.

## Ringkasan Produk

Aplikasi pencatatan keuangan pribadi berbasis chat dengan tampilan mobile-first, terdiri dari 3 menu utama:

- Dashboard
- Chat
- Rekap

Fokus utama: input cepat, alur konfirmasi aman sebelum simpan, dan laporan bulanan yang rapi.

## Dokumen Spesifikasi Detail (Per Feature)

Lihat folder [docs/SPECS](../docs/SPECS/README.md) untuk spesifikasi teknis per feature, rencana layout mobile-first, dan contoh format JSON response API.

## Istilah

- **Pemasukan (Kredit)**: uang masuk.
- **Pengeluaran (Debit)**: uang keluar.
- **Lisensi/Subscription**: masa aktif akun untuk memakai fitur premium.
- **Order**: transaksi pembelian paket.
- **Payment**: pembayaran order melalui Midtrans.
- **Voucher**: kode diskon yang memengaruhi harga checkout.

## Fitur Pengguna (User)

### 1) Akun & Akses

- Registrasi akun.
- Login/Logout.
- Reset password.
- Status akun:
  - **Aktif**: bisa memakai aplikasi.
  - **Expired**: akses dibatasi sampai diperpanjang.

### 2) Navigasi Utama (Mobile)

- Bottom navigation 3 menu: **Dashboard**, **Chat**, **Rekap**.
- UI mobile-first (tanpa layout desktop khusus).

### 3) Dashboard

Ringkasan cepat periode bulan aktif:

- Total pemasukan.
- Total pengeluaran.
- Sisa (pemasukan - pengeluaran).
- Grafik perbandingan pemasukan vs pengeluaran bulanan (komparasi beberapa bulan terakhir).
- Top kategori pengeluaran.
- Transaksi terbaru.

### 4) Chat (Input Utama)

Chat adalah cara utama input dan tanya-jawab.

#### 4.1 Input transaksi natural

Contoh:

- "beli cilok 10rb"
- "gaji 5jt"
- "bayar listrik 350rb"

#### 4.2 Alur konfirmasi aman

Jika user mengirim transaksi:

1. Sistem parsing (jenis, nominal, tanggal/waktu, kategori, deskripsi).
2. Sistem tampilkan ringkasan.
3. Muncul quick reply: **OK** / **batal**.
4. Baru disimpan setelah user memilih/mengetik **OK**.

#### 4.3 Fast Input

- Tombol pilih jenis: **Pemasukan** / **Pengeluaran** (memberi indikator selected).
- Pilih kategori (chip selectable).
- Pilih tanggal (tahun/bulan/tanggal).
- Pilih nominal cepat (3 preset) + nominal kustom (± step).
- Hasil pilihan akan mengisi input chat sehingga user bisa menambahkan deskripsi, lalu kirim.

#### 4.4 Waktu (WIB)

- Timezone default aplikasi: **Asia/Jakarta**.
- Jika user memilih tanggal tanpa jam, sistem akan memakai jam saat input (bukan 00:00).

### 5) Rekap

- List bulanan: pemasukan, pengeluaran, sisa.
- Detail bulan bisa dibuka (click) dan menampilkan laporan debit/kredit.

#### 5.1 Detail laporan (format rapi)

- Kolom minimal:
  - Waktu (tanggal + jam)
  - Deskripsi + kategori
  - Nominal dengan tanda **+ (kredit)** / **- (debit)**

### 6) Export & Email Report (Bulanan)

- Download laporan bulanan format Excel.
- Kirim laporan bulanan ke email user.

Format Excel yang disarankan:

- Tanggal
- Jam
- Keterangan
- Kategori
- Debit (Keluar)
- Kredit (Masuk)
- Saldo berjalan

### 7) Checkout, Voucher, dan Pembayaran

- User pilih paket (mis. 1 bulan, 3 bulan, 12 bulan).
- User bisa memasukkan voucher (diskon persen/nominal).
- Pembayaran menggunakan Midtrans.
- Status lisensi diperbarui setelah payment terverifikasi.

### 8) Backup Otomatis (Opsional setelah MVP)

- Auto-backup Excel berkala.
- Opsi link Google Drive untuk menyimpan backup.

## Fitur Admin

### 1) Admin Dashboard

- Total user aktif/expired.
- Ringkasan order/pembayaran.
- Ringkasan revenue per periode.

### 2) Manajemen User

- Lihat daftar user.
- Lihat status subscription (aktif/expired).
- Suspend/unsuspend.
- Extend subscription manual (untuk support).

### 3) Manajemen Paket (Plan)

- Buat/ubah paket.
- Set durasi dan harga.
- Aktif/nonaktif paket.

### 4) Manajemen Voucher

- Buat voucher:
  - tipe diskon: persen atau nominal
  - masa berlaku
  - kuota
  - batas per user
  - minimum amount (opsional)
  - scope plan (opsional)
- Monitoring pemakaian voucher.

### 5) Monitoring Order & Payment

- Daftar order dan status.
- Detail payment Midtrans dan payload webhook.
- Audit log webhook.

### 6) Monitoring Export & Email Report

- Status job export (success/fail).
- Status pengiriman email report.

## Non-Functional Requirements (NFR)

- Mobile-first dan ringan.
- Tidak menyimpan transaksi tanpa konfirmasi.
- Timezone konsisten (WIB).
- Audit-friendly untuk pembayaran (log webhook).
- Keamanan: jangan pernah percaya status payment dari frontend; hanya dari webhook.
