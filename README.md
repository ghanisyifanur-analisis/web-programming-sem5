# Web Programming Semester 5 - Data Mahasiswa

Proyek sederhana MK Web Programming Semester 5: aplikasi CRUD data mahasiswa berbasis **PHP + MySQL**, dikemas dengan **Docker + Docker Compose**, dan dilengkapi pipeline **CI/CD GitHub Actions** (build, push image ke Docker Hub, serta deploy otomatis ke VPS).

## Fitur

- Tambah, edit, hapus data mahasiswa.
- Validasi NIM unik.
- Tampilan responsif dengan CSS native.
- Database MySQL 8.0 dengan seed data awal.
- phpMyAdmin untuk manajemen database.

## Teknologi

- PHP 8.2 (Apache)
- MySQL 8.0
- Docker & Docker Compose
- GitHub Actions (CI/CD)

## Cara Menjalankan Lokal

1. Copy konfigurasi environment:
   ```bash
   cp .env.example .env
   ```

2. Build dan jalankan container:
   ```bash
   docker compose up --build -d
   ```

3. Buka aplikasi di browser:
   - Web: http://localhost:8080
   - phpMyAdmin: http://localhost:8081

4. Stop container:
   ```bash
   docker compose down
   ```

## Struktur Folder

```
web-programming-sem5/
├── .github/workflows/   # CI/CD pipeline
├── assets/              # CSS
├── docker/db/           # Image & init script database
├── Dockerfile           # Image PHP-Apache
├── docker-compose.yml   # Orkestrasi lokal web + db + phpmyadmin
├── docker-compose.prod.yml # Orkestrasi production (image prebuilt)
├── index.php            # Aplikasi utama
├── .env.example         # Template environment
└── README.md
```

## CI/CD Pipeline

### 1. PR Check (`.github/workflows/pr-check.yml`)
- Build image Docker.
- Jalankan container.
- Lakukan smoke test dengan `curl` ke halaman utama.

### 2. Build, Push & Deploy (`.github/workflows/ci-cd.yml`)
- Trigger: push ke branch `main`.
- Build image Docker.
- Push ke **GitHub Container Registry (GHCR)** — pakai `GITHUB_TOKEN`, tidak perlu Docker Hub.
- Deploy otomatis ke VPS via SSH (opsional, aktifkan dengan mengisi `vars.DEPLOY_HOST`).

### Secrets & Variables yang Diperlukan

| Nama | Tipe | Keterangan |
|------|------|------------|
| `GITHUB_TOKEN` | Otomatis | Disediakan GitHub Actions, sudah punya permission `packages: write`. |
| `SSH_USER` | Secret | User SSH VPS (jika deploy) |
| `SSH_KEY` | Secret | Private key SSH (jika deploy) |
| `DEPLOY_HOST` | Variable | Host/IP VPS (jika deploy) |
| `DEPLOY_PATH` | Variable | Path folder aplikasi di VPS (jika deploy) |

## Catatan

- Gunakan `.env` untuk konfigurasi lokal; file ini tidak perlu di-commit.
- Port database di-host dipetakan ke `3307` agar tidak bentrok dengan MySQL lokal.
