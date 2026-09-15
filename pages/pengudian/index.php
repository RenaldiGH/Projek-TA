<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengundian - SKARIGA Secret Santa</title>
    <link rel="stylesheet" href="../../assets/css/style_admin.css">
</head>
<body>

    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <aside>
            <div class="brand">
                <h2>SKARIGA SECRET SANTA</h2>
            </div>
            <nav>
                <ul>
                    <li><a href="<?= base_url('pages/dashboard/menuadmin.php') ?>">Dashboard</a></li>
                    <li><a href="<?= base_url('pages/event/event.php') ?>">Event</a></li>
                    <li><a href="<?= base_url('pages/peserta/index.php') ?>">Peserta</a></li>
                    <li><a href="<?= base_url('pages/wishlist/index.php') ?>">Wishlist</a></li>
                    <li><a href="<?= base_url('pages/timeline/index.php') ?>">Timeline</a></li>
                    <li class="active"><a href="<?= base_url('pages/pengundian/index.php') ?>">Pengundian</a></li>
                    <li><a href="#">Pemberi</a></li>
                    <li><a href="<?= base_url('pages/penerima/index.php') ?>">Penerima</a></li>
                    <li><a href="#">Laporan</a></li>
                    <li><a href="#">Pengaturan</a></li>
                    <li><a href="<?= base_url('logout.php') ?>">Keluar</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main>
            <!-- Page Header -->
            <header class="page-header">
                <div class="header-title">
                    <h2>Data Peserta</h2>
                    <p>Kelola Semua Event Secreet Santa Skariga</p>
                </div>
                <a href="#" class="btn-add" style="background-color: #2b0054;">+ Mulai Pengundian</a>
            </header>

            <!-- Card Container -->
            <section class="table-card">
                <!-- Dropdown Select Event -->
                <div class="filter-bar">
                    <div class="form-group">
                        <label for="event">Pilih Event</label>
                        <select id="event" class="select-input">
                            <option value="">Sistem Secreet Santa SKARIGA 2025</option>
                        </select>
                    </div>
                </div>

                <!-- Empty State (Belum Ada Pengundian) -->
                <div class="empty-state">
                    <!-- Icon Dadu sesuai Figma -->
                    <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="90" height="90" style="color: #000000; margin-bottom: 16px;">
                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"></circle>
                        <circle cx="15.5" cy="8.5" r="1.5" fill="currentColor"></circle>
                        <circle cx="12" cy="12" r="1.5" fill="currentColor"></circle>
                        <circle cx="8.5" cy="15.5" r="1.5" fill="currentColor"></circle>
                        <circle cx="15.5" cy="15.5" r="1.5" fill="currentColor"></circle>
                    </svg>
                    <h3>Belum Ada Pengundian</h3>
                    <p>Klik "Mulai Pengundian" Untuk melakukan Undian Otomatis</p>
                </div>
            </section>
        </main>
    </div>

</body>
</html>