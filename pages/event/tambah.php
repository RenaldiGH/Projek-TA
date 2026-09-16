<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$error = '';
$nama_event = '';
$deskripsi = '';
$budget = '';
$tanggal_event = '';
$status = 'draft';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_event = trim($_POST['nama_event'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $budget = trim($_POST['budget'] ?? '0');
    $tanggal_event = trim($_POST['tanggal_event'] ?? '');
    $status = $_POST['status'] ?? 'draft';

    if ($nama_event === '' || $tanggal_event === '') {
        $error = 'Nama event dan tanggal wajib diisi.';
    } elseif (!is_numeric($budget) || $budget < 0) {
        $error = 'Budget harus berupa angka dan tidak boleh minus.';
    } elseif (!in_array($status, ['draft', 'aktif', 'selesai'], true)) {
        $error = 'Status tidak valid.';
    } else {
        $user_id = (int) $_SESSION['user_id'];

        $insert = $conn->prepare(
            "INSERT INTO events (user_id, nama_event, deskripsi, budget, tanggal_event, status)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $insert->bind_param('issdss', $user_id, $nama_event, $deskripsi, $budget, $tanggal_event, $status);

        if ($insert->execute()) {
            set_flash('success', 'Event berhasil ditambahkan.');
            header('Location: ' . base_url('pages/event/event.php'));
            exit;
        } else {
            $error = 'Gagal menyimpan event.';
        }

        $insert->close();
    }
}

$page_title = 'Tambah Event';
$page_subtitle = 'Buat event Secret Santa baru';
$active_menu = 'event';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST">
        <div class="form-group">
            <label for="nama_event">Nama Event</label>
            <input type="text" id="nama_event" name="nama_event" value="<?= h($nama_event) ?>" required>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="3"><?= h($deskripsi) ?></textarea>
        </div>

        <div class="form-group">
            <label for="budget">Budget (Rp)</label>
            <input type="number" id="budget" name="budget" value="<?= h($budget) ?>" min="0" step="1000">
        </div>

        <div class="form-group">
            <label for="tanggal_event">Tanggal Event</label>
            <input type="date" id="tanggal_event" name="tanggal_event" value="<?= h($tanggal_event) ?>" required>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Simpan Event</button>
            <a href="<?= base_url('pages/event/event.php') ?>" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>