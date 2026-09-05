<?php

$menu_items = [
    'dashboard'  => ['label' => 'Dashboard',  'url' => base_url('pages/dashboard/menuadmin.php')],
    'event'      => ['label' => 'Event',      'url' => base_url('event.php')],
    'peserta'    => ['label' => 'Peserta',    'url' => base_url('pages/peserta/index.php')],
    'wishlist'   => ['label' => 'Wishlist',   'url' => base_url('pages/wishlist/index.php')],
    'timeline'   => ['label' => 'Timeline',   'url' => base_url('pages/timeline/index.php')],
    'pengundian' => ['label' => 'Pengundian', 'url' => '#'],
    'pemberi'    => ['label' => 'Pemberi',    'url' => '#'],
    'penerima'   => ['label' => 'Penerima',   'url' => '#'],
    'laporan'    => ['label' => 'Laporan',    'url' => '#'],
    'pengaturan' => ['label' => 'Pengaturan', 'url' => '#'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($page_title ?? 'SKARIGA Secret Santa') ?> - SKARIGA Secret Santa</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style_admin.css') ?>">
</head>
<body>

    <div class="dashboard-container">
        <aside>
            <div class="brand">
                <h2>SKARIGA SECRET SANTA</h2>
            </div>
            <nav>
                <ul>
                    <?php foreach ($menu_items as $key => $item): ?>
                        <li class="<?= ($active_menu ?? '') === $key ? 'active' : '' ?>">
                            <a href="<?= $item['url'] ?>"><?= h($item['label']) ?></a>
                        </li>
                    <?php endforeach; ?>
                    <li><a href="<?= base_url('logout.php') ?>">Keluar</a></li>
                </ul>
            </nav>
        </aside>

        <main>
            <header class="page-header">
                <h2><?= h($page_title ?? '') ?></h2>
                <p><?= h($page_subtitle ?? '') ?></p>
            </header>

            <?php render_flash(); ?>