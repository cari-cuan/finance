# Spesifikasi Desain Halaman (Mobile-first saja)

Dokumen ini mendeskripsikan UI untuk refactor Laravel + Inertia + React dengan pendekatan **mobile-first murni** (tanpa layout desktop/desktop-specific). Navigasi utama memakai **bottom navigation 3 menu: Dashboard, Chat, Rekap**.

## Global Styles (berlaku untuk semua halaman)
- Layout system: Flexbox (utama) + CSS Grid (untuk list/kartu ringkas), Tailwind utility-first.
- Breakpoints: fokus **<= 480–600px**. Pada layar lebih besar, UI **tetap memakai layout mobile** (boleh “centered” dengan `max-w-screen-sm`, tanpa pola 2 kolom/desktop).
- Safe area: elemen fixed (bottom nav, composer) wajib `pb-[env(safe-area-inset-bottom)]`.
- Touch targets: minimum **44×44px**; tombol utama tinggi **52–56px**; icon button minimal **48×48px**.
- Spacing: 16px (default), 12px (komponen rapat), 24px (antar-seksi).
- Warna (contoh token Tailwind):
  - Background: `slate-50`; Surface/Card: `white`; Border: `slate-200`
  - Primary: `indigo-600` (pressed/active `indigo-700`)
  - Positif (kredit): `emerald-600`; Negatif (debit): `rose-600`
  - Text: `slate-900` / `slate-600`
- Tipografi: Inter/system; skala ringkas: H1 20/28, H2 16/24, body 14/20, caption 12/16.
- Komponen umum:
  - Button: primary/secondary/danger; state: disabled, loading, focus ring.
  - Input: label + helper/error; tinggi input 48–52px.
  - Card: radius 12–16, shadow halus, padding 16.
  - List item: tinggi baris 56–72px (mudah di-tap), trailing chevron opsional.
  - Toast/Alert: sukses/gagal, auto-dismiss 4–6 detik.

## Navigasi Global
### Bottom Navigation (selalu tampil setelah login)
- Posisi: fixed bottom, lebar penuh, tinggi 64–72px (termasuk safe area).
- Item: 3 (Dashboard, Chat, Rekap) dengan ikon + label.
- State:
  - Active: ikon + label `indigo-600`, indikator (underline kecil / pill background).
  - Inactive: `slate-500`.
- Perilaku:
  - Tap item berpindah halaman (Inertia navigation).
  - Area konten halaman wajib `pb` cukup agar tidak ketutup bottom nav.

---

## 1) Halaman Autentikasi
### Meta Information
- Title: "Masuk / Daftar"
- Description: "Autentikasi untuk mengakses rekap dan chat."

### Layout & Page Structure (mobile)
1. Header kecil: logo + nama aplikasi.
2. Auth Card (stack): segmented control "Masuk" | "Daftar".
3. Form aktif.
4. Footer kecil: bantuan + kebijakan.

### Sections & Components
- Form Masuk: Email, Password, Remember me, tombol "Masuk" (56px), link "Lupa password".
- Form Daftar: Nama, Email, Password, Konfirmasi Password, tombol "Buat Akun".
- Reset Password: input email request link; halaman reset token: password baru + konfirmasi.

---

## 2) Halaman Dashboard
### Meta Information
- Title: "Dashboard"
- Description: "Ringkasan debit/kredit/saldo dan akses cepat."

### Layout & Page Structure (mobile)
1. Top App Bar (sticky): judul, account switcher, user menu.
2. Ringkasan (scroll vertical): kartu Total Debit, Total Kredit, Saldo.
3. Filter ringkas periode (mis. bulan aktif) + tombol reset.
4. Rekap detail versi mobile: daftar kartu transaksi (bukan tabel).

### Sections & Components
- Top App Bar:
  - Account switcher (tap membuka bottom sheet daftar account).
  - User menu: nama/email ringkas + Logout.
- Kartu Ringkasan:
  - 3 kartu (debit/kredit/saldo) full-width, mudah di-tap (untuk membuka Rekap).
- Rekap Detail (Card List):
  - Item: tanggal, deskripsi, badge tipe, amount.
  - Aksi: scroll infinite/pagination tombol “Muat lagi”.
  - Empty state + CTA “Ubah filter”.

---

## 3) Halaman Chat
### Meta Information
- Title: "Chat"
- Description: "Chat terkait data account aktif."

### Layout & Page Structure (mobile)
1. Top App Bar (sticky): judul "Chat" + account aktif.
2. Message list (flex column, scrollable).
3. Composer (fixed) **di atas bottom navigation**.

### Sections & Components
- Message list:
  - Bubble kiri/kanan, timestamp kecil.
  - Empty state bila belum ada chat.
- Composer:
  - Textarea 1–4 baris, tombol kirim (48×48), state loading.
  - Aksesibilitas: Enter mengirim (opsional), Shift+Enter baris baru.
- Context chips (sesuai kebutuhan inti keterkaitan data): periode aktif + keyword terakhir.

---

## 4) Halaman Rekap
### Meta Information
- Title: "Rekap"
- Description: "Filter, lihat rekap detail, dan aksi laporan bulanan."

### Layout & Page Structure (mobile)
1. Top App Bar (sticky): judul "Rekap".
2. Filter Bar (horizontal scroll / wrap): bulan/periode, pencarian, tipe (debit/kredit), reset.
3. Ringkasan: debit/kredit/saldo.
4. Rekap detail: card list transaksi.
5. Aksi laporan bulanan: Export Excel + Email (sebagai section di bawah).

### Sections & Components
- Filter:
  - Month picker (membuka bottom sheet), search field, filter tipe.
  - Validasi: periode wajib.
- Rekap detail:
  - Sort sederhana via menu (tanggal/amount) di action sheet.
- Aksi laporan:
  - Tombol "Export Excel" (loading -> link unduhan).
  - Email laporan: input email tujuan (default email user) + tombol "Kirim Email" + status queued/gagal.
- Error/Success:
  - Banner error di atas konten; toast sukses dengan timestamp aksi terakhir.
