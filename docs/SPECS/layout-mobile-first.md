# Layout & Navigation (Mobile-First)

## Prinsip

- Fokus mobile-only (tanpa layout desktop khusus).
- Touch target minimal 44×44px.
- Bottom navigation fixed dengan safe-area (iOS).
- Performa cepat: lazy-load page, minim library berat.

## Struktur Navigasi

### Setelah login & lisensi aktif

- Bottom nav 3 menu:
  - Dashboard
  - Chat
  - Rekap

### Saat belum lisensi

- Paywall/Upgrade flow:
  - pilih paket
  - apply voucher
  - bayar Midtrans

## Layout Global

### Header

- Judul halaman (Dashboard/Chat/Rekap)
- Icon profil/menu (opsional)

### Body

- Konten scrollable

### Bottom Nav

- Fixed, tinggi 56–64px
- Aman dari overlap dengan input chat

## Screen Spec

### Dashboard

- Cards ringkas (income, expense, balance)
- Chart bulanan income vs expense (6 bulan)
- Top kategori expense (3)
- List transaksi terbaru

### Chat

- Chat history
- Typing indicator
- Quick reply OK/batal (muncul saat ada pending)
- Fast input panel:
  - pilih type (income/expense)
  - pilih kategori
  - pilih tanggal (tahun/bulan/tanggal)
  - pilih nominal preset + nominal custom
  - input teks untuk note/deskripsi

### Rekap

- List bulanan (income, expense, sisa)
- Tap untuk buka detail bulan
- Detail dalam modal/bottom sheet:
  - tabel laporan (waktu, deskripsi/kategori, nominal +/−)

## Komponen UI (React)

Disarankan struktur komponen:

- `AppShell`
  - `TopBar`
  - `BottomNav`
  - `Content`
- `DashboardPage`
  - `SummaryCards`
  - `MonthlyChart`
  - `TopCategories`
  - `RecentTransactions`
- `ChatPage`
  - `ChatHistory`
  - `TypingIndicator`
  - `QuickReplies`
  - `FastInputPanel`
  - `ChatComposer`
- `RecapPage`
  - `MonthlyList`
  - `MonthDetailSheet`

## UX Rules

- Semua aksi simpan transaksi wajib ada konfirmasi OK/batal.
- Jangan mengganggu user dengan modal berlebihan.
- Input default:
  - tanggal = hari ini
  - jam = jam sekarang
  - kategori = pilihan user

