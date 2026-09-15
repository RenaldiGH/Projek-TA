<?php


require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/header.php';



$nama = $_SESSION['nama'] ?? 'Nama Peserta';
$email = $_SESSION['email'] ?? 'peserta@email.com';
$role = $_SESSION['role'] ?? 'Peserta';
$tanggal_bergabung = $_SESSION['tanggal_bergabung'] ?? '15 September 2026';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_SESSION['nama'] = $_POST['nama'];
    $_SESSION['email'] = $_POST['email'];
    $_SESSION['role'] = $_POST['role'];
    $_SESSION['tanggal_bergabung'] = $_POST['tanggal_bergabung'];

    $nama = $_SESSION['nama'];
    $email = $_SESSION['email'];
    $role = $_SESSION['role'];
    $tanggal_bergabung = $_SESSION['tanggal_bergabung'];

    $berhasil = true;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profil Peserta - Secret Santa</title>

    <link rel="stylesheet"
          href="<?= base_url('assets/css/profiluser.css') ?>">

</head>


<body>


<div class="dashboard">


    <aside class="sidebar">


        <div class="logo">

            <h2>
                SECRET<br>
                SANTA
            </h2>

        </div>


        <nav class="menu">


            <a href="<?= base_url('pages/dashboard/dashboarduser.php') ?>">
                Dashboard
            </a>


            <a href="<?= base_url('pages/profil/profiluser.php') ?>"
               class="active">
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


            <a href="#">
                Pengaturan
            </a>


            <a href="<?= base_url('logout.php') ?>">
                Keluar
            </a>


        </nav>


    </aside>


    <main class="content">


        <div class="page-header">

            <div>

                <h1>
                    Profil Saya
                </h1>

                <p>
                    Kelola informasi akun Secret Santa kamu.
                </p>

            </div>

        </div>



        <?php if (isset($berhasil)): ?>

            <div class="success-message">
                Profil berhasil diperbarui!
            </div>

        <?php endif; ?>



        <form method="POST">


            <section class="profile-top">



                <div class="profile-card">


                    <div class="avatar">
                        P
                    </div>


                    <h2>
                        <?= htmlspecialchars($nama); ?>
                    </h2>


                    <p class="profile-email">
                        <?= htmlspecialchars($email); ?>
                    </p>


                    <div class="active-badge">
                         Akun Aktif
                    </div>


                </div>




                <div class="information-card">


                    <h2>
                        Informasi Profil
                    </h2>


                    <div class="form-group">

                        <label for="nama">
                            Nama Lengkap
                        </label>

                        <input
                            type="text"
                            id="nama"
                            name="nama"
                            value="<?= htmlspecialchars($nama); ?>"
                            placeholder="Masukkan nama lengkap"
                        >

                    </div>




                    <div class="form-group">

                        <label for="email">
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars($email); ?>"
                            placeholder="Masukkan email"
                        >

                    </div>




                    <div class="form-group">

                        <label for="role">
                            Role
                        </label>

                        <input
                            type="text"
                            id="role"
                            name="role"
                            value="<?= htmlspecialchars($role); ?>"
                            placeholder="Masukkan role"
                        >

                    </div>




                    <div class="form-group">

                        <label for="tanggal_bergabung">
                            Tanggal Bergabung
                        </label>

                        <input
                            type="text"
                            id="tanggal_bergabung"
                            name="tanggal_bergabung"
                            value="<?= htmlspecialchars($tanggal_bergabung); ?>"
                            placeholder="Masukkan tanggal bergabung"
                        >

                    </div>



                    <button type="submit" class="save-button">
                        Simpan Perubahan
                    </button>


                </div>


            </section>


        </form>


    </main>


</div>


</body>

</html>