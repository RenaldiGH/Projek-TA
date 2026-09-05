<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$event_id = (int) ($_GET['event_id'] ?? $_POST['event_id'] ?? 0);

if (!$event_id) {
    header('Location: ' . base_url('pages/wishlist/index.php'));
    exit;
}

// Ambil daftar peserta di event ini buat pilihan dropdown "Nama Peserta"
$peserta_stmt = $conn->prepare("SELECT id, nama FROM peserta WHERE event_id = ? ORDER BY nama ASC");
$peserta_stmt->bind_param('i', $event_id);
$peserta_stmt->execute();
$peserta_result = $peserta_stmt->get_result();
$peserta_options = [];
while ($row = $peserta_result->fetch_assoc()) {
    $peserta_options[] = $row;
}
$peserta_stmt->close();

$error = '';
$peserta_id = '';
$nama_barang = '';
$kategori = '';
$estimasi_harga = '';
$deskripsi = '';
$link_referensi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $peserta_id = (int) ($_POST['peserta_id'] ?? 0);
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $estimasi_harga = trim($_POST['estimasi_harga'] ?? '0');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $link_referensi = trim($_POST['link_referensi'] ?? '');

    if (!$peserta_id || $nama_barang === '') {
        $error = 'Peserta dan nama barang wajib diisi.';
    } elseif (!is_numeric($estimasi_harga) || $estimasi_harga < 0) {
        $error = 'Estimasi harga harus berupa angka.';
    } else {
        $insert = $conn->prepare(
            "INSERT INTO wishlist (peserta_id, nama_barang, kategori, estimasi_harga, deskripsi, link_referensi)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $insert->bind_param('issdss', $peserta_id, $nama_barang, $kategori, $estimasi_harga, $deskripsi, $link_referensi);

        if ($insert->execute()) {
            set_flash('success', 'Wishlist berhasil ditambahkan.');
            header('Location: ' . base_url('pages/wishlist/index.php?event_id=' . $event_id));
            exit;
        } else {
            $error = 'Gagal menyimpan wishlist.';
        }

        $insert->close();
    }
}

$page_title = 'Tambah Wishlist';
$page_subtitle = 'Tambahkan item wishlist untuk peserta';
$active_menu = 'wishlist';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endif; ?>

<?php if (!$peserta_options): ?>
    <div class="table-card">
        <div class="empty-state">
            <h3>Belum Ada Peserta</h3>
            <p>Tambahkan peserta ke event ini terlebih dahulu sebelum mengisi wishlist.</p>
        </div>
    </div>
<?php else: ?>
    <div class="form-card">
        <form method="POST">
            <input type="hidden" name="event_id" value="<?= $event_id ?>">

            <div class="form-group">
                <label for="peserta_id">Nama Peserta</label>
                <select id="peserta_id" name="peserta_id" required>
                    <option value="">-- Pilih Peserta --</option>
                    <?php foreach ($peserta_options as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= (int) $peserta_id === (int) $p['id'] ? 'selected' : '' ?>>
                            <?= h($p['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="nama_barang">Nama Barang</label>
                <input type="text" id="nama_barang" name="nama_barang" value="<?= h($nama_barang) ?>" required>
            </div>

            <div class="form-group">
                <label for="kategori">Kategori</label>
                <input type="text" id="kategori" name="kategori" value="<?= h($kategori) ?>" placeholder="Beauty, Lifestyle, dsb.">
            </div>

            <div class="form-group">
                <label for="estimasi_harga">Estimasi Harga (Rp)</label>
                <input type="number" id="estimasi_harga" name="estimasi_harga" value="<?= h($estimasi_harga) ?>" min="0" step="1000">
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="3"><?= h($deskripsi) ?></textarea>
            </div>

            <div class="form-group">
                <label for="link_referensi">Link Referensi (opsional)</label>
                <input type="text" id="link_referensi" name="link_referensi" value="<?= h($link_referensi) ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Simpan Wishlist</button>
                <a href="<?= base_url('pages/wishlist/index.php?event_id=' . $event_id) ?>" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>