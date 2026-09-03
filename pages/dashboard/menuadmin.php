<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

require_login();

if ($_SESSION['role'] !== 'admin') {
    header('Location: ' . base_url('pages/dashboard/index.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
   <link rel="stylesheet" href="../../assets/css/style_admin.css">
</head>
<body>

    <div class="dashboard-container">
        <!-- Sidebar / Navigasi Samping -->
        <aside>
            <div class="brand">
                <h2>SKARIGA SECRET SANTA</h2>
            </div>
            <nav>
                <ul>
                    <li class="active"><a href="#">Dashboard</a></li>
                    <li><a href="<?= base_url('event.php') ?>">Event</a></li>
                    <li><a href="#">Peserta</a></li>
                    <li><a href="#">Wishlist</a></li>
                    <li><a href="#">Timeline</a></li>
                    <li><a href="#">Pengundian</a></li>
                    <li><a href="#">Pemberi</a></li>
                    <li><a href="#">Penerima</a></li>
                    <li><a href="#">Laporan</a></li>
                    <li><a href="#">Pengaturan</a></li>
                    <li><a href="<?= base_url('logout.php') ?>">Keluar</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Konten Utama -->
        <main>
            <!-- Header / Profile Admin -->
            <header>
                <div class="header-title">
                    <h2>Dashboard Admin</h2>
                    <h3>Selamat datang, Admin!</h3>
                    <p>Kelola Sistem Secret Santa SKARIGA</p>
                </div>
                <div class="user-profile">
                    <div>
                        <strong>Admin</strong><br>
                        <small style="color: #666;">Administrator</small>
                    </div>
                    <div class="user-avatar"></div>
                </div>
            </header>

            <!-- Ringkasan Stat / Stat Card -->
            <section class="grid-4">
                <div class="card banner-card">
                    <h3>SKARIGA<br>SECRET<br>SANTA</h3>
                </div>
                <div class="card stat-card">
                    <p>Total Event</p>
                    <h3>1</h3>
                </div>
                <div class="card stat-card">
                    <p>Total Peserta</p>
                    <h3>120</h3>
                </div>
                <div class="card stat-card">
                    <p>Total Pengundian</p>
                    <h3>0</h3>
                </div>
                <div class="card stat-card">
                    <p>Timeline</p>
                    <h3>1</h3>
                </div>
            </section>

            <!-- Section Table Event & Pengundian -->
            <section class="grid-2">
                <!-- Event Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <h3>Event Terbaru</h3>
                        <a href="#" class="btn-all">Lihat Semua</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Event</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Secret Santa 2025</td>
                                <td>25 Des 2025</td>
                                <td class="badge-active">Aktif</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pengundian Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <h3>Pengundian Terbaru</h3>
                        <a href="#" class="btn-all">Lihat Semua</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Secret Santa 2025</td>
                                <td>05 Des 2025</td>
                                <td class="badge-done">Selesai</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Section Action & Log -->
            <section class="grid-2">
                <!-- Pengundian Cepat -->
                <div class="card quick-draw">
                    <h3>Pengundian Cepat</h3>
                    <p>Lakukan pengundian acak secara otomatis</p>
                    <form action="" method="POST">
                        <button type="submit" name="acak" class="btn-primary">Lakukan Pengacakan Otomatis</button>
                    </form>
                </div>

                <!-- Log Aktivitas Terbaru -->
                <div class="card">
                    <h3>Log aktivitas Terbaru</h3>
                    <ul class="log-list">
                        <li><span class="check-icon">✓</span> Pengundian Otomatis</li>
                        <li><span class="check-icon">✓</span> Peserta baru saja bergabung</li>
                    </ul>
                </div>
            </section>
        </main>
    </div>

</body>
</html>