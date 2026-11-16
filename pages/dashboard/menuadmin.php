<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

// ---------- Foto profil admin (untuk avatar di header) ----------
$userId = (int)($_SESSION['user_id'] ?? 0);
$fotoAdmin = '';

$stmt = $conn->prepare("SELECT foto FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$rowFoto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!empty($rowFoto['foto'])
    && is_file(__DIR__ . '/../../assets/uploads/profile/' . basename($rowFoto['foto']))) {
    $fotoAdmin = base_url('assets/uploads/profile/' . rawurlencode($rowFoto['foto']));
}

$total_event = (int) $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc()['total'];
$total_peserta = (int) $conn->query("SELECT COUNT(*) AS total FROM peserta")->fetch_assoc()['total'];
$total_pengundian = (int) $conn->query("SELECT COUNT(*) AS total FROM pengundian")->fetch_assoc()['total'];
$total_timeline = (int) $conn->query("SELECT COUNT(*) AS total FROM timeline")->fetch_assoc()['total'];

$event_terbaru = $conn->query(
    "SELECT nama_event, tanggal_event, status
     FROM events
     ORDER BY created_at DESC
     LIMIT 5"
);

$daftar_event = [];
$resDaftar = $conn->query("SELECT id, nama_event FROM events ORDER BY created_at DESC");
while ($resDaftar && ($ev = $resDaftar->fetch_assoc())) {
    $daftar_event[] = $ev;
}

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style_admin.css') ?>">
</head>

<body>

    <div class="dashboard-container">

        <aside class="admin-sidebar">

    <div class="brand">
        <h2>SKARIGA SECRET SANTA</h2>
    </div>

    <nav class="admin-menu">

        <div class="admin-menu-group">
            <span class="admin-menu-heading">MAIN</span>
            <ul>
                <li class="active"><a href="<?= base_url('pages/dashboard/menuadmin.php') ?>"><i class="fa-solid fa-house admin-icon"></i><span>Dashboard</span></a></li>
            </ul>
        </div>

        <div class="admin-menu-group">
            <span class="admin-menu-heading">MANAGEMENT</span>
            <ul>
                <li><a href="<?= base_url('pages/event/event.php') ?>"><i class="fa-solid fa-calendar-days admin-icon"></i><span>Event</span></a></li>
                <li><a href="<?= base_url('pages/peserta/index.php') ?>"><i class="fa-solid fa-users admin-icon"></i><span>Peserta</span></a></li>
                <li><a href="<?= base_url('pages/wishlist/index.php') ?>"><i class="fa-solid fa-heart admin-icon"></i><span>Wishlist</span></a></li>
                <li><a href="<?= base_url('pages/timeline/index.php') ?>"><i class="fa-solid fa-clock admin-icon"></i><span>Timeline</span></a></li>
                <li><a href="<?= base_url('pages/pengundian/index.php') ?>"><i class="fa-solid fa-shuffle admin-icon"></i><span>Pengundian</span></a></li>
                <li><a href="<?= base_url('pages/pemberi/index.php') ?>"><i class="fa-solid fa-gift admin-icon"></i><span>Pemberi</span></a></li>
                <li><a href="<?= base_url('pages/penerima/index.php') ?>"><i class="fa-solid fa-user-check admin-icon"></i><span>Penerima</span></a></li>
            </ul>
        </div>

        <div class="admin-menu-group">
            <span class="admin-menu-heading">TOOLS</span>
            <ul>
                <li><a href="<?= base_url('pages/laporan/index.php') ?>"><i class="fa-solid fa-file-lines admin-icon"></i><span>Laporan</span></a></li>
                <li><a href="<?= base_url('pages/riwayat/index.php') ?>"><i class="fa-solid fa-clock-rotate-left admin-icon"></i><span>Riwayat</span></a></li>
                <li><a href="<?= base_url('pages/user/index.php') ?>"><i class="fa-solid fa-user admin-icon"></i><span>User</span></a></li>
                <li><a href="<?= base_url('pages/adminpengaturan/index.php') ?>"><i class="fa-solid fa-gear admin-icon"></i><span>Pengaturan</span></a></li>
            </ul>
        </div>

    </nav>

    <div class="admin-sidebar-bottom">
        <div class="admin-sidebar-divider"></div>
        <a href="<?= base_url('logout.php') ?>"><i class="fa-solid fa-right-from-bracket admin-icon"></i><span>Keluar</span></a>
    </div>

</aside>


        <main>

            <header>
                <div class="header-title">
                    <h2>Dashboard Admin</h2>
                    <h3>Selamat datang, <?= h($_SESSION['nama'] ?? 'Admin') ?>!</h3>
                    <p>Kelola Sistem Secret Santa SKARIGA</p>
                </div>

                <a href="<?= base_url('pages/profiladmin/index.php') ?>"
                    class="user-profile"
                    style="text-decoration: none; color: inherit;">

                    <div style="text-align: right;">
                        <strong><?= h($_SESSION['nama'] ?? 'Admin') ?></strong><br>
                        <small style="color: #666;">Administrator</small>
                    </div>

                    <div class="user-avatar" style="overflow:hidden;">
                        <?php if ($fotoAdmin): ?>
                            <img src="<?= h($fotoAdmin) ?>" alt="Foto profil"
                                style="width:100%; height:100%; object-fit:cover; border-radius:50%; display:block;">
                        <?php endif; ?>
                    </div>

                </a>
            </header>


            <section class="grid-4">
                <div class="card banner-card" style="position: relative; overflow: hidden;">
                    <h3 style="position: relative; z-index: 2; max-width: 55%; font-size: 20px; line-height: 1.2;">
                        SKARIGA<br>
                        SECRET<br>
                        SANTA
                    </h3>

                    <img src="<?= base_url('assets/img/dasbor.png') ?>"
                        alt="santa"
                        style="
                  position: absolute;
                  right: -10px;
                  bottom: 0;
                  width: 130px;
                  z-index: 1;

            ">
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
                        <a href="<?= base_url('pages/event/event.php') ?>" class="btn-all">Lihat Semua</a>
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
                                <tr>
                                    <td colspan="3" style="text-align:center; color:#666;">Belum ada event</td>
                                </tr>
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
                        <a href="<?= base_url('pages/pengundian/index.php') ?>" class="btn-all">Lihat Semua</a>
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
                                <tr>
                                    <td colspan="3" style="text-align:center; color:#666;">Belum ada pengundian</td>
                                </tr>
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
                    <?php if (!$daftar_event): ?>
                        <p style="color:#666; font-size:13px;">Belum ada event. Buat event terlebih dahulu.</p>
                    <?php else: ?>
                        <form action="<?= base_url('pages/pengundian/index.php') ?>" method="GET">
                            <input type="hidden" name="auto" value="1">
                            <select name="event_id" aria-label="Pilih event"
                                    style="width:100%; height:36px; padding:0 10px; margin-bottom:10px; font-size:13px; background:#fff; border:1px solid #ccc; border-radius:10px;">
                                <?php foreach ($daftar_event as $ev): ?>
                                    <option value="<?= (int) $ev['id'] ?>"><?= h($ev['nama_event']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-primary">Lakukan Pengacakan Otomatis</button>
                        </form>
                    <?php endif; ?>
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