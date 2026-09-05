<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$event_id = (int) ($_GET['event_id'] ?? $_POST['event_id'] ?? 0);

if (!$id) {
    header('Location: ' . base_url('pages/peserta/index.php'));
    exit;
}

$stmt = $conn->prepare("SELECT * FROM peserta WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$peserta = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$peserta) {
    header('Location: ' . base_url('pages/peserta/index.php?event_id=' . $event_id));
    exit;
}

$error = '';
$nama = $peserta['nama'];
$email = $peserta['email'];
$no_hp = $peserta['no_hp'];
$status = $peserta['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $status = $_POST['status'] ?? 'aktif';

    if ($nama === '' || $email === '') {
        $error = 'Nama dan email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (!in_array($status, ['aktif', 'nonaktif'], true)) {
        $error = 'Status tidak valid.';
    } else {
        $update = $conn->prepare(
            "UPDATE peserta SET nama = ?, email = ?, no_hp = ?, status = ? WHERE id = ?"
        );
        $update->bind_param('ssssi', $nama, $email, $no_hp, $status, $id);

        if ($update->execute()) {
            set_flash('success', 'Data peserta berhasil diperbarui.');
            header('Location: ' . base_url('pages/peserta/index.php?event_id=' . $event_id));
            exit;
        } else {
            $error = 'Gagal memperbarui data peserta.';
        }

        $update->close();
    }
}

$page_title = 'Edit Peserta';
$page_subtitle = 'Perbarui data peserta';
$active_menu = 'peserta';

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
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" value="<?= h($nama) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= h($email) ?>" required>
        </div>

        <div class="form-group">
            <label for="no_hp">No. HP</label>
            <input type="text" id="no_hp" name="no_hp" value="<?= h($no_hp) ?>">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
            <a href="<?= base_url('pages/peserta/index.php?event_id=' . $event_id) ?>" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>