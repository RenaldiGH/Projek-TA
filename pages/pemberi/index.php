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
        "SELECT pg.id, p.nama AS pemberi_nama
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
    <div style="display:flex; justify-content:flex-end; margin-bottom:10px;">
        <a href="<?= base_url('pages/pengundian/index.php?event_id=' . $event_id) ?>"
           style="background:#1a0066; color:#fff; font-size:12px; font-weight:600; padding:8px 14px; border-radius:6px; text-decoration:none;">
            + Mulai Pengundian
        </a>
    </div>

    <!-- Pilih Event -->
    <form method="GET" style="margin-bottom:18px;">
        <label for="event_id" style="display:block; font-size:12px; margin-bottom:4px;">Pilih Event</label>
        <select name="event_id" id="event_id" onchange="this.form.submit()"
                style="width:170px; height:34px; padding:0 10px; font-size:12px; background:#fff; border:1px solid #ccc; border-radius:10px;">
            <?php foreach ($events as $e): ?>
                <option value="<?= (int) $e['id'] ?>" <?= (int) $e['id'] === (int) $event_id ? 'selected' : '' ?>>
                    <?= h($e['nama_event']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Kartu Tabel -->
    <div style="background:#fff; border:1px solid #ddd; border-radius:12px; overflow:hidden;">

        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #ddd;">
                    <th style="width:20%; text-align:left; padding:16px 20px; font-size:17px; font-weight:700;">No.</th>
                    <th style="text-align:center; padding:16px 20px; font-size:17px; font-weight:700;">Nama Pemberi</th>
                    <th style="width:20%; text-align:center; padding:16px 20px; font-size:17px; font-weight:700;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pemberi_list as $i => $pb): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:14px 20px;"><?= $i + 1 ?>.</td>
                        <td style="padding:14px 20px; text-align:center;"><?= h($pb['pemberi_nama']) ?></td>
                        <td style="padding:14px 20px; text-align:center;">
                            <a href="<?= base_url('pages/laporan/index.php?event_id=' . $event_id) ?>" title="Lihat Laporan">👁️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (!$pemberi_list): ?>
            <!-- Empty State -->
            <div style="text-align:center; padding:50px 20px 60px;">

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 140 110" width="130" height="102" style="color:#000;">
                    <circle cx="30" cy="38" r="16" fill="currentColor"/>
                    <path d="M4 98 C4 74 16 62 30 62 C44 62 56 74 56 98 Z" fill="currentColor"/>
                    <circle cx="110" cy="38" r="16" fill="currentColor"/>
                    <path d="M84 98 C84 74 96 62 110 62 C124 62 136 74 136 98 Z" fill="currentColor"/>
                    <circle cx="70" cy="44" r="18" fill="currentColor" stroke="#fff" stroke-width="3"/>
                    <path d="M38 108 C38 82 52 68 70 68 C88 68 102 82 102 108 Z" fill="currentColor" stroke="#fff" stroke-width="3"/>
                </svg>

                <h3 style="margin:28px 0 4px; font-size:16px; font-weight:700;">Belum Ada Hasil Pengundian</h3>
                <p style="margin:0; font-size:16px;">Lakukan pengundian terlebih dahulu</p>

            </div>
        <?php endif; ?>

    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>