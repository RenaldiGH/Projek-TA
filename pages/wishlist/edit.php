<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$event_id = (int) ($_GET['event_id'] ?? $_POST['event_id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM wishlist WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$wishlist = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$wishlist) {
    header('Location: ' . base_url('pages/wishlist/index.php?event_id=' . $event_id));
    exit;
}

$error = '';
$nama_barang = $wishlist['nama_barang'];
$kategori = $wishlist['kategori'];
$estimasi_harga = $wishlist['estimasi_harga'];
$deskripsi = $wishlist['deskripsi'];
$link_referensi = $wishlist['link_referensi'];
$status = $wishlist['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $estimasi_harga = trim($_POST['estimasi_harga'] ?? '0');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $link_referensi = trim($_POST['link_referensi'] ?? '');
    $status = $_POST['status'] ?? 'belum_dipilih';

    if ($nama_barang === '') {
        $error = 'Nama barang wajib diisi.';
    } elseif (!is_numeric($estimasi_harga) || $estimasi_harga < 0) {
        $error = 'Estimasi harga harus berupa angka.';
    } elseif (!in_array($status, ['belum_dipilih', 'dipilih'], true)) {
        $error = 'Status tidak valid.';
    } else {
        $update = $conn->prepare(
            "UPDATE wishlist
             SET nama_barang = ?, kategori = ?, estimasi_harga = ?, deskripsi = ?, link_referensi = ?, status = ?
             WHERE id = ?"
        );
        $update->bind_param(
            'ssdsssi',
            $nama_barang,
            $kategori,
            $estimasi_harga,
            $deskripsi,
            $link_referensi,
            $status,
            $id
        );

        if ($update->execute()) {
            set_flash('success', 'Wishlist berhasil diperbarui.');
            header('Location: ' . base_url('pages/wishlist/index.php?event_id=' . $event_id));
            exit;
        } else {
            $error = 'Gagal memperbarui wishlist.';
        }

        $update->close();
    }
}

$page_title = 'Edit Wishlist';
$page_subtitle = 'Perbarui data wishlist';
$active_menu = 'wishlist';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="event_id" value="<?= $event_id ?>">

        <div class="form-group">
            <label for="nama_barang">Nama Barang</label>
            <input type="text" id="nama_barang" name="nama_barang" value="<?= h($nama_barang) ?>" required>
        </div>

        <div class="form-group">
            <label for="kategori">Kategori</label>
            <input type="text" id="kategori" name="kategori" value="<?= h($kategori) ?>">
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

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="belum_dipilih" <?= $status === 'belum_dipilih' ? 'selected' : '' ?>>Belum Dipilih</option>
                <option value="dipilih" <?= $status === 'dipilih' ? 'selected' : '' ?>>Dipilih</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
            <a href="<?= base_url('pages/wishlist/index.php?event_id=' . $event_id) ?>" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>