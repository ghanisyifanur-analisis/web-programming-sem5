<?php
// Konfigurasi koneksi database. Ubah lewat environment variable bila diperlukan.
$host = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_NAME') ?: 'inventaris_kampus';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPassword = getenv('DB_PASSWORD') ?: '';
$dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    error_log('InventarisKu database connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit(
        '<!doctype html><html lang="id"><meta charset="utf-8">' .
        '<meta name="viewport" content="width=device-width,initial-scale=1">' .
        '<title>Koneksi database gagal</title><body style="font-family:system-ui;padding:2rem;max-width:700px;margin:auto">' .
        '<h1>Koneksi database belum berhasil</h1>' .
        '<p>Pastikan MySQL pada XAMPP aktif dan database <code>inventaris_kampus</code> sudah diimpor dari <code>database.sql</code>.</p>' .
        '<p>Pengaturan default XAMPP: host <code>127.0.0.1</code>, user <code>root</code>, password kosong.</p>' .
        '</body></html>'
    );
}