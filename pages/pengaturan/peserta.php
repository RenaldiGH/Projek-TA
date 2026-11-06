<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_peserta();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// >>> SESUAIKAN jika nama tabel/kolom di database berbeda
$T_USERS    = 'users';
$C_USER_ID  = 'id';
$C_PASSWORD = 'password';

// Set true saat development agar pesan error asli tampil. Ubah ke false di server asli.
$debug = true;

$user_id = (int) ($_SESSION['user_id'] ?? 0);

$error = '';
$success = '';

// Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_lama       = $_POST['password_lama'] ?? '';
    $password_baru       = $_POST['password_baru'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
    $csrf                = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = 'Sesi tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } elseif ($user_id <= 0) {
        $error = 'Pengguna tidak ditemukan. Silakan login kembali.';
    } elseif ($password_lama === '' || $password_baru === '' || $konfirmasi_password === '') {
        $error = 'Semua password harus diisi.';
    } elseif (strlen($password_baru) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } elseif ($password_baru !== $konfirmasi_password) {
        $error = 'Konfirmasi password baru tidak sama.';
    } elseif ($password_baru === $password_lama) {
        $error = 'Password baru tidak boleh sama dengan password lama.';
    } else {
        try {
            // Ambil password saat ini
            $stmt = $conn->prepare(
                "SELECT $C_PASSWORD FROM $T_USERS WHERE $C_USER_ID = ? LIMIT 1"
            );
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->bind_result($password_db);
            $ada = $stmt->fetch();
            $stmt->close();

            if (!$ada) {
                throw new Exception('Data pengguna tidak ditemukan.');
            }

            // Cek password lama (mendukung password ter-hash maupun teks biasa)
            $sudah_hash = password_get_info($password_db)['algo'] !== null;

            $cocok = $sudah_hash
                ? password_verify($password_lama, $password_db)
                : hash_equals((string) $password_db, $password_lama);

            if (!$cocok) {
                $error = 'Password lama salah.';
            } else {
                // Format penyimpanan mengikuti yang dipakai login
                $password_simpan = $sudah_hash
                    ? password_hash($password_baru, PASSWORD_DEFAULT)
                    : $password_baru;

                $stmt = $conn->prepare(
                    "UPDATE $T_USERS SET $C_PASSWORD = ? WHERE $C_USER_ID = ?"
                );
                $stmt->bind_param('si', $password_simpan, $user_id);
                $stmt->execute();
                $stmt->close();

                $success = 'Password berhasil diubah.';
            }
        } catch (Throwable $e) {
            error_log('Ubah Password Error: ' . $e->getMessage());
            $error = $debug
                ? 'Error: ' . $e->getMessage()
                : 'Password belum dapat diubah. Silakan coba lagi.';
        }
    }
}

$page_title = 'Pengaturan Akun';
$page_subtitle = 'Atur keamanan akun kamu';
$active_menu = 'pengaturan';

// Style input dipakai ulang agar kode lebih ringkas
$style_label = 'display:block; font-size:12px; margin:0 0 8px 8px;';
$style_input = 'display:block; width:100%; box-sizing:border-box; height:42px; padding:0 48px 0 16px; font-size:14px; border:1px solid #cfcfcf; border-radius:14px; background:#fff;';
$style_toggle = 'position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:16px; padding:4px;';

// >>> SESUAIKAN: nama file layout user (samakan dengan halaman peserta lain)
require_once __DIR__ . '/../../includes/user_layout_top.php';
?>

<div class="table-card">

    <h3 style="margin:0 0 22px; font-size:16px;">Ubah Password</h3>

    <?php if ($error): ?>
        <p style="color:#a12626; font-size:13px; margin:0 0 16px;"><?= h($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color:#1b6b32; font-size:13px; margin:0 0 16px;"><?= h($success) ?></p>
    <?php endif; ?>

    <form method="POST" action="" autocomplete="off">

        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">

        <?php
        $fields = [
            ['password_lama',       'Password Lama',            'current-password'],
            ['password_baru',       'Password Baru',            'new-password'],
            ['konfirmasi_password', 'Konfirmasi Password Baru', 'new-password'],
        ];
        foreach ($fields as [$name, $label, $autocomplete]):
        ?>
            <div style="margin-bottom:20px;">
                <label for="<?= $name ?>" style="<?= $style_label ?>"><?= $label ?></label>
                <div style="position:relative;">
                    <input type="password" name="<?= $name ?>" id="<?= $name ?>"
                           style="<?= $style_input ?>"
                           autocomplete="<?= $autocomplete ?>"
                           <?= $name !== 'password_lama' ? 'minlength="6"' : '' ?>
                           required>
                    <button type="button"
                            style="<?= $style_toggle ?>"
                            aria-label="Tampilkan atau sembunyikan password"
                            onclick="togglePassword('<?= $name ?>', this)">👁</button>
                </div>
            </div>
        <?php endforeach; ?>

        <div style="display:flex; justify-content:center; margin-top:40px;">
            <button type="submit"
                    style="width:257px; height:40px; background:#1a0066; color:#fff; font-size:17px; font-weight:600; border:none; border-radius:8px; cursor:pointer;">
                Simpan Password
            </button>
        </div>

    </form>

</div>

<script>
function togglePassword(id, button) {
    var input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = '🙈';
    } else {
        input.type = 'password';
        button.textContent = '👁';
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/user_layout_bottom.php'; ?>