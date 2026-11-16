
<?php
// Layout bersama untuk semua halaman peserta.

$user_menu = [
    'dashboard' => [
        'label' => 'Dashboard',
        'url' => base_url('pages/dashboard/dashboarduser.php'),
        'icon' => 'dashboard'
    ],
    'profil' => [
        'label' => 'Profil',
        'url' => base_url('pages/profil/profiluser.php'),
        'icon' => 'user'
    ],
    'wishlist' => [
        'label' => 'Wishlist',
        'url' => base_url('pages/wishlist/saya.php'),
        'icon' => 'gift'
    ],
    'timeline' => [
        'label' => 'Timeline',
        'url' => base_url('pages/timeline/saya.php'),
        'icon' => 'calendar'
    ],
    'undian' => [
        'label' => 'Hasil Undian',
        'url' => base_url('pages/hasilundian/hasil_undian.php'),
        'icon' => 'shuffle'
    ],
    'pengaturan' => [
        'label' => 'Pengaturan',
        'url' => base_url('pages/pengaturan/peserta.php'),
        'icon' => 'settings'
    ],
];

$nama_user = $_SESSION['nama'] ?? 'Peserta';
$inisial = strtoupper(mb_substr($nama_user, 0, 1));

$active_menu = $active_menu ?? '';

// Fungsi untuk menampilkan ikon Font Awesome
function user_menu_icon($icon) {
    $icons = [
        'dashboard' => 'fa-house',
        'user'      => 'fa-user',
        'gift'      => 'fa-gift',
        'calendar'  => 'fa-calendar-days',
        'shuffle'   => 'fa-shuffle',
        'settings'  => 'fa-gear',
        'logout'    => 'fa-right-from-bracket'
    ];

    $nama_icon = $icons[$icon] ?? '';

    return '<i class="fa-solid ' . $nama_icon . ' user-icon" aria-hidden="true"></i>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= h($page_title ?? 'Secret Santa') ?> - SKARIGA Secret Santa</title>

    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- CSS User -->
    <link rel="stylesheet" href="<?= base_url('assets/css/user.css') ?>">
</head>

<body>

<div class="user-layout">

    <aside class="user-sidebar">

        <div class="user-logo">
            <h2>SKARIGA<br>SECRET SANTA</h2>
        </div>

        <nav class="user-menu">

            <div class="user-menu-group">

                <span class="user-menu-heading">MAIN</span>

                <?php foreach (['dashboard', 'profil', 'wishlist', 'timeline', 'undian'] as $key): ?>
                    <?php $item = $user_menu[$key]; ?>

                    <a href="<?= $item['url'] ?>"
                       class="user-menu-link <?= $active_menu === $key ? 'active' : '' ?>">

                        <?= user_menu_icon($item['icon']) ?>

                        <span><?= h($item['label']) ?></span>

                    </a>

                <?php endforeach; ?>

            </div>

            <div class="user-menu-group">

                <span class="user-menu-heading">TOOLS</span>

                <?php $item = $user_menu['pengaturan']; ?>

                <a href="<?= $item['url'] ?>"
                   class="user-menu-link <?= $active_menu === 'pengaturan' ? 'active' : '' ?>">

                    <?= user_menu_icon($item['icon']) ?>

                    <span><?= h($item['label']) ?></span>

                </a>

            </div>

        </nav>

        <div class="user-sidebar-bottom">

            <div class="user-sidebar-divider"></div>

            <span class="user-menu-heading">ACCOUNT</span>

            <a href="<?= base_url('logout.php') ?>" class="user-menu-link">
                <?= user_menu_icon('logout') ?>
                <span>Keluar</span>
            </a>

        </div>

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
