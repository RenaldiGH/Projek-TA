
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

    // 1. Cek data kosong
    if ($nama === '' || $email === '' || $password === '' || $konfirmasi === '') {

        $error = 'Semua data wajib diisi.';

    // 2. Cek format email
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Email tidak valid.';

    // 3. Cek panjang password
    } elseif (strlen($password) < 6) {

        $error = 'Password minimal 6 karakter.';

    // 4. Cek konfirmasi password
    } elseif ($password !== $konfirmasi) {

        $error = 'Konfirmasi password tidak sama.';

    } else {

        // 5. Cek apakah email sudah terdaftar
        $stmt = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $error = 'Email sudah terdaftar.';

        } else {

            // 6. Hash password
            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // 7. Masukkan data ke database
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
</head>

<body>

<h2>Register</h2>

<?php if ($error !== ''): ?>
    <p><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success !== ''): ?>
    <p><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="POST">

    <input
        type="text"
        name="nama"
        placeholder="Nama Lengkap"
        required
    >

    <br><br>

    <input
        type="email"
        name="email"
        placeholder="Email"
        required
    >

    <br><br>

    <input
        type="password"
        name="password"
        placeholder="Password"
        required
    >

    <br><br>

    <input
        type="password"
        name="konfirmasi"
        placeholder="Konfirmasi Password"
        required
    >

    <br><br>

    <button type="submit">Register</button>

</form>

<br>

<a href="login.php">Login</a>

</body>
</html>

