# Auto Backup (Post-MVP)

## Tujuan

- Backup data user sebagai Excel secara berkala.
- Memberi rasa aman dan opsi migrasi.

## Scope

### MVP+

- Backup manual (download) + email report sudah ada.

### Post-MVP

- Auto-backup bulanan (atau mingguan).
- Penyimpanan:
  - default: storage aplikasi (lebih aman untuk akun dijual-belikan)
  - opsional: link Google Drive user untuk mirror

## Model Data

### `backup_exports`

- `id`
- `user_id`
- `period` (`YYYY-MM`)
- `status` (`queued/success/failed`)
- `storage_path`
- `drive_file_id` (nullable)
- `created_at`

### `user_integrations`

- `id`
- `user_id`
- `provider` (`google_drive`)
- `access_token`
- `refresh_token`
- `expires_at`
- `scopes`

## Job

- Scheduler bulanan: generate excel -> save -> optional upload Drive.
- Jika upload gagal, backup tetap ada di storage aplikasi.

## API

`POST /api/settings/integrations/google-drive/connect`

`POST /api/backups/run?period=2026-04`

