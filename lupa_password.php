<?php
require_once __DIR__ . '/config/config.php';

$error = '';
$info = '';
$link = '';
$email = '';

// Di localhost (XAMPP) tidak ada server email, jadi tautan ditampilkan di layar.
// Di server sungguhan tautan dikirim lewat email.
$mode_lokal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Masukkan email yang valid.';

    } else {

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {

            // Hapus token lama milik user ini, lalu buat token baru (berlaku 1 jam)
            $uid = (int) $user['id'];
            $del = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $del->bind_param("i", $uid);
            $del->execute();
            $del->close();

            $token = bin2hex(random_bytes(32));
            $hash = hash('sha256', $token);
            $expires = date('Y-m-d H:i:s', time() + 3600);

            $ins = $conn->prepare(
                "INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)"
            );
            $ins->bind_param("iss", $uid, $hash, $expires);
            $ins->execute();
            $ins->close();

            $url = base_url('reset_password.php?token=' . $token);

            if ($mode_lokal) {
                $link = $url;
            } else {
                @mail(
                    $email,
                    'Reset Password SKARIGA Secret Santa',
                    "Klik tautan berikut untuk membuat password baru (berlaku 1 jam):\n\n" . $url
                    . "\n\nAbaikan email ini bila Anda tidak memintanya.",
                    "From: no-reply@skariga.local"
                );
            }
        }

        // Pesan sama baik email terdaftar maupun tidak
        $info = 'Jika email terdaftar, tautan untuk membuat password baru sudah dibuat.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password</title>
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
        <h1>Lupa Password</h1>
        <p class="login-note">Masukkan email akunmu. Kami buatkan tautan untuk membuat password baru.</p>

        <?php if ($error !== ''): ?>
            <div class="login-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($info !== ''): ?>
            <div class="login-ok"><?= htmlspecialchars($info) ?></div>
        <?php endif; ?>

        <?php if ($link !== ''): ?>
            <div class="login-linkbox">
                <strong>Mode lokal (tanpa server email):</strong>
                <a href="<?= htmlspecialchars($link) ?>">Buat password baru</a><br>
                Tautan berlaku 1 jam dan hanya bisa dipakai sekali.
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('lupa_password.php') ?>">
            <div class="login-field">
                <input type="email" name="email" placeholder="Email"
                       value="<?= htmlspecialchars($email) ?>" required>
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zm9 7L4.5 7v.1L12 13l7.5-5.9V7z"/></svg>
            </div>

            <button type="submit" class="login-submit">Kirim Tautan</button>
        </form>

        <p class="login-foot"><a href="<?= base_url('login.php') ?>">Kembali ke login</a></p>
    </div>
</div>
</body>
</html>
