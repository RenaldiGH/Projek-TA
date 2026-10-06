<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

// Buat tabel pengaturan otomatis kalau belum ada
$conn->query(
    "CREATE TABLE IF NOT EXISTS pengaturan (
        id INT PRIMARY KEY,
        nama_aplikasi VARCHAR(100) NOT NULL,
        min_peserta INT NOT NULL DEFAULT 3,
        max_peserta INT NOT NULL DEFAULT 10
    )"
);
$conn->query(
    "INSERT IGNORE INTO pengaturan (id, nama_aplikasi, min_peserta, max_peserta)
     VALUES (1, 'SKARIGA Secret Santa', 3, 10)"
);

// Tab aktif
$tab_valid = ['umum', 'notifikasi', 'email', 'backup'];
$tab = $_GET['tab'] ?? 'umum';
if (!in_array($tab, $tab_valid, true)) {
    $tab = 'umum';
}

$error = '';
$success = '';

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_aplikasi = trim($_POST['nama_aplikasi'] ?? '');
    $min_peserta   = (int) ($_POST['min_peserta'] ?? 0);
    $max_peserta   = (int) ($_POST['max_peserta'] ?? 0);

    if ($nama_aplikasi === '') {
        $error = 'Nama aplikasi wajib diisi.';
    } elseif ($min_peserta < 2) {
        $error = 'Jumlah minimal peserta tidak boleh kurang dari 2.';
    } elseif ($max_peserta < $min_peserta) {
        $error = 'Maksimal peserta harus lebih besar atau sama dengan minimal peserta.';
    } else {
        $stmt = $conn->prepare(
            "UPDATE pengaturan
             SET nama_aplikasi = ?, min_peserta = ?, max_peserta = ?
             WHERE id = 1"
        );
        $stmt->bind_param('sii', $nama_aplikasi, $min_peserta, $max_peserta);
        $stmt->execute();
        $stmt->close();

        $success = 'Pengaturan berhasil disimpan.';
    }
}

// Ambil data pengaturan
$stmt = $conn->prepare(
    "SELECT nama_aplikasi, min_peserta, max_peserta
     FROM pengaturan
     WHERE id = 1"
);
$stmt->execute();
$pengaturan = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Kalau validasi gagal, tampilkan kembali input terakhir
if ($error !== '') {
    $pengaturan['nama_aplikasi'] = $nama_aplikasi;
    $pengaturan['min_peserta']   = $min_peserta;
    $pengaturan['max_peserta']   = $max_peserta;
}

$page_title = 'Pengaturan Sistem';
$page_subtitle = 'Atur konfigurasi sistem';
$active_menu = 'pengaturan';

// Style input dipakai ulang agar kode lebih ringkas
$style_label = 'display:block; font-size:12px; margin:0 0 8px 8px;';
$style_input = 'display:block; width:100%; box-sizing:border-box; height:42px; padding:0 16px; font-size:14px; border:1px solid #cfcfcf; border-radius:14px; background:#fff;';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<div class="table-card">

    <!-- Tab -->
    <div style="display:flex; border-bottom:1px solid #d9d9d9; margin-bottom:22px;">
        <?php
        $daftar_tab = ['umum' => 'Umum', 'notifikasi' => 'Notifikasi', 'email' => 'Email', 'backup' => 'Backup'];
        foreach ($daftar_tab as $key => $label):
        ?>
            <a href="?tab=<?= $key ?>"
               style="flex:1; text-align:center; padding:10px 0 12px; font-size:12px; color:#000; text-decoration:none; font-weight:<?= $tab === $key ? '600' : '400' ?>;">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($error): ?>
        <p style="color:#a12626; font-size:13px; margin:0 0 16px;"><?= h($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color:#1b6b32; font-size:13px; margin:0 0 16px;"><?= h($success) ?></p>
    <?php endif; ?>

    <?php if ($tab === 'umum'): ?>

        <form method="POST" action="?tab=umum">

            <div style="margin-bottom:20px;">
                <label for="nama_aplikasi" style="<?= $style_label ?>">Nama Aplikasi</label>
                <input type="text" name="nama_aplikasi" id="nama_aplikasi"
                       style="<?= $style_input ?>"
                       value="<?= h($pengaturan['nama_aplikasi']) ?>" required>
            </div>

            <div style="margin-bottom:20px;">
                <label for="min_peserta" style="<?= $style_label ?>">Jumlah minimal peserta per event</label>
                <input type="number" name="min_peserta" id="min_peserta"
                       style="<?= $style_input ?>"
                       value="<?= (int) $pengaturan['min_peserta'] ?>" min="2" required>
            </div>

            <div style="margin-bottom:20px;">
                <label for="max_peserta" style="<?= $style_label ?>">Maksimal peserta per event</label>
                <input type="number" name="max_peserta" id="max_peserta"
                       style="<?= $style_input ?>"
                       value="<?= (int) $pengaturan['max_peserta'] ?>" min="2" required>
            </div>

            <div style="display:flex; justify-content:center; margin-top:40px;">
                <button type="submit"
                        style="width:257px; height:40px; background:#1a0066; color:#fff; font-size:17px; font-weight:600; border:none; border-radius:8px; cursor:pointer;">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    <?php elseif ($tab === 'notifikasi'): ?>

        <div class="empty-state">
            <h3>Pengaturan Notifikasi</h3>
            <p>Belum ada pengaturan notifikasi.</p>
        </div>

    <?php elseif ($tab === 'email'): ?>

        <div class="empty-state">
            <h3>Pengaturan Email</h3>
            <p>Belum ada pengaturan email.</p>
        </div>

    <?php elseif ($tab === 'backup'): ?>

        <div class="empty-state">
            <h3>Backup Data</h3>
            <p>Belum ada pengaturan backup.</p>
        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>