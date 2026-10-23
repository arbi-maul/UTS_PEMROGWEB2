# UTS PEMROGRAMAN 2

Portal aplikasi PHP berisi CRUD biodata mahasiswa melalui Google Sheets / Apps Script dan integrasi dataset Apify untuk menampilkan akun, komentar, sentimen kata kunci, serta link sumber TikTok.

## Identitas

- Nama: Arbi Maulana
- NIM: 231011402268
- Kelas: 07TPLP032

## Menjalankan di Laragon

1. Simpan folder proyek ke `C:\laragon\www\UTS_PEMROGRAMAN_2`.
2. Salin `config.example.php` menjadi `config.local.php`.
3. Isi URL deployment Apps Script, API key, token Apify, dan ID dataset Anda di `config.local.php`.
4. Aktifkan ekstensi PHP `curl` dan `mbstring`, lalu jalankan Apache.
5. Buka `http://localhost/UTS_PEMROGRAMAN_2/`.

Alternatif: dari folder proyek jalankan `php -S localhost:8000`, lalu buka `http://localhost:8000`.

Deploy Apps Script harus aktif, dapat diakses aplikasi, serta menggunakan API key yang sesuai. API memerlukan koneksi internet. Klik **Ambil Data dari Apify** untuk memuat dataset.

Konfigurasi juga dapat diberikan melalui environment variable: `APPS_SCRIPT_URL`, `APPS_SCRIPT_KEY`, `APIFY_TOKEN`, dan `APIFY_DATASET_ID`.

## Struktur

- `index.php`: beranda
- `crud/index.php`: CRUD biodata
- `apify/index.php`: integrasi Apify
- `config.example.php`: contoh konfigurasi tanpa rahasia
- `.gitignore`: mengecualikan konfigurasi lokal dan cache

## Mengunggah ke GitHub

Buat repository, pilih **Add file > Upload files**, lalu unggah isi folder proyek. Jangan unggah `config.local.php`, `.env`, atau `apify/cache.json`, termasuk melalui unggahan manual. `.gitignore` berlaku saat memakai Git dan bukan penyaring unggahan browser.

GitHub menyimpan kode proyek. GitHub Pages tidak menjalankan PHP; gunakan Laragon atau hosting yang mendukung PHP untuk menjalankan aplikasi.

## Validasi

Tautan lokal telah diperiksa. Eksekusi PHP dan koneksi API belum diuji pada lingkungan pembuat paket.
