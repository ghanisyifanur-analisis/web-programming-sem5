USE siakad;

CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    angkatan INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO mahasiswa (nim, nama, jurusan, angkatan) VALUES
('21001', 'Ahmad Fauzi', 'Teknik Informatika', 2021),
('21002', 'Budi Santoso', 'Sistem Informasi', 2021),
('21003', 'Citra Lestari', 'Teknik Komputer', 2022);
