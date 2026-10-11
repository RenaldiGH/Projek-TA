<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$events   = get_events($conn);
$event_id = resolve_event_id($conn, $_GET['event_id'] ?? $_POST['event_id'] ?? null);

$pesan = '';
$tipe_pesan = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event_id) {

    // Ambil semua peserta event ini
    $stmt = $conn->prepare("SELECT id FROM peserta WHERE event_id = ?");
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $ids = [];
    while ($r = $res->fetch_assoc()) {
        $ids[] = (int) $r['id'];
    }
    $stmt->close();

    if (count($ids) < 2) {
        $pesan = 'Peserta minimal 2 orang untuk melakukan pengundian.';
        $tipe_pesan = 'error';
    } else {
        // Acak urutan, lalu setiap orang memberi ke orang berikutnya (melingkar)
        // Cara ini menjamin tidak ada yang mendapat dirinya sendiri.
        shuffle($ids);
        $total = count($ids);

        $conn->begin_transaction();
        try {
            // Hapus hasil undian lama (jika undi ulang)
            $del = $conn->prepare("DELETE FROM pengundian WHERE event_id = ?");
            $del->bind_param('i', $event_id);
            $del->execute();
            $del->close();

            $ins = $conn->prepare(
                "INSERT INTO pengundian (event_id, pemberi_id, penerima_id) VALUES (?, ?, ?)"
            );
            for ($i = 0; $i < $total; $i++) {
                $pemberi  = $ids[$i];
                $penerima = $ids[($i + 1) % $total];
                $ins->bind_param('iii', $event_id, $pemberi, $penerima);
                $ins->execute();
            }
            $ins->close();

            $conn->commit();
            $pesan = 'Pengundian berhasil dilakukan.';
        } catch (Throwable $e) {
            $conn->rollback();
            $pesan = 'Pengundian gagal, silakan coba lagi.';
            $tipe_pesan = 'error';
        }
    }
}

$hasil = [];

if ($event_id) {
    $stmt = $conn->prepare(
        "SELECT pg.id, pb.nama AS pemberi_nama, pn.nama AS penerima_nama
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
        $hasil[] = $row;
    }
    $stmt->close();
}

$page_title    = 'Pengundian';
$page_subtitle = 'Kelola pengundian Secret Santa SKARIGA';
$active_menu   = 'pengundian';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<?php if (!$events): ?>

    <div class="table-card">
        <div class="empty-state">
            <h3>Belum Ada Event</h3>
            <p>Buat event terlebih dahulu sebelum melakukan pengundian.</p>
        </div>
    </div>

<?php else: ?>

    <?php if ($pesan): ?>
        <div style="margin-bottom:14px; padding:10px 14px; border-radius:8px; font-size:13px;
                    background:<?= $tipe_pesan === 'success' ? '#e6f4ea' : '#fdecea' ?>;
                    color:<?= $tipe_pesan === 'success' ? '#1e6b34' : '#a12622' ?>;">
            <?= h($pesan) ?>
        </div>
    <?php endif; ?>

    <!-- Tombol Mulai Pengundian -->
    <div style="display:flex; justify-content:flex-end; margin-bottom:10px;">
        <form method="POST"
              onsubmit="return confirm('<?= $hasil ? 'Hasil undian sebelumnya akan diganti. Lanjutkan?' : 'Mulai pengundian sekarang?' ?>');">
            <input type="hidden" name="event_id" value="<?= (int) $event_id ?>">
            <button type="submit"
                    style="background:#1a0066; color:#fff; font-size:12px; font-weight:600; padding:8px 14px; border:none; border-radius:6px; cursor:pointer;">
                <?= $hasil ? '↻ Undi Ulang' : '+ Mulai Pengundian' ?>
            </button>
        </form>
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

        <?php if ($hasil): ?>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #ddd;">
                        <th style="width:15%; text-align:left; padding:16px 20px; font-size:17px; font-weight:700;">No.</th>
                        <th style="text-align:center; padding:16px 20px; font-size:17px; font-weight:700;">Pemberi</th>
                        <th style="text-align:center; padding:16px 20px; font-size:17px; font-weight:700;">Penerima</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hasil as $i => $row): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:14px 20px;"><?= $i + 1 ?>.</td>
                            <td style="padding:14px 20px; text-align:center;"><?= h($row['pemberi_nama']) ?></td>
                            <td style="padding:14px 20px; text-align:center;"><?= h($row['penerima_nama']) ?></td>
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
                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"></circle>
                    <circle cx="15.5" cy="8.5" r="1.5" fill="currentColor"></circle>
                    <circle cx="12" cy="12" r="1.5" fill="currentColor"></circle>
                    <circle cx="8.5" cy="15.5" r="1.5" fill="currentColor"></circle>
                    <circle cx="15.5" cy="15.5" r="1.5" fill="currentColor"></circle>
                </svg>

                <h3 style="margin:28px 0 4px; font-size:16px; font-weight:700;">Belum Ada Pengundian</h3>
                <p style="margin:0; font-size:16px;">Klik "Mulai Pengundian" untuk melakukan undian otomatis</p>
            </div>
        <?php endif; ?>

    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>