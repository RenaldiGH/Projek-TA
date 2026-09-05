<?php
// event.php
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Event - SKARIGA Secret Santa</title>
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
                    <li><a href="<?= base_url('pages/dashboard/menuadmin.php') ?>">Dashboard</a></li>
                    <li class="active"><a href="event.php">Event</a></li>
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

        
        <main>
          
            <header class="page-header">
                <h2>Data Event</h2>
                <p>Kelola Semua Event Secreet Santa Skariga</p>
            </header>

            
            <section class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">NO</th>
                            <th>Nama Event</th>
                            <th>Tanggal</th>
                            <th>Budget</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1.</td>
                            <td>Secreet Santa</td>
                            <td>25 Des 2025</td>
                            <td>100.000</td>
                            <td>Event Tukar Hadiah</td>
                            <td><span class="badge-active">Aktif</span></td>
                            <td class="action-buttons">
                                <a href="#" class="btn-edit" title="Edit">✏️</a>
                                <a href="#" class="btn-delete" title="Hapus">🗑️</a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination Navigasi -->
                <div class="pagination">
                    <a href="#" class="page-nav">&lt;</a>
                    <a href="#" class="page-num">1</a>
                    <a href="#" class="page-nav">&gt;</a>
                </div>
            </section>
        </main>
    </div>

</body>
</html>