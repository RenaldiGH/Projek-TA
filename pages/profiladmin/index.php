<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - SKARIGA Secret Santa</title>
    <link rel="stylesheet" href="../../assets/css/style_admin.css">
</head>
<body>

    <div class="dashboard-container">
        <aside>
            <div class="brand">
                <h2>SKARIGA SECRET SANTA</h2>
            </div>
            <nav>
                <ul>
                    <li class="active"><a href="<?= base_url('pages/dashboard/menuadmin.php') ?>">Dashboard</a></li>
                    <li><a href="<?= base_url('pages/event/event.php') ?>">Event</a></li>
                    <li><a href="<?= base_url('pages/peserta/index.php') ?>">Peserta</a></li>
                    <li><a href="<?= base_url('pages/wishlist/index.php') ?>">Wishlist</a></li>
                    <li><a href="<?= base_url('pages/timeline/index.php') ?>">Timeline</a></li>
                    <li><a href="<?= base_url('pages/pengundian/index.php') ?>">Pengundian</a></li>
                    <li><a href="#">Pemberi</a></li>
                    <li><a href="<?= base_url('pages/penerima/index.php') ?>">Penerima</a></li>
                    <li><a href="#">Laporan</a></li>
                    <li><a href="#">Pengaturan</a></li>
                    <li><a href="<?= base_url('logout.php') ?>">Keluar</a></li>
                </ul>
            </nav>
        </aside>

        <main>
            <header class="page-header">
                <div>
                    <h2>Profil Admin</h2>
                    <p>Kelola informasi akun admin.</p>
                </div>
                <a href="<?= base_url('pages/dashboard/menuadmin.php') ?>" class="btn-back">← Kembali</a>
            </header>

            <div class="profile-container">
                <div class="profile-card">
                    <div class="avatar-box">
                        <div class="avatar-circle">
                            <svg width="45" height="45" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                            <span class="avatar-badge">📷</span>
                        </div>
                        <h3><?= h($_SESSION['nama'] ?? 'Admin') ?></h3>
                        <span>Administrator</span>
                    </div>

                    <div class="info-list">
                        <div class="info-item">
                            <span>📅</span>
                            <div>
                                <strong>Bergabung Sejak</strong><br>
                                <span>01 Januari 2025</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <span>🕒</span>
                            <div>
                                <strong>Akun Terakhir Aktif</strong><br>
                                <span>20 Des 2024 23.59</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-card">
                    <form action="#" method="POST">
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" name="nama" value="<?= h($_SESSION['nama'] ?? 'Admin') ?>" class="select-input">
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="admin@gmail.com" class="select-input">
                        </div>

                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" value="Administrator" class="select-input" readonly>
                        </div>

                        <div class="form-group">
                            <label>Password Baru <small>(kosongkan jika tidak diubah)</small></label>
                            <input type="password" name="password" placeholder="Masukkan password baru" class="select-input">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-add">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

</body>
</html>