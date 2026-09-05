<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

        // Cek email dan password
        if ($user && password_verify($password, $user['password'])) {

            // Buat session
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect berdasarkan role
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

<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/login.css">


<div class="login-wrapper">
    <div class="login-container">
        <div class="login-image"></div>
        
        <div class="login-box">
            <p class="login-sub">Buat Akun Baru</p>
            <h1>Login</h1>
            <form method="POST" action="<?= base_url('login.php') ?>">
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>

            

                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <div class="input-login">
                    <button type="submit">Login</button>
                    
                </div>
            </form>
        </div>
    </div>
</div>
