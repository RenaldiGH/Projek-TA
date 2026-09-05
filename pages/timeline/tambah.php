<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$event_id = (int) ($_GET['event_id'] ?? $_POST['event_id'] ?? 0);

if (!$event_id) {
    header('Location: ' . base_url('pages/timeline/index.php'));
    exit;
}

$error = '';
$judul = '';
$tanggal = '';
$waktu = '';
$deskripsi = '';

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

        $insert = $conn->prepare(
            "INSERT INTO timeline (event_id, judul, tanggal, waktu, deskripsi)
             VALUES (?, ?, ?, ?, ?)"
        );
        $insert->bind_param('issss', $event_id, $judul, $tanggal_db, $waktu_db, $deskripsi);

        if ($insert->execute()) {
            set_flash('success', 'Timeline berhasil ditambahkan.');
            header('Location: ' . base_url('pages/timeline/index.php?event_id=' . $event_id));
            exit;
        } else {
            $error = 'Gagal menyimpan timeline.';
        }

        $insert->close();
    }
}

$page_title = 'Tambah Timeline';
$page_subtitle = 'Tambahkan jadwal baru untuk event';
$active_menu = 'timeline';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST">
        <input type="hidden" name="event_id" value="<?= $event_id ?>">

        <div class="form-group">
            <label for="judul">Judul</label>
            <input type="text" id="judul" name="judul" value="<?= h($judul) ?>" placeholder="Batas Pendaftaran, Pengundian, dsb." required>
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
            <button type="submit" class="btn-primary">Simpan Timeline</button>
            <a href="<?= base_url('pages/timeline/index.php?event_id=' . $event_id) ?>" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>