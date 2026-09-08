<?php 
require_once __DIR__ . '/../../config/config.php'; 
require_once __DIR__ . '/../../includes/header.php'; 
?>

<link rel="stylesheet" href="../../assets/css/dashboarduser.css">

<div class="dashboard">


    <aside class="sidebar">

    
        <div class="logo">
            <h2>
                SECRET<br>
                SANTA
            </h2>
        </div>

    
        <nav class="menu">

            <a href="<?= base_url('pages/dashboard/dashboarduser.php') ?>" class="active">
                Dashboard
            </a>

            <a href="#">
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

        <header class="header">

            <div class="welcome">

                <h1>
                    Halo, Peserta!
                </h1>

                <p>
                    Selamat datang di Secret Santa SKARIGA
                </p>

            </div>


            <div class="profile">

              

                <span>
                    Peserta
                </span>

            </div>

        </header>


        <section class="cards">


    
            <div class="card status-card">

                <h3>
                    Status Saya
                </h3>



                <strong>
                    Terdaftar
                </strong>

                <p>
                    Kamu sudah terdaftar di
                    Secret Santa Skariga
                </p>

            </div>


            <div class="card wishlist-card">

                <h3>
                    Wishlist Saya
                </h3>



                <strong>
                    3 Barang
                </strong>

            </div>

            <div class="card timeline-card">

                <h3>
                    Timeline Terdekat
                </h3>

    

                <p>
                    11-16 Desember 2024
                </p>

                <p>
                    Pengisian Wishlist
                </p>

            </div>

            <div class="card announcement-card">

                <h3>
                    Pengumuman Terbaru
                </h3>

    

                <p>
                    Pengundian akan dilakukan
                    pada 16 Desember 2024
                </p>

            </div>

        </section>


        <div class="pesan">


            <p>
                Jangan lupa Siapkan Hadiah Terbaik Untuk Secret Santamu!
            </p>

        </div>

    </main>

</div> 