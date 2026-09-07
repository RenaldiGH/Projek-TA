<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/header.php';
?>

<link rel="stylesheet" href="css/dashboard-user.css">

<div class="dashboard">

   
    <div class="sidebar">

        <h2>SECRET<br>SANTA</h2>

        <li class="active"><a href="<?= base_url('pages/dashboard/dashboarduser.php') ?>">Dashboard</a></li>
        <a href="#">Profil</a>
        <a href="<?= base_url('pages/wishlist/index.php') ?>">Wishlist</a>
        <a href="">Timeline</a>
        <a href="#">Hasil Undian</a>
        <a href="">Pengaturan</a>
        <a href="<?= base_url('logout.php') ?>">Keluar</a>

    </div>


  
    <div class="content">

        <div class="header">

            <div>
                <h1>Halo, Peserta! 👋</h1>
                <p>Selamat datang di Secret Santa SKARIGA</p>
            </div>

            <div>
                🔔 &nbsp; 👤 <b>Peserta</b>
            </div>

        </div>


       
        <div class="cards">

            <div class="card">
                <h3>Status Saya</h3>
                <div class="icon">🎁</div>
                <b>Terdaftar</b>
                <p>Kamu sudah terdaftar di Secret Santa Skariga</p>
            </div>


            <div class="card">
                <h3>Wishlist Saya</h3>
                <div class="icon">❤️</div>
                <b>3 Barang</b>
            </div>


            <div class="card">
                <h3>Timeline Terdekat</h3>
                <div class="icon">📅</div>
                <p>11-16 Desember 2024</p>
                <p>Pengisian Wishlist</p>
            </div>


            <div class="card">
                <h3>Pengumuman Terbaru</h3>
                <div class="icon">📢</div>
                <p>Pengundian akan dilakukan pada 16 Desember 2024</p>
            </div>

        </div>


       
        <div class="pesan">
            Jangan lupa Siapkan Hadiah Terbaik Untuk Secret Santamu!
        </div>

    </div>

</div>