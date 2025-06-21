# Aplikasi To-Do List

## Deskripsi
Aplikasi sederhana berbasis PHP untuk mencatat tugas harian.

## Fitur
- Tambah tugas baru
- Tandai tugas selesai
- Hapus tugas
- Edit tugas

## Struktur Folder
- index.php - Aplikasi Utama
- config/database.php - Koneksi Database
- screenshot - Tangkapan result pekerjaan
- .env.example - File konfigurasi env
- .gitignore - File untuk mengabaikan file tertentu dalam repositori Git
- composer.json - File konfigurasi Composer
- composer.lock - File kunci Composer
- README.md - Dokumentasi proyek ini
- db_todolist_bnsp2025.sql - File SQL untuk membuat tabel dan data awal

## Cara menjalankan
1. Salin proyek ini ke dalam folder "htdocs" pada server lokal Anda (XAMPP, WAMP, dll).
2. Ubah .env.example menjadi .env dan sesuaikan konfigurasi database jika diperlukan.
3. Pastikan server lokal Anda berjalan.
4. Buka terminal dan arahkan ke folder proyek, lalu jalankan perintah berikut untuk menginstal dependensi:
   ```bash
   composer install
   ```
5. Pastikan untuk membuat database baru dan mengimpor file SQL yang disediakan (Bisa gunakan db_todolist_bnsp2025.sql).
6. Buka browser dan akses `http://localhost/nama_folder_proyek/index.php`.

## Kontributor
- [Alvin Indra Pratama](https://github.com/alvinindra)