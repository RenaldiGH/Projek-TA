<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/header.php';

$pesan = '';
$tipe_pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';


    if (
        empty($password_lama) ||
        empty($password_baru) ||
        empty($konfirmasi_password)
    ) {

        $pesan = 'Semua password harus diisi.';
        $tipe_pesan = 'error';

    }


    elseif (strlen($password_baru) < 6) {

        $pesan = 'Password baru minimal 6 karakter.';
        $tipe_pesan = 'error';

    }

    elseif ($password_baru !== $konfirmasi_password) {

        $pesan = 'Konfirmasi password baru tidak sama.';
        $tipe_pesan = 'error';

    }

    else {

        $_SESSION['password'] = $password_baru;

        $pesan = 'Password berhasil diubah.';
        $tipe_pesan = 'success';
    }
}

?>


<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Pengaturan Akun - Secret Santa</title>

<link rel="stylesheet" href="../../assets/css/pengaturanpeserta.css">

</head>


<body>


<div class="dashboard">


    <aside class="sidebar">


        <div class="logo">

            <div class="logo-decoration">
                
            </div>

            <h2>
                SECRET<br>
                SANTA
            </h2>

        </div>

        <nav class="menu">


            <a href="<?= base_url('pages/dashboard/dashboarduser.php') ?>">
                Dashboard
            </a>


            <a href="<?= base_url('pages/profil/profiluser.php') ?>">
                Profil
            </a>


            <a href="<?= base_url('pages/wishlist/index.php') ?>">
                Wishlist
            </a>


            <a href="#">
                Timeline
            </a>


            <a href="#">
                Hasil Undian
            </a>


            <a href="<?= base_url('pages/pengaturan/pengaturan.php') ?>"
               class="active">
                Pengaturan
            </a>


            <a href="<?= base_url('logout.php') ?>">
                Keluar
            </a>


        </nav>

        <div class="christmas-decoration">
            
        </div>


    </aside>

    <main class="content">

        <div class="page-header">

            <h1>
                Pengaturan akun
            </h1>

        </div>



        <?php if (!empty($pesan)): ?>

            <div class="message <?= $tipe_pesan ?>">

                <?= htmlspecialchars($pesan); ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="password-card">


                <h2>
                    Ubah Password
                </h2>



                <div class="form-group">

                    <label for="password_lama">
                        Password Lama
                    </label>


                    <div class="password-box">


                        <span class="lock-icon">
                            
                        </span>


                        <input
                            type="password"
                            id="password_lama"
                            name="password_lama"
                            placeholder=""
                            required
                        >


                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword('password_lama', this)"
                        >
                            
                        </button>


                    </div>

                </div>

                <div class="form-group">

                    <label for="password_baru">
                        Password Baru
                    </label>


                    <div class="password-box">


                        <span class="lock-icon">
                            
                        </span>


                        <input
                            type="password"
                            id="password_baru"
                            name="password_baru"
                            required
                        >


                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword('password_baru', this)"
                        >
                            👁
                        </button>


                    </div>

                </div>

                <div class="form-group">

                    <label for="konfirmasi_password">
                        Konfirmasi Password Baru
                    </label>


                    <div class="password-box">


                        <span class="lock-icon">
                            
                        </span>


                        <input
                            type="password"
                            id="konfirmasi_password"
                            name="konfirmasi_password"
                            required
                        >


                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword('konfirmasi_password', this)"
                        >
                            👁
                        </button>


                    </div>

                </div>


            </div>


            <div class="button-area">

                <button
                    type="submit"
                    class="save-button"
                >
                    Simpan Password
                </button>

            </div>


        </form>


    </main>


</div>

<script>

function togglePassword(id, button) {

    const input = document.getElementById(id);

    if (input.type === "password") {

        input.type = "text";

        button.textContent = "";

    } else {

        input.type = "password";

        button.textContent = "👁";

    }

}

</script>


</body>

</html>