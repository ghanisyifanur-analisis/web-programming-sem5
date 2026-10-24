<?php
declare(strict_types=1);
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/config/database.php';

$allowedConditions = ['Baik', 'Perlu Perbaikan', 'Rusak'];
$allowedCategories = ['Perangkat Keras', 'Jaringan', 'Periferal', 'Furniture', 'Laboratorium', 'Lainnya'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        set_flash('danger', 'Sesi formulir tidak valid. Muat ulang halaman, lalu coba lagi.');
        redirect();
    }
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$id) {
            set_flash('danger', 'ID inventaris tidak valid.');
            redirect();
        }
        try {
            $statement = $pdo->prepare('DELETE FROM barang WHERE id = ?');
            $statement->execute([$id]);
            set_flash($statement->rowCount() > 0 ? 'success' : 'warning', $statement->rowCount() > 0 ? 'Data inventaris berhasil dihapus.' : 'Data tidak ditemukan.');
        } catch (PDOException $exception) {
            error_log('InventarisKu delete failed: ' . $exception->getMessage());
            set_flash('danger', 'Data belum dapat dihapus. Silakan periksa database.');
        }
        redirect();
    }
    if ($action === 'save') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $kode = strtoupper(trim((string) ($_POST['kode'] ?? '')));
        $nama = trim((string) ($_POST['nama_barang'] ?? ''));
        $kategori = trim((string) ($_POST['kategori'] ?? ''));
        $lokasi = trim((string) ($_POST['lokasi'] ?? ''));
        $jumlah = filter_var($_POST['jumlah'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]]);
        $kondisi = trim((string) ($_POST['kondisi'] ?? ''));

        if ($kode === '' || strlen($kode) > 30 || $nama === '' || strlen($nama) > 150 || $lokasi === '' || strlen($lokasi) > 100) {
            set_flash('danger', 'Kode, nama barang, dan lokasi wajib diisi. Periksa panjang teks yang dimasukkan.');
            redirect($id ? 'index.php?edit=' . $id : 'index.php');
        }
        if (!in_array($kategori, $allowedCategories, true) || !in_array($kondisi, $allowedConditions, true) || $jumlah === false) {
            set_flash('danger', 'Kategori, jumlah, atau kondisi barang tidak valid.');
            redirect($id ? 'index.php?edit=' . $id : 'index.php');
        }
        try {
            $check = $pdo->prepare('SELECT id FROM barang WHERE kode = ? AND id <> ? LIMIT 1');
            $check->execute([$kode, $id ?: 0]);
            if ($check->fetch()) {
                set_flash('danger', 'Kode inventaris sudah digunakan. Gunakan kode yang berbeda.');
                redirect($id ? 'index.php?edit=' . $id : 'index.php');
            }
            if ($id) {
                $statement = $pdo->prepare('UPDATE barang SET kode = ?, nama_barang = ?, kategori = ?, lokasi = ?, jumlah = ?, kondisi = ? WHERE id = ?');
                $statement->execute([$kode, $nama, $kategori, $lokasi, $jumlah, $kondisi, $id]);
                set_flash('success', 'Perubahan data inventaris berhasil disimpan.');
            } else {
                $statement = $pdo->prepare('INSERT INTO barang (kode, nama_barang, kategori, lokasi, jumlah, kondisi) VALUES (?, ?, ?, ?, ?, ?)');
                $statement->execute([$kode, $nama, $kategori, $lokasi, $jumlah, $kondisi]);
                set_flash('success', 'Barang baru berhasil ditambahkan.');
            }
        } catch (PDOException $exception) {
            error_log('InventarisKu save failed: ' . $exception->getMessage());
            set_flash('danger', 'Data belum dapat disimpan. Periksa koneksi database dan pastikan kode inventaris unik.');
        }
        redirect();
    }
    set_flash('warning', 'Aksi tidak dikenali.');
    redirect();
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editItem = null;
if ($editId) {
    $statement = $pdo->prepare('SELECT * FROM barang WHERE id = ?');
    $statement->execute([$editId]);
    $editItem = $statement->fetch() ?: null;
    if (!$editItem) {
        set_flash('warning', 'Data yang akan diedit tidak ditemukan.');
        redirect();
    }
}
$search = trim(is_string($_GET['q'] ?? '') ? $_GET['q'] : '');
$filterCondition = trim(is_string($_GET['kondisi'] ?? '') ? $_GET['kondisi'] : '');
$filterCategory = trim(is_string($_GET['kategori'] ?? '') ? $_GET['kategori'] : '');
$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(kode LIKE ? OR nama_barang LIKE ? OR kategori LIKE ? OR lokasi LIKE ?)';
    $needle = '%' . $search . '%';
    array_push($params, $needle, $needle, $needle, $needle);
}
if (in_array($filterCondition, $allowedConditions, true)) {
    $where[] = 'kondisi = ?';
    $params[] = $filterCondition;
}
if (in_array($filterCategory, $allowedCategories, true)) {
    $where[] = 'kategori = ?';
    $params[] = $filterCategory;
}
$sql = 'SELECT id, kode, nama_barang, kategori, lokasi, jumlah, kondisi, updated_at FROM barang';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY updated_at DESC, nama_barang ASC';
$statement = $pdo->prepare($sql);
$statement->execute($params);
$items = $statement->fetchAll();
$stats = $pdo->query("SELECT COUNT(*) AS total_barang, COALESCE(SUM(jumlah), 0) AS total_unit, COALESCE(SUM(kondisi = 'Baik'), 0) AS kondisi_baik, COALESCE(SUM(kondisi = 'Rusak'), 0) AS kondisi_rusak FROM barang")->fetch();
$flash = get_flash();
$form = $editItem ?: ['id' => '', 'kode' => '', 'nama_barang' => '', 'kategori' => 'Perangkat Keras', 'lokasi' => '', 'jumlah' => 1, 'kondisi' => 'Baik'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplikasi inventaris kampus untuk mengelola barang laboratorium dan fasilitas.">
    <title>InventarisKu — Inventaris Kampus</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="index.php"><span class="brand-mark">IK</span><span>Inventaris<span class="brand-light">Ku</span><small>Campus asset manager</small></span></a>
        <div class="nav-label">MENU UTAMA</div>
        <a class="nav-item active" href="index.php"><span class="nav-icon">▦</span> Ringkasan & Inventaris</a>
        <div class="sidebar-note"><span class="note-dot"></span><span>Mode praktikum<br><strong>Web Programming</strong></span></div>
        <div class="sidebar-footer">PHP 8 · MySQL · PDO<br>Semester 5 Project</div>
    </aside>
    <main class="main-content">
        <header class="topbar"><div><div class="eyebrow">SISTEM INFORMASI KAMPUS</div><h1>Inventaris Kampus</h1><p class="page-subtitle">Kelola barang laboratorium, perangkat, dan fasilitas dalam satu tempat.</p></div><div class="user-chip"><span class="avatar">A</span><span><strong>Administrator</strong><small>Local development</small></span></div></header>
        <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div><?php endif; ?>
        <section class="stats-grid" aria-label="Statistik inventaris">
            <article class="stat-card"><div class="stat-top"><span>Total jenis barang</span><span class="stat-icon icon-blue">▦</span></div><div class="stat-value"><?= number_format((int) $stats['total_barang']) ?></div><div class="stat-caption">Item terdaftar di database</div></article>
            <article class="stat-card"><div class="stat-top"><span>Total unit barang</span><span class="stat-icon icon-purple">▤</span></div><div class="stat-value"><?= number_format((int) $stats['total_unit']) ?></div><div class="stat-caption">Akumulasi seluruh jumlah unit</div></article>
            <article class="stat-card"><div class="stat-top"><span>Kondisi baik</span><span class="stat-icon icon-green">✓</span></div><div class="stat-value"><?= number_format((int) $stats['kondisi_baik']) ?></div><div class="stat-caption">Jenis barang dalam kondisi baik</div></article>
            <article class="stat-card"><div class="stat-top"><span>Perlu ditangani</span><span class="stat-icon icon-orange">!</span></div><div class="stat-value"><?= number_format((int) $stats['kondisi_rusak']) ?></div><div class="stat-caption">Jenis barang berstatus rusak</div></article>
        </section>
        <section class="workspace">
            <article class="panel form-panel">
                <div class="panel-heading"><div><span class="section-kicker"><?= $editItem ? 'PEMBARUAN DATA' : 'FORMULIR' ?></span><h2><?= $editItem ? 'Edit barang' : 'Tambah barang' ?></h2></div><span class="heading-symbol">＋</span></div>
                <form method="post" action="index.php" class="inventory-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($form['id']) ?>">
                    <label for="kode">Kode inventaris <span>*</span></label><input id="kode" name="kode" type="text" maxlength="30" placeholder="Contoh: LAB-KOM-001" value="<?= e($form['kode']) ?>" required>
                    <label for="nama_barang">Nama barang <span>*</span></label><input id="nama_barang" name="nama_barang" type="text" maxlength="150" placeholder="Nama perangkat atau fasilitas" value="<?= e($form['nama_barang']) ?>" required>
                    <div class="field-row"><div><label for="kategori">Kategori</label><select id="kategori" name="kategori" required><?php foreach ($allowedCategories as $category): ?><option value="<?= e($category) ?>" <?= $form['kategori'] === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select></div><div><label for="jumlah">Jumlah unit</label><input id="jumlah" name="jumlah" type="number" min="0" max="1000000" value="<?= e($form['jumlah']) ?>" required></div></div>
                    <label for="lokasi">Lokasi penyimpanan <span>*</span></label><input id="lokasi" name="lokasi" type="text" maxlength="100" placeholder="Contoh: Lab Komputer 1" value="<?= e($form['lokasi']) ?>" required>
                    <label for="kondisi">Kondisi barang</label><select id="kondisi" name="kondisi" required><?php foreach ($allowedConditions as $condition): ?><option value="<?= e($condition) ?>" <?= $form['kondisi'] === $condition ? 'selected' : '' ?>><?= e($condition) ?></option><?php endforeach; ?></select>
                    <button class="btn btn-primary btn-full" type="submit"><?= $editItem ? 'Simpan perubahan' : '＋ Tambahkan barang' ?></button>
                    <?php if ($editItem): ?><a href="index.php" class="cancel-link">Batalkan edit</a><?php else: ?><p class="form-hint">Kolom bertanda <span>*</span> wajib diisi.</p><?php endif; ?>
                </form>
            </article>
            <article class="panel table-panel">
                <div class="panel-heading table-heading"><div><span class="section-kicker">DATABASE BARANG</span><h2>Daftar inventaris <span class="count-pill"><?= count($items) ?></span></h2></div><span class="table-caption">Data tersimpan di MySQL</span></div>
                <form method="get" action="index.php" class="filter-bar">
                    <div class="search-box"><span>⌕</span><input type="search" name="q" value="<?= e($search) ?>" placeholder="Cari kode, nama, kategori, lokasi…" aria-label="Cari inventaris"></div>
                    <select name="kategori" aria-label="Filter kategori"><option value="">Semua kategori</option><?php foreach ($allowedCategories as $category): ?><option value="<?= e($category) ?>" <?= $filterCategory === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select>
                    <select name="kondisi" aria-label="Filter kondisi"><option value="">Semua kondisi</option><?php foreach ($allowedConditions as $condition): ?><option value="<?= e($condition) ?>" <?= $filterCondition === $condition ? 'selected' : '' ?>><?= e($condition) ?></option><?php endforeach; ?></select>
                    <button class="btn btn-filter" type="submit">Filter</button>
                    <?php if ($search !== '' || $filterCategory !== '' || $filterCondition !== ''): ?><a class="reset-filter" href="index.php">Reset</a><?php endif; ?>
                </form>
                <div class="table-wrap"><table><thead><tr><th>BARANG</th><th>KATEGORI</th><th>LOKASI</th><th>JUMLAH</th><th>KONDISI</th><th>AKSI</th></tr></thead><tbody>
                <?php foreach ($items as $item): ?>
                    <tr><td><div class="item-cell"><span class="item-avatar"><?= e(mb_strtoupper(mb_substr($item['nama_barang'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span><div><strong><?= e($item['nama_barang']) ?></strong><small><?= e($item['kode']) ?></small></div></div></td><td><span class="category-label"><?= e($item['kategori']) ?></span></td><td class="location-cell"><span>⌖</span><?= e($item['lokasi']) ?></td><td><span class="quantity-pill"><?= number_format((int) $item['jumlah']) ?> unit</span></td><td><span class="status status-<?= $item['kondisi'] === 'Baik' ? 'good' : ($item['kondisi'] === 'Rusak' ? 'bad' : 'repair') ?>"><span></span><?= e($item['kondisi']) ?></span></td><td><div class="actions"><a class="action-edit" href="?edit=<?= (int) $item['id'] ?>" aria-label="Edit <?= e($item['nama_barang']) ?>" title="Edit">✎</a><form method="post" action="index.php" onsubmit="return confirm('Hapus barang <?= e($item['nama_barang']) ?>? Tindakan ini tidak dapat dibatalkan.');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><button class="action-delete" type="submit" aria-label="Hapus <?= e($item['nama_barang']) ?>" title="Hapus">×</button></form></div></td></tr>
                <?php endforeach; ?>
                <?php if (!$items): ?><tr><td colspan="6"><div class="empty-state"><span>⌕</span><strong>Data tidak ditemukan</strong><p>Ubah kata kunci atau filter, atau tambahkan barang melalui formulir.</p></div></td></tr><?php endif; ?>
                </tbody></table></div>
                <div class="table-footer"><span>Menampilkan <strong><?= count($items) ?></strong> dari inventaris yang sesuai filter</span><span class="secure-note">● Data tersimpan di database lokal</span></div>
            </article>
        </section>
        <footer class="page-footer"><span>© <?= date('Y') ?> InventarisKu · Proyek MK Web Programming Semester 5</span><span>Dibangun dengan PHP & MySQL</span></footer>
    </main>
</div>
</body>
</html>