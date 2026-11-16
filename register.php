
<?php

require_once __DIR__ . '/config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil data dari form
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    // Mengecek data kosong
    if ($nama === '' || $email === '' || $password === '' || $konfirmasi === '') {

        $error = 'Semua data wajib diisi.';

    //  Cek format email
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Email tidak valid.';

    //  Cek panjang password
    } elseif (strlen($password) < 6) {

        $error = 'Password minimal 6 karakter.';

    //  Cek konfirmasi password
    } elseif ($password !== $konfirmasi) {

        $error = 'Konfirmasi password tidak sama.';

    } else {

        //  Cek apakah email sudah terdaftar
        $stmt = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $error = 'Email sudah terdaftar.';

        } else {

            // Hash password
            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            //  Masukkan data ke database
            $stmt = $conn->prepare(
                "INSERT INTO users (nama, email, password, role)
                 VALUES (?, ?, ?, 'peserta')"
            );

            $stmt->bind_param(
                "sss",
                $nama,
                $email,
                $password_hash
            );

            if ($stmt->execute()) {

                $success = 'Register berhasil. Silakan login.';

            } else {

                $error = 'Register gagal.';
            }
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/login.css') ?>">
</head>
<body>
<div class="login-wrapper">

    <?php include __DIR__ . '/includes/auth_bg.php'; ?>

    <div class="login-card">
        <a href="<?= base_url() ?>" class="login-close" aria-label="Tutup">&times;</a>

        <div class="login-hat" aria-hidden="true">
            <svg viewBox="0 0 64 48" width="64" height="48">
                <path d="M6 40C8 18 26 6 46 8c-8 4-10 12-8 20z" fill="#d90032"/>
                <path d="M4 38c14 6 40 6 56 0l-2 8c-14 5-38 5-52 0z" fill="#fff"/>
                <circle cx="48" cy="8" r="6" fill="#fff"/>
            </svg>
        </div>
        <h1>Register</h1>
        <p class="login-greet">Buat akun baru dan ikut tukar kado!</p>

        <?php if ($error !== ''): ?>
            <div class="login-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="login-ok"><?= htmlspecialchars($success) ?> <a href="<?= base_url('login.php') ?>" style="color:#fff;font-weight:900;">Login</a></div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('register.php') ?>">
            <div class="login-field">
                <input type="text" name="nama" placeholder="Nama Lengkap"
                       value="<?= htmlspecialchars($success === '' ? ($nama ?? '') : '') ?>" required>
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4.4 0-8 2.2-8 5v1h16v-1c0-2.8-3.6-5-8-5z"/></svg>
            </div>

            <div class="login-field">
                <input type="email" name="email" placeholder="Email"
                       value="<?= htmlspecialchars($success === '' ? ($email ?? '') : '') ?>" required>
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zm9 7L4.5 7v.1L12 13l7.5-5.9V7z"/></svg>
            </div>

            <div class="login-field">
                <input type="password" name="password" id="pw1" placeholder="Password" required>
                <button type="button" class="login-eye" data-target="pw1" aria-label="Tampilkan password">
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 5C6 5 2 12 2 12s4 7 10 7 10-7 10-7-4-7-10-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg>
                </button>
            </div>

            <div class="login-field">
                <input type="password" name="konfirmasi" id="pw2" placeholder="Konfirmasi Password" required>
                <button type="button" class="login-eye" data-target="pw2" aria-label="Tampilkan password">
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 5C6 5 2 12 2 12s4 7 10 7 10-7 10-7-4-7-10-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg>
                </button>
            </div>

            <button type="submit" class="login-submit">Register</button>
        </form>

        <p class="login-foot">Sudah punya akun? <a href="<?= base_url('login.php') ?>">Login</a></p>
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
