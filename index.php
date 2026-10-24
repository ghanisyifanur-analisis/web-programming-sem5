<?php
$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'siakad';
$user = getenv('DB_USER') ?: 'siakaduser';
$pass = getenv('DB_PASSWORD') ?: 'siakadpass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . htmlspecialchars($e->getMessage()));
}

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO mahasiswa (nim, nama, jurusan, angkatan) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$_POST['nim'], $_POST['nama'], $_POST['jurusan'], $_POST['angkatan']]);
            $message = 'Data mahasiswa berhasil ditambahkan.';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Gagal menambahkan: ' . htmlspecialchars($e->getMessage());
            $messageType = 'error';
        }
    } elseif ($action === 'update' && !empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE mahasiswa SET nim=?, nama=?, jurusan=?, angkatan=? WHERE id=?");
        try {
            $stmt->execute([$_POST['nim'], $_POST['nama'], $_POST['jurusan'], $_POST['angkatan'], $_POST['id']]);
            $message = 'Data mahasiswa berhasil diperbarui.';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Gagal memperbarui: ' . htmlspecialchars($e->getMessage());
            $messageType = 'error';
        }
    } elseif ($action === 'delete' && !empty($_POST['id'])) {
        $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id=?");
        $stmt->execute([$_POST['id']]);
        $message = 'Data mahasiswa berhasil dihapus.';
        $messageType = 'success';
    }
}

// Prepare edit data
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}

// Fetch list
$mahasiswa = $pdo->query("SELECT * FROM mahasiswa ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Programming Semester 5 - Data Mahasiswa</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <h1>Sistem Data Mahasiswa</h1>
    <p>Proyek MK Web Programming Semester 5 dengan Docker & CI/CD.</p>

    <?php if ($message): ?>
        <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <h2><?= $edit ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?></h2>
    <form method="POST" action="?">
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
        <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($edit['id']) ?>">
        <?php endif; ?>
        <input type="text" name="nim" placeholder="NIM" required
               value="<?= htmlspecialchars($edit['nim'] ?? '') ?>">
        <input type="text" name="nama" placeholder="Nama Lengkap" required
               value="<?= htmlspecialchars($edit['nama'] ?? '') ?>">
        <input type="text" name="jurusan" placeholder="Jurusan" required
               value="<?= htmlspecialchars($edit['jurusan'] ?? '') ?>">
        <input type="number" name="angkatan" placeholder="Angkatan" required
               value="<?= htmlspecialchars($edit['angkatan'] ?? '') ?>">
        <button type="submit"><?= $edit ? 'Simpan Perubahan' : 'Tambah' ?></button>
        <?php if ($edit): ?>
            <a href="?" class="button"><button type="button" class="secondary">Batal</button></a>
        <?php endif; ?>
    </form>

    <h2>Daftar Mahasiswa</h2>
    <table>
        <thead>
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Jurusan</th>
                <th>Angkatan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mahasiswa as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['nim']) ?></td>
                    <td><?= htmlspecialchars($m['nama']) ?></td>
                    <td><?= htmlspecialchars($m['jurusan']) ?></td>
                    <td><?= htmlspecialchars($m['angkatan']) ?></td>
                    <td class="actions">
                        <a href="?edit=<?= $m['id'] ?>"><button>Edit</button></a>
                        <form method="POST" action="?" onsubmit="return confirm('Yakin ingin menghapus?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($mahasiswa)): ?>
                <tr><td colspan="5">Belum ada data mahasiswa.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="status">
        <strong>Status:</strong> <?= phpversion() ?> | DB Host: <?= htmlspecialchars($host) ?> | Total data: <?= count($mahasiswa) ?>
    </div>
</div>
</body>
</html>
