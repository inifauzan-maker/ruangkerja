# Dokumentasi RuangKerja / TimManager

Versi dokumentasi: **1.2 — 4 September 2026**

## Artefak

- `Dokumentasi_TimManager_Lengkap.docx` — dokumentasi teknis dan panduan pengguna lengkap.
- `timmanager-documentation.drawio` — diagram multipage yang dapat diedit.
- `TIMMANAGER.md` — ringkasan dokumentasi repository.

## Cakupan

- Autentikasi, profil, avatar, dan kata sandi.
- Tim, anggota, undangan Gmail, dan RBAC owner/admin/member.
- Proyek, Kanban, ringkasan, chat grup, dan pengumuman.
- Multi-assignee, checklist, komentar, mention, serta activity log.
- Filter tugas dan pencarian global proyek/tugas/file.
- File privat dengan preview, versioning, izin download, dan antivirus opsional.
- Fonnte WhatsApp API per pengguna, quiet hours, reminder, queue, dan histori.
- Dashboard laporan, beban kerja, aktivitas proyek, histori email/WhatsApp, PDF/XLSX.

## Halaman Draw.io

1. Sitemap
2. ERD Core
3. ERD Collaboration & File
4. RBAC
5. Database Catalog
6. Task Flow
7. File Flow
8. Notification & Reports
9. User Guide
10. Deployment

## Membuka diagram

1. Buka [diagrams.net](https://app.diagrams.net/).
2. Pilih **File > Open From > Device**.
3. Pilih `docs/timmanager-documentation.drawio`.
4. Berpindah diagram melalui tab halaman di bawah editor.

## Quality gate terakhir

- Migration aplikasi seluruhnya berstatus `Ran`.
- 92 test dan 358 assertions lulus.
- Kompilasi Blade dan build frontend berhasil.

## Catatan konfigurasi

Gmail menggunakan SMTP `smtp.gmail.com` dengan Google App Password. Antivirus upload dapat diaktifkan melalui `ATTACHMENT_ANTIVIRUS_ENABLED=true` setelah ClamAV tersedia. Queue worker dan scheduler perlu dijalankan pada server produksi.
