<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$events = get_events($conn);
$event_id = resolve_event_id($conn, $_GET['event_id'] ?? null);

$pemberi_list = [];

if ($event_id) {
    $stmt = $conn->prepare(
        "SELECT pg.id, p.nama AS pemberi_nama, pg.status_kado,
                pg.cek_tema, pg.cek_budget, pg.cek_bungkus
         FROM pengundian pg
         JOIN peserta p ON p.id = pg.pemberi_id
         WHERE pg.event_id = ?
         ORDER BY p.nama ASC"
    );
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $pemberi_list[] = $row;
    }

    $stmt->close();
}

$page_title = 'Pemberi(Peserta)';
$page_subtitle = 'Daftar Peserta yang memberi hadiah';
$active_menu = 'pemberi';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<?php if (!$events): ?>

    <div class="table-card">
        <div class="empty-state">
            <h3>Belum Ada Event</h3>
            <p>Buat event terlebih dahulu sebelum melihat pemberi.</p>
        </div>
    </div>

<?php else: ?>

    <!-- Tombol Mulai Pengundian -->
    <div style="display:flex; justify-content:flex-end; margin-bottom:16px;">
        <a href="<?= base_url('pages/pengundian/index.php?event_id=' . (int) $event_id) ?>" class="btn-add">+ Mulai Pengundian</a>
    </div>

    <div class="table-card">
        <form method="GET" class="filter-bar">
            <div class="filter-group">
                <label for="event_id">Pilih Event</label>
                <select name="event_id" id="event_id" onchange="this.form.submit()">
                    <?php foreach ($events as $e): ?>
                        <option value="<?= (int) $e['id'] ?>" <?= (int) $e['id'] === (int) $event_id ? 'selected' : '' ?>>
                            <?= h($e['nama_event']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if (!$pemberi_list): ?>
            <div class="empty-state">
                <h3>Belum Ada Hasil Pengundian</h3>
                <p>Lakukan pengundian terlebih dahulu.</p>
            </div>
        <?php else: ?>
            <?php
            $siap = 0;
            foreach ($pemberi_list as $pb) {
                if (in_array($pb['status_kado'], ['sudah_dibungkus', 'diserahkan'], true)) {
                    $siap++;
                }
            }
            $total = count($pemberi_list);
            $persen = $total > 0 ? (int) round($siap / $total * 100) : 0;
            ?>
            <div style="margin:0 0 18px;">
                <div style="display:flex; justify-content:space-between; font-size:13px; color:#475569; margin-bottom:6px;">
                    <span>Kado siap (sudah dibungkus / diserahkan)</span>
                    <strong><?= $siap ?> dari <?= $total ?> (<?= $persen ?>%)</strong>
                </div>
                <div style="height:10px; background:#e2e8f0; border-radius:999px; overflow:hidden;">
                    <div style="height:100%; width:<?= $persen ?>%; background:#e00000;"></div>
                </div>
            </div>

            <table class="data-table table-grid">
                <thead>
                    <tr>
                        <th style="width: 50px;">NO</th>
                        <th>Nama Pemberi</th>
                        <th>Status Kado</th>
                        <th>Checklist</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pemberi_list as $i => $pb): ?>
                        <tr>
                            <td><?= $i + 1 ?>.</td>
                            <td><?= h($pb['pemberi_nama']) ?></td>
                            <td><?= status_kado_badge($pb['status_kado']) ?></td>
                            <td><?= (int) $pb['cek_tema'] + (int) $pb['cek_budget'] + (int) $pb['cek_bungkus'] ?>/3</td>
                            <td class="action-buttons">
                                <a href="<?= base_url('pages/laporan/index.php?event_id=' . (int) $event_id) ?>" title="Lihat Laporan">👁️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>