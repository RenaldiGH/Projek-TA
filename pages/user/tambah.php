<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$error = '';
$nama = '';
$email = '';
$role = 'peserta';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'peserta';

    $error = validasi_user($conn, $nama, $email, $password, $role);

    if ($error === '') {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param('ssss', $nama, $email, $hash, $role);
            $stmt->execute();
            $stmt->close();

            set_flash('success', 'User berhasil ditambahkan.');
            header('Location: ' . base_url('pages/user/index.php'));
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = 'Gagal menyimpan user. Email mungkin sudah dipakai.';
        }
    }
}

$page_title = 'Tambah User';
$page_subtitle = 'Buat akun pengguna baru';
$active_menu = 'user';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST">
        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" value="<?= h($nama) ?>" maxlength="100" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= h($email) ?>" maxlength="100" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" minlength="6" maxlength="72" required>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="peserta" <?= $role === 'peserta' ? 'selected' : '' ?>>Peserta</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Simpan User</button>
            <a href="<?= base_url('pages/user/index.php') ?>" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>