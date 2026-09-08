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
    <title>Penerima (Peserta) - SKARIGA Secret Santa</title>
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
                    <li><a href="#">Pengundian</a></li>
                    <li><a href="#">Pemberi</a></li>
                    <li class="active"><a href="<?= base_url('pages/penerima/index.php') ?>">Penerima</a></li>
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
                    <h2>Penerima(Peserta)</h2>
                    <p>Daftar Peserta yang menerima hadiah</p>
                </div>
                <a href="#" class="btn-add">+ Mulai Pengundian</a>
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

                <!-- Data Table Header -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">No.</th>
                            <th>Nama Penerima</th>
                            <th style="text-align: right; padding-right: 40px;">Aksi</th>
                        </tr>
                    </thead>
                </table>

                <!-- Empty State (Belum Ada Hasil Pengundian) -->
                <div class="empty-state">
                    <svg class="empty-state-icon" viewBox="0 0 24 24" fill="currentColor" width="90" height="90">
                        <path d="M16.5 13c-1.2 0-3.07.34-4.5 1-1.43-.66-3.3-1-4.5-1C5.17 13 1 14.17 1 16.5V19h22v-2.5c0-2.33-4.17-3.5-7.5-3.5zM12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-4.5 0c1.93 0 3.5-1.57 3.5-3.5S9.43 5 7.5 5 4 6.57 4 8.5 5.57 12 7.5 12zm9 0c1.93 0 3.5-1.57 3.5-3.5S18.43 5 16.5 5 13 6.57 13 8.5s1.57 3.5 3.5 3.5z"/>
                    </svg>
                    <h3>Belum Ada Hasil Pengundian</h3>
                    <p>Lakukan pengundian terlebih dahulu</p>
                </div>
            </section>
        </main>
    </div>

</body>
</html>