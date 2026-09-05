
<?php

require_once __DIR__ . '/config/database.php';

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
    <title>Register</title>

    
    <link rel="stylesheet" href="assets/css/register.css">
</head>

<body>

    <div class="register-wrapper">

        <div class="register-container">

            
            <div class="register-image"></div>


            <div class="register-box">

                <p class="register-sub">
                    Buat Akun Baru
                </p>

                <h1>Register</h1>


                
                <?php if ($error !== ''): ?>
                    <p>
                        <?= htmlspecialchars($error) ?>
                    </p>
                <?php endif; ?>


                <?php if ($success !== ''): ?>
                    <p>
                        <?= htmlspecialchars($success) ?>
                    </p>
                <?php endif; ?>


                <form method="POST">

                    
                    <div class="input-group">
                        <input
                            type="text"
                            name="nama"
                            placeholder="Nama Lengkap"
                            required
                        >
                    </div>


                    <div class="input-group">
                        <input
                            type="email"
                            name="email"
                            placeholder="Email"
                            required
                        >
                    </div>


                    
                    <div class="input-group">
                        <input
                            type="password"
                            name="password"
                            placeholder="Password"
                            required
                        >
                    </div>


                    <div class="input-group">
                        <input
                            type="password"
                            name="konfirmasi"
                            placeholder="Konfirmasi Password"
                            required
                        >
                    </div>


                    <div class="input-register">
                        <button type="submit">
                            Register
                        </button>
                    </div>

                </form>

                <p class="register-link">
                    Sudah punya akun?
                    <a href="login.php">Login</a>
                </p>

            </div>

        </div>

    </div>

</body>
</html>