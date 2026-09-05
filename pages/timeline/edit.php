<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$event_id = (int) ($_GET['event_id'] ?? $_POST['event_id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM timeline WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$timeline = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$timeline) {
    header('Location: ' . base_url('pages/timeline/index.php?event_id=' . $event_id));
    exit;
}

$error = '';
$judul = $timeline['judul'];
$tanggal = $timeline['tanggal'];
$waktu = $timeline['waktu'] ? substr($timeline['waktu'], 0, 5) : '';
$deskripsi = $timeline['deskripsi'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? '');
    $waktu = trim($_POST['waktu'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($judul === '') {
        $error = 'Judul wajib diisi.';
    } else {
        $tanggal_db = $tanggal !== '' ? $tanggal : null;
        $waktu_db = $waktu !== '' ? $waktu : null;

        $update = $conn->prepare(
            "UPDATE timeline SET judul = ?, tanggal = ?, waktu = ?, deskripsi = ? WHERE id = ?"
        );
        $update->bind_param('ssssi', $judul, $tanggal_db, $waktu_db, $deskripsi, $id);

        if ($update->execute()) {
            set_flash('success', 'Timeline berhasil diperbarui.');
            header('Location: ' . base_url('pages/timeline/index.php?event_id=' . $event_id));
            exit;
        } else {
            $error = 'Gagal memperbarui timeline.';
        }

        $update->close();
    }
}

$page_title = 'Edit Timeline';
$page_subtitle = 'Perbarui jadwal event';
$active_menu = 'timeline';

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
            <label for="judul">Judul</label>
            <input type="text" id="judul" name="judul" value="<?= h($judul) ?>" required>
        </div>

        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= h($tanggal) ?>">
        </div>

        <div class="form-group">
            <label for="waktu">Waktu</label>
            <input type="time" id="waktu" name="waktu" value="<?= h($waktu) ?>">
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="3"><?= h($deskripsi) ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
            <a href="<?= base_url('pages/timeline/index.php?event_id=' . $event_id) ?>" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>