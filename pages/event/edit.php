<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if (!$id) {
    header('Location: ' . base_url('pages/event/event.php'));
    exit;
}

$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    header('Location: ' . base_url('pages/event/event.php'));
    exit;
}

$error = '';
$nama_event = $event['nama_event'];
$deskripsi = $event['deskripsi'];
$budget = $event['budget'];
$tanggal_event = $event['tanggal_event'];
$status = $event['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_event = trim($_POST['nama_event'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $budget = trim($_POST['budget'] ?? '0');
    $tanggal_event = trim($_POST['tanggal_event'] ?? '');
    $status_baru = $_POST['status'] ?? 'draft';

    if ($nama_event === '' || $tanggal_event === '') {
        $error = 'Nama event dan tanggal wajib diisi.';
    } elseif (!is_numeric($budget) || $budget < 0) {
        $error = 'Budget harus berupa angka dan tidak boleh minus.';
    } elseif (!in_array($status_baru, ['draft', 'aktif', 'selesai'], true)) {
        $error = 'Status tidak valid.';
    } elseif ($status === 'selesai' && $status_baru !== 'selesai') {
        $error = 'Event yang sudah selesai tidak bisa dikembalikan statusnya.';
    } else {
        $status = $status_baru;

        $update = $conn->prepare(
            "UPDATE events SET nama_event = ?, deskripsi = ?, budget = ?, tanggal_event = ?, status = ? WHERE id = ?"
        );
        $update->bind_param('ssdssi', $nama_event, $deskripsi, $budget, $tanggal_event, $status, $id);

        if ($update->execute()) {
            set_flash('success', 'Event berhasil diperbarui.');
            header('Location: ' . base_url('pages/event/event.php'));
            exit;
        } else {
            $error = 'Gagal memperbarui event.';
        }

        $update->close();
    }
}

$page_title = 'Edit Event';
$page_subtitle = 'Perbarui data event';
$active_menu = 'event';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">

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
            <select id="status" name="status" <?= $status === 'selesai' ? 'disabled' : '' ?>>
                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
            </select>
            <?php if ($status === 'selesai'): ?>
                <input type="hidden" name="status" value="selesai">
                <small style="color:#888;">Event yang sudah selesai tidak bisa diubah statusnya.</small>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
            <a href="<?= base_url('pages/event/event.php') ?>" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>