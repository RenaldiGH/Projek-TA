<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$event_id = (int) ($_GET['event_id'] ?? $_POST['event_id'] ?? 0);

if (!$event_id) {
    header('Location: ' . base_url('pages/peserta/index.php'));
    exit;
}

$error = '';
$nama = '';
$email = '';
$no_hp = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');

    if ($nama === '' || $email === '') {
        $error = 'Nama dan email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        // Cek apakah peserta dengan email ini sudah terdaftar di event yang sama
        $cek = $conn->prepare("SELECT id FROM peserta WHERE event_id = ? AND email = ?");
        $cek->bind_param('is', $event_id, $email);
        $cek->execute();

        if ($cek->get_result()->num_rows > 0) {
            $error = 'Peserta dengan email ini sudah terdaftar di event ini.';
        } else {
            $cek->close();

            // Setiap baris peserta wajib terhubung ke tabel users (foreign key).
            // Kalau emailnya belum punya akun user, buatkan otomatis dengan role peserta,
            // supaya nantinya peserta itu bisa login pakai email ini + password default.
            $user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $user_stmt->bind_param('s', $email);
            $user_stmt->execute();
            $user_row = $user_stmt->get_result()->fetch_assoc();
            $user_stmt->close();

            if ($user_row) {
                $user_id = (int) $user_row['id'];
            } else {
                $default_password = password_hash('peserta123', PASSWORD_DEFAULT);
                $insert_user = $conn->prepare(
                    "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'peserta')"
                );
                $insert_user->bind_param('sss', $nama, $email, $default_password);
                $insert_user->execute();
                $user_id = $insert_user->insert_id;
                $insert_user->close();
            }

            $insert = $conn->prepare(
                "INSERT INTO peserta (event_id, user_id, nama, email, no_hp, status)
                 VALUES (?, ?, ?, ?, ?, 'aktif')"
            );
            $insert->bind_param('iisss', $event_id, $user_id, $nama, $email, $no_hp);

            if ($insert->execute()) {
                set_flash('success', 'Peserta berhasil ditambahkan.');
                header('Location: ' . base_url('pages/peserta/index.php?event_id=' . $event_id));
                exit;
            } else {
                $error = 'Gagal menyimpan data peserta.';
            }

            $insert->close();
        }
    }
}

$page_title = 'Tambah Peserta';
$page_subtitle = 'Tambahkan peserta baru ke dalam event';
$active_menu = 'peserta';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST">
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

        <p style="font-size:12px; color:#666;">
            Kalau email ini belum pernah dipakai, sistem otomatis membuatkan akun peserta
            dengan password default <strong>peserta123</strong>.
        </p>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Simpan Peserta</button>
            <a href="<?= base_url('pages/peserta/index.php?event_id=' . $event_id) ?>" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>