<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$events   = get_events($conn);
$event_id = resolve_event_id($conn, $_GET['event_id'] ?? null);

$nama_event = '';
foreach ($events as $e) {
    if ((int) $e['id'] === (int) $event_id) {
        $nama_event = $e['nama_event'];
        break;
    }
}

$laporan = [];

if ($event_id) {
    $stmt = $conn->prepare(
        "SELECT pg.*, pb.nama AS pemberi_nama, pn.nama AS penerima_nama
         FROM pengundian pg
         JOIN peserta pb ON pb.id = pg.pemberi_id
         JOIN peserta pn ON pn.id = pg.penerima_id
         WHERE pg.event_id = ?
         ORDER BY pb.nama ASC"
    );
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $laporan[] = $row;
    }
    $stmt->close();
}

// Format tanggal undi (mendukung kolom tanggal_undi atau created_at)
function format_tanggal_undi(array $row): string
{
    $tgl = $row['tanggal_undi'] ?? $row['created_at'] ?? null;
    if (!$tgl) {
        return '-';
    }
    $ts = strtotime($tgl);
    return $ts ? date('d-m-Y H:i', $ts) : '-';
}

$page_title    = 'Laporan';
$page_subtitle = 'Lihat laporan data pengundian';
$active_menu   = 'laporan';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<style>
    @media print {
        aside, nav, .no-print, .sidebar { display: none !important; }
        main, .main-content { margin: 0 !important; width: 100% !important; }
    }
</style>

<?php if (!$events): ?>

    <div class="table-card">
        <div class="empty-state">
            <h3>Belum Ada Event</h3>
            <p>Buat event terlebih dahulu sebelum melihat laporan.</p>
        </div>
    </div>

<?php else: ?>

    <!-- Tombol Unduh PDF -->
    <div class="no-print" style="display:flex; justify-content:flex-end; margin-bottom:10px;">
        <?php if ($laporan): ?>
            <button type="button" onclick="window.print()"
                    style="background:#1a0066; color:#fff; font-size:12px; font-weight:600; padding:8px 14px; border:none; border-radius:6px; cursor:pointer;">
                ⬇ Unduh PDF
            </button>
        <?php else: ?>
            <button type="button" disabled
                    style="background:#1a0066; color:#fff; font-size:12px; font-weight:600; padding:8px 14px; border:none; border-radius:6px; opacity:0.6; cursor:not-allowed;">
                ⬇ Unduh PDF
            </button>
        <?php endif; ?>
    </div>

    <!-- Pilih Event -->
    <form method="GET" class="no-print" style="margin-bottom:18px;">
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

    <!-- Judul khusus saat dicetak -->
    <h2 style="display:none; margin-bottom:10px;" class="print-title">Laporan Pengundian - <?= h($nama_event) ?></h2>
    <style>@media print { .print-title { display:block !important; } }</style>

    <!-- Kartu Tabel -->
    <div style="background:#fff; border:1px solid #ddd; border-radius:12px; overflow:hidden;">

        <?php if ($laporan): ?>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #ddd;">
                        <th style="width:10%; text-align:left; padding:16px 20px; font-size:17px; font-weight:700;">No.</th>
                        <th style="text-align:center; padding:16px 20px; font-size:17px; font-weight:700;">Pemberi</th>
                        <th style="text-align:center; padding:16px 20px; font-size:17px; font-weight:700;">Penerima</th>
                        <th style="text-align:center; padding:16px 20px; font-size:17px; font-weight:700;">Tanggal Undi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laporan as $i => $row): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:14px 20px;"><?= $i + 1 ?>.</td>
                            <td style="padding:14px 20px; text-align:center;"><?= h($row['pemberi_nama']) ?></td>
                            <td style="padding:14px 20px; text-align:center;"><?= h($row['penerima_nama']) ?></td>
                            <td style="padding:14px 20px; text-align:center;"><?= h(format_tanggal_undi($row)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
            <!-- Empty State -->
            <div style="text-align:center; padding:50px 20px 60px;">

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                     width="90" height="90" style="color:#000;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="12" y1="12" x2="12" y2="18"></line>
                    <polyline points="9 15 12 18 15 15"></polyline>
                </svg>

                <h3 style="margin:28px 0 4px; font-size:16px; font-weight:700;">Belum Ada Laporan</h3>
                <p style="margin:0; font-size:16px;">Lakukan pengundian untuk melihat laporan</p>
            </div>
        <?php endif; ?>

    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>