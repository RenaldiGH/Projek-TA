<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_login();


$total_event = (int) $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc()['total'];
$total_peserta = (int) $conn->query("SELECT COUNT(*) AS total FROM peserta")->fetch_assoc()['total'];
$total_pengundian = (int) $conn->query("SELECT COUNT(*) AS total FROM pengundian")->fetch_assoc()['total'];
$total_timeline = (int) $conn->query("SELECT COUNT(*) AS total FROM timeline")->fetch_assoc()['total'];

// Event Terbaru (5 baris terakhir)
$event_terbaru = $conn->query(
    "SELECT nama_event, tanggal_event, status
     FROM events
     ORDER BY created_at DESC
     LIMIT 5"
);

// Pengundian Terbaru (5 baris terakhir, join biar tau nama event-nya)
$pengundian_terbaru = $conn->query(
    "SELECT e.nama_event, p.tanggal_undi, p.status
     FROM pengundian p
     JOIN events e ON e.id = p.event_id
     ORDER BY p.tanggal_undi DESC
     LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style_admin.css') ?>">
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
                    <li class="active"><a href="<?= base_url('pages/dashboard/menuadmin.php') ?>">Dashboard</a></li>
                    <li><a href="<?= base_url('event.php') ?>">Event</a></li>
                    <li><a href="<?= base_url('pages/peserta/index.php') ?>">Peserta</a></li>
                    <li><a href="<?= base_url('pages/wishlist/index.php') ?>">Wishlist</a></li>
                    <li><a href="<?= base_url('pages/timeline/index.php') ?>">Timeline</a></li>
                    <li><a href="#">Pengundian</a></li>
                    <li><a href="#">Pemberi</a></li>
                    <li><a href="#">Penerima</a></li>
                    <li><a href="#">Laporan</a></li>
                    <li><a href="#">Pengaturan</a></li>
                    <li><a href="<?= base_url('logout.php') ?>">Keluar</a></li>
                </ul>
            </nav>
        </aside>

       
        <main>
            
            <header>
                <div class="header-title">
                    <h2>Dashboard Admin</h2>
                    <h3>Selamat datang, <?= h($_SESSION['nama'] ?? 'Admin') ?>!</h3>
                    <p>Kelola Sistem Secret Santa SKARIGA</p>
                </div>
                <div class="user-profile">
                    <div>
                        <strong><?= h($_SESSION['nama'] ?? 'Admin') ?></strong><br>
                        <small style="color: #666;">Administrator</small>
                    </div>
                    <div class="user-avatar"></div>
                </div>
            </header>

            
            <section class="grid-4">
                <div class="card banner-card">
                    <h3>SKARIGA<br>SECRET<br>SANTA</h3>
                </div>
                <div class="card stat-card">
                    <p>Total Event</p>
                    <h3><?= $total_event ?></h3>
                </div>
                <div class="card stat-card">
                    <p>Total Peserta</p>
                    <h3><?= $total_peserta ?></h3>
                </div>
                <div class="card stat-card">
                    <p>Total Pengundian</p>
                    <h3><?= $total_pengundian ?></h3>
                </div>
                <div class="card stat-card">
                    <p>Timeline</p>
                    <h3><?= $total_timeline ?></h3>
                </div>
            </section>

            
            <section class="grid-2">
                <!-- Event Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <h3>Event Terbaru</h3>
                        <a href="<?= base_url('event.php') ?>" class="btn-all">Lihat Semua</a>
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
                            <?php if ($event_terbaru->num_rows === 0): ?>
                                <tr><td colspan="3" style="text-align:center; color:#666;">Belum ada event</td></tr>
                            <?php else: ?>
                                <?php while ($e = $event_terbaru->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= h($e['nama_event']) ?></td>
                                        <td><?= $e['tanggal_event'] ? date('d M Y', strtotime($e['tanggal_event'])) : '-' ?></td>
                                        <td class="<?= $e['status'] === 'aktif' ? 'badge-active' : 'badge-done' ?>">
                                            <?= h(ucfirst($e['status'])) ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
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
                            <?php if ($pengundian_terbaru->num_rows === 0): ?>
                                <tr><td colspan="3" style="text-align:center; color:#666;">Belum ada pengundian</td></tr>
                            <?php else: ?>
                                <?php while ($p = $pengundian_terbaru->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= h($p['nama_event']) ?></td>
                                        <td><?= date('d M Y', strtotime($p['tanggal_undi'])) ?></td>
                                        <td class="<?= $p['status'] === 'terkirim' ? 'badge-done' : 'badge-active' ?>">
                                            <?= h(ucfirst($p['status'])) ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

           
            <section class="grid-2">
                
                <div class="card quick-draw">
                    <h3>Pengundian Cepat</h3>
                    <p>Lakukan pengundian acak secara otomatis</p>
                    <form action="" method="POST">
                        <button type="submit" name="acak" class="btn-primary">Lakukan Pengacakan Otomatis</button>
                    </form>
                </div>
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