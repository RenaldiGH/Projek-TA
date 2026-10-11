<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$my_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, nama, email, role FROM users WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    set_flash('error', 'User tidak ditemukan.');
    header('Location: ' . base_url('pages/user/index.php'));
    exit;
}

$is_self = $id === $my_id;

$error = '';
$nama = $user['nama'];
$email = $user['email'];
$role = $user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $is_self ? $user['role'] : ($_POST['role'] ?? $user['role']);

    $error = validasi_user($conn, $nama, $email, $password, $role, $id, false);

    // Minimal harus ada satu admin
    if ($error === '' && $user['role'] === 'admin' && $role !== 'admin') {
        $total_admin = (int) $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'")->fetch_assoc()['total'];

        if ($total_admin <= 1) {
            $error = 'Role admin terakhir tidak boleh diubah.';
        }
    }

    if ($error === '') {
        try {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare(
                    "UPDATE users SET nama = ?, email = ?, role = ?, password = ? WHERE id = ?"
                );
                $stmt->bind_param('ssssi', $nama, $email, $role, $hash, $id);
            } else {
                $stmt = $conn->prepare(
                    "UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?"
                );
                $stmt->bind_param('sssi', $nama, $email, $role, $id);
            }

            $stmt->execute();
            $stmt->close();

            // Samakan data di tabel peserta
            $stmt = $conn->prepare("UPDATE peserta SET nama = ?, email = ? WHERE user_id = ?");
            $stmt->bind_param('ssi', $nama, $email, $id);
            $stmt->execute();
            $stmt->close();

            if ($is_self) {
                $_SESSION['nama'] = $nama;
                $_SESSION['email'] = $email;
            }

            set_flash('success', 'Data user berhasil diperbarui.');
            header('Location: ' . base_url('pages/user/index.php'));
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = 'Gagal memperbarui user. Email mungkin sudah dipakai.';
        }
    }
}

$page_title = 'Edit User';
$page_subtitle = 'Perbarui data akun pengguna';
$active_menu = 'user';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" value="<?= h($nama) ?>" maxlength="100" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= h($email) ?>" maxlength="100" required>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" <?= $is_self ? 'disabled' : '' ?>>
                <option value="peserta" <?= $role === 'peserta' ? 'selected' : '' ?>>Peserta</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <?php if ($is_self): ?>
                <small style="color:#666;">Role akunmu sendiri tidak bisa diubah.</small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" id="password" name="password" minlength="6" maxlength="72" placeholder="Kosongkan kalau tidak diganti">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
            <a href="<?= base_url('pages/user/index.php') ?>" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>