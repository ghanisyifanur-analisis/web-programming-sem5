# InventarisKu — Sistem Inventaris Kampus

Proyek mata kuliah **Web Programming Semester 5** berupa aplikasi CRUD inventaris laboratorium dan fasilitas kampus menggunakan PHP dan MySQL.

## Fitur
- Dashboard statistik jenis barang, jumlah unit, kondisi baik, dan barang rusak.
- CRUD inventaris: tambah, edit, dan hapus.
- Pencarian menurut kode, nama, kategori, atau lokasi.
- Filter kategori dan kondisi barang.
- Validasi server-side, kode unik, PDO prepared statements, output HTML di-escape, dan token CSRF.
- Database SQL dengan delapan data contoh dan antarmuka responsif.

## Teknologi
PHP 8.1+, PDO MySQL, MySQL 8 / MariaDB, HTML5, CSS3, Git dan GitHub.

## Instalasi menggunakan XAMPP (Windows)
1. Salin folder inventaris-kampus ke folder htdocs XAMPP.
2. Aktifkan Apache dan MySQL melalui XAMPP Control Panel.
3. Buka phpMyAdmin, pilih Import, lalu import file database.sql.
4. Buka alamat localhost/inventaris-kampus di browser.

Konfigurasi default: host 127.0.0.1, database inventaris_kampus, user root, password kosong. Jika perlu, konfigurasi dapat diatur melalui environment variables DB_HOST, DB_NAME, DB_USER, dan DB_PASSWORD.

## Struktur
    inventaris-kampus/
    ├── assets/style.css
    ├── config/database.php
    ├── includes/functions.php
    ├── database.sql
    ├── index.php
    └── README.md

## Catatan
Proyek ini untuk praktikum lokal, belum siap untuk deployment publik. Sebelum rilis, tambahkan autentikasi, otorisasi, HTTPS, pengelolaan rahasia, logging, dan backup database. Ide pengembangan lanjutan: riwayat peminjaman/pengembalian, unggah foto aset, dan ekspor laporan.

Dibuat untuk pembelajaran HTML, CSS, PHP, MySQL, CRUD, validasi server-side, dan Git/GitHub.