<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil data dari form
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Cek input kosong
    if ($email === '' || $password === '') {

        $error = 'Email dan password wajib diisi.';

    } else {

        // Cari user berdasarkan email
        $stmt = $conn->prepare(
            "SELECT id, nama, email, password, role
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // redirect berdasarkan role
            if ($user['role'] === 'admin') {

                header('Location: ' . base_url('pages/dashboard/menuadmin.php'));
                exit;

            } elseif ($user['role'] === 'peserta') {

                header('Location: ' . base_url('pages/dashboard/dashboarduser.php'));
                exit;

            } else {

                $error = 'Role tidak valid.';
            }

        } else {

            $error = 'Email atau password salah.';
        }

        $stmt->close();
    }
}
?>

<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/login.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/review.css') ?>">

<div class="login-wrapper">

    <?php $auth_tombol = ['Daftar', 'register.php']; include __DIR__ . '/includes/auth_bg.php'; ?>

    <!-- Kartu login (efek kaca) -->
    <div class="login-card">
        <a href="<?= base_url() ?>" class="login-close" aria-label="Tutup">&times;</a>

        <div class="login-hat" aria-hidden="true">
            <svg viewBox="0 0 64 48" width="64" height="48">
                <path d="M6 40C8 18 26 6 46 8c-8 4-10 12-8 20z" fill="#d90032"/>
                <path d="M4 38c14 6 40 6 56 0l-2 8c-14 5-38 5-52 0z" fill="#fff"/>
                <circle cx="48" cy="8" r="6" fill="#fff"/>
            </svg>
        </div>
        <h1>Login</h1>
        <p class="login-greet">Ho ho ho! Masuk untuk mulai tukar kado.</p>

        <?php if (isset($_GET['reset'])): ?>
            <div class="login-ok">Password berhasil diubah. Silakan login.</div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="login-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('login.php') ?>">
            <div class="login-field">
                <input type="email" name="email" placeholder="Email"
                       value="<?= htmlspecialchars($email ?? '') ?>" required>
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zm9 7L4.5 7v.1L12 13l7.5-5.9V7z"/></svg>
            </div>

            <div class="login-field">
                <input type="password" name="password" id="loginPassword" placeholder="Password" required>
                <button type="button" class="login-eye" id="togglePassword" aria-label="Tampilkan password">
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 5C6 5 2 12 2 12s4 7 10 7 10-7 10-7-4-7-10-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg>
                </button>
            </div>

            <div class="login-forgot"><a href="<?= base_url('lupa_password.php') ?>">Lupa password?</a></div>

            <button type="submit" class="login-submit">Login</button>
        </form>

        <p class="login-foot">Belum punya akun? <a href="<?= base_url('register.php') ?>">Daftar</a></p>
    </div>
</div>

<script>
    // Tampilkan / sembunyikan password
    document.getElementById('togglePassword').addEventListener('click', function () {
        var input = document.getElementById('loginPassword');
        input.type = input.type === 'password' ? 'text' : 'password';
    });
</script>
