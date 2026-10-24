-- InventarisKu: skema dan data awal untuk MySQL 8 / XAMPP
CREATE DATABASE IF NOT EXISTS inventaris_kampus
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventaris_kampus;

CREATE TABLE IF NOT EXISTS barang (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    kode VARCHAR(30) NOT NULL,
    nama_barang VARCHAR(150) NOT NULL,
    kategori VARCHAR(60) NOT NULL,
    lokasi VARCHAR(100) NOT NULL,
    jumlah INT UNSIGNED NOT NULL DEFAULT 0,
    kondisi ENUM('Baik', 'Perlu Perbaikan', 'Rusak') NOT NULL DEFAULT 'Baik',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_barang_kode (kode),
    KEY idx_barang_nama (nama_barang),
    KEY idx_barang_kategori (kategori),
    KEY idx_barang_kondisi (kondisi)
) ENGINE=InnoDB;

INSERT IGNORE INTO barang (kode, nama_barang, kategori, lokasi, jumlah, kondisi) VALUES
('LAB-KOM-001', 'Komputer Desktop', 'Perangkat Keras', 'Lab Komputer 1', 24, 'Baik'),
('LAB-KOM-002', 'Monitor 24 Inci', 'Perangkat Keras', 'Lab Komputer 1', 24, 'Baik'),
('LAB-JAR-001', 'Router Gigabit', 'Jaringan', 'Lab Jaringan', 4, 'Baik'),
('LAB-JAR-002', 'Switch 24 Port', 'Jaringan', 'Lab Jaringan', 3, 'Perlu Perbaikan'),
('LAB-PER-001', 'Keyboard USB', 'Periferal', 'Gudang Lab', 30, 'Baik'),
('LAB-PER-002', 'Proyektor LCD', 'Periferal', 'Ruang Kelas A', 2, 'Rusak'),
('LAB-FUR-001', 'Meja Komputer', 'Furniture', 'Lab Komputer 1', 24, 'Baik'),
('LAB-LAB-001', 'Multimeter Digital', 'Laboratorium', 'Lab Elektronika', 10, 'Perlu Perbaikan');