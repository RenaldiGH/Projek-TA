<?php
require_once __DIR__ . '/config/config.php';

$error = '';
$valid = false;
$reset = null;

$token = $_POST['token'] ?? $_GET['token'] ?? '';
$token = is_string($token) ? trim($token) : '';

if ($token !== '' && preg_match('/^[a-f0-9]{64}$/', $token)) {

    $hash = hash('sha256', $token);

    $stmt = $conn->prepare(
        "SELECT id, user_id, expires_at FROM password_resets WHERE token_hash = ? AND used = 0"
    );
    $stmt->bind_param("s", $hash);
    $stmt->execute();
    $reset = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($reset && strtotime($reset['expires_at']) > time()) {
        $valid = true;
    }
}

if ($valid && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($password === '' || $konfirmasi === '') {

        $error = 'Semua data wajib diisi.';

    } elseif (strlen($password) < 6) {

        $error = 'Password minimal 6 karakter.';

    } elseif ($password !== $konfirmasi) {

        $error = 'Konfirmasi password tidak sama.';

    } else {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $uid = (int) $reset['user_id'];

        $conn->begin_transaction();

        try {
            $up = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $up->bind_param("si", $password_hash, $uid);
            $up->execute();
            $up->close();

            // Token dipakai sekali, hapus semua token milik user ini
            $del = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $del->bind_param("i", $uid);
            $del->execute();
            $del->close();

            $conn->commit();

            header('Location: ' . base_url('login.php?reset=1'));
            exit;

        } catch (Throwable $e) {
            $conn->rollback();
            $error = 'Gagal mengubah password. Coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/login.css') ?>">
</head>
<body>
<div class="login-wrapper">

    <?php include __DIR__ . '/includes/auth_bg.php'; ?>

    <div class="login-card">
        <a href="<?= base_url('login.php') ?>" class="login-close" aria-label="Tutup">&times;</a>

        <div class="login-hat" aria-hidden="true">
            <svg viewBox="0 0 64 48" width="64" height="48">
                <path d="M6 40C8 18 26 6 46 8c-8 4-10 12-8 20z" fill="#d90032"/>
                <path d="M4 38c14 6 40 6 56 0l-2 8c-14 5-38 5-52 0z" fill="#fff"/>
                <circle cx="48" cy="8" r="6" fill="#fff"/>
            </svg>
        </div>
        <h1>Password Baru</h1>

        <?php if (!$valid): ?>

            <div class="login-error">Tautan tidak valid atau sudah kedaluwarsa.</div>
            <p class="login-foot"><a href="<?= base_url('lupa_password.php') ?>">Minta tautan baru</a></p>

        <?php else: ?>

            <p class="login-note">Buat password baru untuk akunmu (minimal 6 karakter).</p>

            <?php if ($error !== ''): ?>
                <div class="login-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= base_url('reset_password.php') ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="login-field">
                    <input type="password" name="password" id="pw1" placeholder="Password baru" required>
                    <button type="button" class="login-eye" data-target="pw1" aria-label="Tampilkan password">
                        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 5C6 5 2 12 2 12s4 7 10 7 10-7 10-7-4-7-10-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg>
                    </button>
                </div>

                <div class="login-field">
                    <input type="password" name="konfirmasi" id="pw2" placeholder="Konfirmasi password" required>
                    <button type="button" class="login-eye" data-target="pw2" aria-label="Tampilkan password">
                        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 5C6 5 2 12 2 12s4 7 10 7 10-7 10-7-4-7-10-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg>
                    </button>
                </div>

                <button type="submit" class="login-submit">Simpan Password</button>
            </form>

        <?php endif; ?>
    </div>
</div>

<script>
    document.querySelectorAll('.login-eye').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.target);
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
</script>
</body>
</html>
