<?php
// Layout bersama untuk semua halaman peserta.
// Variabel yang dipakai: $page_title, $page_subtitle, $active_menu

$user_menu = [
    'dashboard' => ['label' => 'Dashboard',    'url' => base_url('pages/dashboard/dashboarduser.php')],
    'profil'    => ['label' => 'Profil',       'url' => base_url('pages/profil/profiluser.php')],
    'wishlist'  => ['label' => 'Wishlist',     'url' => base_url('pages/wishlist/saya.php')],
    'timeline'  => ['label' => 'Timeline',     'url' => base_url('pages/timeline/saya.php')],
    'undian'    => ['label' => 'Hasil Undian', 'url' => '#'],
    'pengaturan'=> ['label' => 'Pengaturan',   'url' => base_url('pages/pengaturan/peserta.php')],
];

$nama_user = $_SESSION['nama'] ?? 'Peserta';
$inisial   = strtoupper(mb_substr($nama_user, 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($page_title ?? 'Secret Santa') ?> - SKARIGA Secret Santa</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/user.css') ?>">
</head>
<body>

<div class="user-layout">

    <aside class="user-sidebar">
        <div class="user-logo">
            <h2>SECRET<br>SANTA</h2>
        </div>

        <nav class="user-menu">
            <?php foreach ($user_menu as $key => $item): ?>
                <a href="<?= $item['url'] ?>" class="<?= ($active_menu ?? '') === $key ? 'active' : '' ?>">
                    <?= h($item['label']) ?>
                </a>
            <?php endforeach; ?>
            <a href="<?= base_url('logout.php') ?>">Keluar</a>
        </nav>
    </aside>

    <main class="user-content">

        <header class="user-header">
            <div>
                <h1><?= h($page_title ?? '') ?></h1>
                <p><?= h($page_subtitle ?? '') ?></p>
            </div>

            <a href="<?= base_url('pages/profil/profiluser.php') ?>" class="user-chip">
                <span class="user-chip-avatar"><?= h($inisial) ?></span>
                <span><?= h($nama_user) ?></span>
            </a>
        </header>

        <?php render_flash(); ?>