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
$tema = $event['tema'] ?? '';
$aturan_kado = $event['aturan_kado'] ?? '';
$budget = $event['budget'];
$biaya_pendaftaran = $event['biaya_pendaftaran'] ?? '0';
$tanggal_event = $event['tanggal_event'];
$status = $event['status'];
$gambar_lama = $event['gambar'] ?? '';
$gambar_url = event_gambar_url($gambar_lama);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_event = trim($_POST['nama_event'] ?? '');
    $deskripsi = bersihkan_html_rich(trim($_POST['deskripsi'] ?? ''));
    $tema = trim($_POST['tema'] ?? '');
    $aturan_kado = bersihkan_html_rich(trim($_POST['aturan_kado'] ?? ''));
    $budget = trim($_POST['budget'] ?? '0');
    $biaya_pendaftaran = trim($_POST['biaya_pendaftaran'] ?? '');
if ($biaya_pendaftaran === '') {
    $biaya_pendaftaran = '0';
}
    $tanggal_event = trim($_POST['tanggal_event'] ?? '');
    $status_baru = $_POST['status'] ?? 'draft';

    if ($nama_event === '' || $tanggal_event === '') {
        $error = 'Nama event dan tanggal wajib diisi.';
    } elseif (!is_numeric($budget) || $budget < 0) {
        $error = 'Budget harus berupa angka dan tidak boleh minus.';
        } elseif (!is_numeric($biaya_pendaftaran) || $biaya_pendaftaran < 0) {
    $error = 'Biaya pendaftaran harus berupa angka dan tidak boleh minus.';
    } elseif (!in_array($status_baru, ['draft', 'aktif', 'selesai'], true)) {
        $error = 'Status tidak valid.';
    } elseif ($status === 'selesai' && $status_baru !== 'selesai') {
        $error = 'Event yang sudah selesai tidak bisa dikembalikan statusnya.';
    } elseif (mb_strlen($nama_event) > 150) {
        $error = 'Nama event maksimal 150 karakter.';
    } elseif (!($d = DateTime::createFromFormat('Y-m-d', $tanggal_event)) || $d->format('Y-m-d') !== $tanggal_event) {
        $error = 'Format tanggal event tidak valid.';
    } elseif (data_sudah_ada($conn, "SELECT id FROM events WHERE nama_event = ? AND id <> ?", 'si', $nama_event, $id)) {
        $error = 'Event dengan nama "' . $nama_event . '" sudah ada. Gunakan nama lain.';
    } else {
        $status = $status_baru;

        // Foto event: ganti atau hapus (opsional)
        $gambar_baru    = $gambar_lama;
        $gambar_diganti = false;

        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $err_foto = '';
            $hasil_foto = simpan_gambar_event($_FILES['gambar'], $err_foto);

            if ($hasil_foto === null) {
                $error = $err_foto;
            } else {
                $gambar_baru    = $hasil_foto;
                $gambar_diganti = true;
            }
        } elseif (!empty($_POST['hapus_gambar'])) {
            $gambar_baru    = '';
            $gambar_diganti = true;
        }

        if ($error === '') {
            $gambar_db = $gambar_baru !== '' ? $gambar_baru : null;

            $update = $conn->prepare(
                "UPDATE events SET nama_event = ?, deskripsi = ?, tema = ?, aturan_kado = ?, gambar = ?, budget = ?, biaya_pendaftaran = ?, tanggal_event = ?, status = ? WHERE id = ?"
            );
            $update->bind_param('sssssddssi', $nama_event, $deskripsi, $tema, $aturan_kado, $gambar_db, $budget, $biaya_pendaftaran, $tanggal_event, $status, $id);

            if ($update->execute()) {
                if ($gambar_diganti) {
                    hapus_gambar_event($gambar_lama);
                }
                set_flash('success', 'Event berhasil diperbarui.');
                header('Location: ' . base_url('pages/event/event.php'));
                exit;
            }

            $error = 'Gagal memperbarui event.';
            $update->close();

            if ($gambar_diganti && $gambar_baru !== '') {
                hapus_gambar_event($gambar_baru);
            }
        }
    }
}

$page_title = 'Edit Event';
$page_subtitle = 'Perbarui data event';
$active_menu = 'event';


require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>
<link rel="stylesheet" href="<?= base_url('assets/css/rich-editor.css') ?>">
<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="form-group">
            <label for="nama_event">Nama Event</label>
            <input type="text" id="nama_event" name="nama_event" value="<?= h($nama_event) ?>" required>
        </div>

        <div class="form-group">
            <label for="foto">Foto Event</label>
            <img id="fotoPreview" alt="Foto event" <?php if (!empty($gambar_url)): ?>src="<?= h($gambar_url) ?>"<?php endif; ?>
                 style="display:<?= !empty($gambar_url) ? 'block' : 'none' ?>; width:100%; max-height:170px; object-fit:cover; border-radius:10px; margin-bottom:8px;">
            <input type="file" id="foto" name="gambar" accept="image/jpeg,image/png,image/webp">
            <small style="color:#888;">JPG, PNG, atau WEBP, maksimal 2 MB. Kosongkan jika tidak ingin mengubah foto.</small>
            <?php if (!empty($gambar_url)): ?>
                <label style="display:flex; align-items:center; gap:6px; margin-top:8px; font-weight:400;">
                    <input type="checkbox" name="hapus_gambar" value="1"> Hapus foto (kembali ke gambar bawaan)
                </label>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
           <textarea id="deskripsi" name="deskripsi" rows="3" class="rich-field"><?= h(tampilkan_rich($deskripsi)) ?></textarea>
        </div>

        <div class="form-group">
            <label for="tema">Tema Kado</label>
            <input type="text" id="tema" name="tema" value="<?= h($tema) ?>" maxlength="150" placeholder="Contoh: Alat tulis lucu, Cozy night, Jajanan daerah">
        </div>

        <div class="form-group">
            <label for="aturan_kado">Aturan Kado</label>
            <textarea id="aturan_kado" name="aturan_kado" rows="3" class="rich-field"><?= h(tampilkan_rich($aturan_kado)) ?></textarea>
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
<script src="<?= base_url('assets/js/rich-editor.js') ?>"></script>
<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>