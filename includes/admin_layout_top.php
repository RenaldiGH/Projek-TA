
<?php

$event_query = (!empty($event_id)) ? ('?event_id=' . (int) $event_id) : '';

$menu_items = [
    'dashboard' => [
        'label' => 'Dashboard',
        'url' => base_url('pages/dashboard/menuadmin.php'),
        'icon' => 'dashboard',
        'group' => 'MAIN'
    ],

    'event' => [
        'label' => 'Event',
        'url' => base_url('pages/event/event.php' . $event_query),
        'icon' => 'calendar',
        'group' => 'MANAGEMENT'
    ],

    'peserta' => [
        'label' => 'Peserta',
        'url' => base_url('pages/peserta/index.php' . $event_query),
        'icon' => 'users',
        'group' => 'MANAGEMENT'
    ],

    'wishlist' => [
        'label' => 'Wishlist',
        'url' => base_url('pages/wishlist/index.php' . $event_query),
        'icon' => 'heart',
        'group' => 'MANAGEMENT'
    ],

    'timeline' => [
        'label' => 'Timeline',
        'url' => base_url('pages/timeline/index.php' . $event_query),
        'icon' => 'clock',
        'group' => 'MANAGEMENT'
    ],

    'pengundian' => [
        'label' => 'Pengundian',
        'url' => base_url('pages/pengundian/index.php' . $event_query),
        'icon' => 'shuffle',
        'group' => 'MANAGEMENT'
    ],

    'pemberi' => [
        'label' => 'Pemberi',
        'url' => base_url('pages/pemberi/index.php' . $event_query),
        'icon' => 'gift',
        'group' => 'MANAGEMENT'
    ],

    'penerima' => [
        'label' => 'Penerima',
        'url' => base_url('pages/penerima/index.php' . $event_query),
        'icon' => 'user-check',
        'group' => 'MANAGEMENT'
    ],

    'laporan' => [
        'label' => 'Laporan',
        'url' => base_url('pages/laporan/index.php' . $event_query),
        'icon' => 'file',
        'group' => 'TOOLS'
    ],

    'riwayat' => [
        'label' => 'Riwayat',
        'url' => base_url('pages/riwayat/index.php' . $event_query),
        'icon' => 'history',
        'group' => 'TOOLS'
    ],

    'user' => [
        'label' => 'User',
        'url' => base_url('pages/user/index.php' . $event_query),
        'icon' => 'user',
        'group' => 'TOOLS'
    ],

    'pengaturan' => [
        'label' => 'Pengaturan',
        'url' => base_url('pages/adminpengaturan/index.php' . $event_query),
        'icon' => 'settings',
        'group' => 'TOOLS'
    ],
];

$active_menu = $active_menu ?? '';


// FUNGSI IKON FONT AWESOME
function admin_menu_icon($icon) {

    $icons = [
        'dashboard'  => 'fa-house',
        'calendar'   => 'fa-calendar-days',
        'users'      => 'fa-users',
        'heart'      => 'fa-heart',
        'clock'      => 'fa-clock',
        'shuffle'    => 'fa-shuffle',
        'gift'       => 'fa-gift',
        'user-check' => 'fa-user-check',
        'file'       => 'fa-file-lines',
        'history'    => 'fa-clock-rotate-left',
        'user'       => 'fa-user',
        'settings'   => 'fa-gear',
        'logout'     => 'fa-right-from-bracket'
    ];

    $nama_icon = $icons[$icon] ?? '';

    return '<i class="fa-solid ' . $nama_icon . ' admin-icon" aria-hidden="true"></i>';
}


// MENGELOMPOKKAN MENU
$grouped_menu = [];

foreach ($menu_items as $key => $item) {
    $grouped_menu[$item['group']][$key] = $item;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= h($page_title ?? 'Secret Santa') ?> - SKARIGA Secret Santa</title>

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- CSS ADMIN -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style_admin.css') ?>">
</head>

<body>

<div class="dashboard-container">

    <!-- SIDEBAR ADMIN -->
    <aside class="admin-sidebar">

        <div class="brand">
            <h2>SKARIGA SECRET SANTA</h2>
        </div>

        <nav class="admin-menu">

            <?php foreach ($grouped_menu as $group_name => $items): ?>

                <div class="admin-menu-group">

                    <span class="admin-menu-heading">
                        <?= h($group_name) ?>
                    </span>

                    <ul>

                        <?php foreach ($items as $key => $item): ?>

                            <li class="<?= $active_menu === $key ? 'active' : '' ?>">

                                <a href="<?= $item['url'] ?>">

                                    <?= admin_menu_icon($item['icon']) ?>

                                    <span><?= h($item['label']) ?></span>

                                </a>

                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endforeach; ?>

        </nav>

        <!-- TOMBOL KELUAR -->
        <div class="admin-sidebar-bottom">

            <div class="admin-sidebar-divider"></div>

            <a href="<?= base_url('logout.php') ?>">

                <?= admin_menu_icon('logout') ?>

                <span>Keluar</span>

            </a>

        </div>

    </aside>

    <!-- KONTEN UTAMA -->
    <main>

        <header class="page-header">

            <h2><?= h($page_title ?? '') ?></h2>

            <p><?= h($page_subtitle ?? '') ?></p>

        </header>

        <?php render_flash(); ?>
