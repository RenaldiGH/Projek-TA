<?php
/**
 * Latar halaman autentikasi (login, register, lupa password, reset password):
 * video salju, lapisan gelap, dan bar atas. Dipakai bersama agar tampilannya sama.
 * Tombol di pojok kanan atas bisa diganti lewat $auth_tombol = ['Teks', 'file.php'] sebelum include.
 */
$auth_tombol = $auth_tombol ?? ['Login', 'login.php'];
?>
<video class="login-video" autoplay muted loop playsinline preload="auto"
       poster="<?= base_url('assets/img/pohonnatal.png') ?>">
    <source src="<?= base_url('assets/video/login-bg.mp4') ?>" type="video/mp4">
</video>
<div class="login-overlay"></div>

<header class="login-topbar">
    <a href="<?= base_url() ?>" class="login-brand">
        <span>SKARIGA</span>
        <strong>SECRET SANTA</strong>
    </a>
</header>
