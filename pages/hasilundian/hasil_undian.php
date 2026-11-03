<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_login();

$user_id = (int) ($_SESSION['user_id'] ?? 0);

$hasil = [];
$jumlah_event_diikuti = 0;
$error_message = '';

// Set true saat development agar pesan error asli tampil. Ubah ke false di server asli.
$debug = true;

/*
|--------------------------------------------------------------------------
| PEMETAAN TABEL & KOLOM  (sesuaikan jika nama di database berbeda)
|--------------------------------------------------------------------------
| Cek di phpMyAdmin: tabel pengundian, peserta, events, wishlist.
*/
$T_UNDIAN   = 'pengundian';
$T_PESERTA  = 'peserta';
$T_EVENT    = 'events';
$T_WISHLIST = 'wishlist';

$C_UNDIAN_ID       = 'id';
$C_UNDIAN_EVENT    = 'event_id';
$C_UNDIAN_PEMBERI  = 'pemberi_id';
$C_UNDIAN_PENERIMA = 'penerima_id';

$C_EVENT_ID   = 'id';
$C_EVENT_NAMA = 'nama_event';

try {

    if ($user_id <= 0) {
        throw new Exception('ID pengguna tidak ditemukan. Silakan login kembali.');
    }

    $sql = "SELECT
                u.$C_UNDIAN_ID AS id,
                u.$C_UNDIAN_EVENT AS event_id,
                e.$C_EVENT_NAMA AS nama_event,
                pn.id AS penerima_id,
                pn.nama AS penerima_nama

            FROM $T_PESERTA me

            INNER JOIN $T_UNDIAN u
                ON u.$C_UNDIAN_PEMBERI = me.id
                AND u.$C_UNDIAN_EVENT = me.event_id

            INNER JOIN $T_PESERTA pn
                ON pn.id = u.$C_UNDIAN_PENERIMA

            INNER JOIN $T_EVENT e
                ON e.$C_EVENT_ID = u.$C_UNDIAN_EVENT

            WHERE me.user_id = ?

            ORDER BY e.$C_EVENT_ID DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $row['wishlist'] = [];
        $hasil[] = $row;
    }

    $stmt->close();

    // Cek apakah user sudah ikut event (untuk memilih pesan kosong yang tepat)
    $stmtC = $conn->prepare("SELECT COUNT(*) FROM $T_PESERTA WHERE user_id = ?");
    $stmtC->bind_param('i', $user_id);
    $stmtC->execute();
    $stmtC->bind_result($jumlah_event_diikuti);
    $stmtC->fetch();
    $stmtC->close();

    /*
    |--------------------------------------------------------------------------
    | AMBIL WISHLIST PENERIMA
    |--------------------------------------------------------------------------
    */

    if (!empty($hasil)) {

        $sqlWishlist = "SELECT
                            nama_barang,
                            kategori,
                            estimasi_harga

                        FROM $T_WISHLIST

                        WHERE peserta_id = ?

                        ORDER BY id ASC";

        $stmtW = $conn->prepare($sqlWishlist);

        foreach ($hasil as &$h) {

            $pid = (int) $h['penerima_id'];

            $stmtW->bind_param('i', $pid);
            $stmtW->execute();

            $resultWishlist = $stmtW->get_result();

            while ($w = $resultWishlist->fetch_assoc()) {
                $h['wishlist'][] = $w;
            }
        }

        unset($h);

        $stmtW->close();
    }

} catch (Throwable $e) {

    // Catat error untuk pemeriksaan admin.
    error_log('Hasil Undian Error: ' . $e->getMessage());

    $error_message = $debug
        ? 'Error: ' . $e->getMessage()
        : 'Data hasil undian belum dapat ditampilkan. Silakan hubungi admin.';
}

$page_title = 'Hasil Undian';
$page_subtitle = 'Siapa yang harus kamu beri hadiah';
$active_menu = 'undian';

require_once __DIR__ . '/../../includes/user_layout_top.php';
?>

<style>
    /* Disamakan dengan tampilan halaman Timeline Saya */
    .us-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 2px 12px rgba(17, 21, 47, 0.05);
    }
    .us-empty {
        box-sizing: border-box;
        min-height: 244px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 27px 24px 24px;
    }
    .us-empty h3 {
        margin: 0 0 6px;
        font-size: 18px;
        font-weight: 700;
        color: #11152f;
    }
    .us-empty p {
        margin: 0 0 18px;
        font-size: 16px;
        color: #6b7280;
    }
    .us-empty p:last-child { margin-bottom: 0; }
    .us-btn {
        display: inline-block;
        background: #e00000;
        color: #ffffff;
        font-size: 14px;
        font-weight: 700;
        padding: 10px 18px;
        line-height: 18px;
        border-radius: 8px;
        text-decoration: none;
    }
    .us-btn:hover { background: #c40000; }
    .us-result { padding: 28px 32px; margin-bottom: 20px; }
    .us-result h3 { margin: 0 0 4px; font-size: 18px; font-weight: 700; color: #11152f; }
    .us-result p  { margin: 0 0 16px; color: #6b7280; }
    .us-result p strong { color: #11152f; }
</style>

<?php if ($error_message): ?>

    <div class="us-card us-empty">
        <div>
            <h3>Terjadi Kesalahan</h3>
            <p><?= h($error_message) ?></p>
        </div>
    </div>

<?php elseif (empty($hasil)): ?>

    <div class="us-card us-empty">

        <?php if ($jumlah_event_diikuti > 0): ?>

            <h3>Undian Belum Diacak</h3>
            <p>Hasil undian akan muncul setelah admin menyelesaikan proses pengundian.</p>

        <?php else: ?>

            <h3>Kamu Belum Ikut Event</h3>
            <p>Hasil undian akan muncul setelah kamu mengikuti event.</p>
            <a href="<?= base_url('pages/dashboard/dashboarduser.php') ?>" class="us-btn">Pilih Event</a>

        <?php endif; ?>

    </div>

<?php else: ?>

    <?php foreach ($hasil as $h): ?>

        <div class="us-card us-result">

            <h3>
                <?= h($h['nama_event']) ?>
            </h3>

            <p>
                Kamu memberi hadiah kepada:
                <strong><?= h($h['penerima_nama']) ?></strong>
            </p>

            <h4 style="margin-bottom:12px;">
                Wishlist Penerima
            </h4>

            <?php if (empty($h['wishlist'])): ?>

                <p>Penerima belum mengisi wishlist.</p>

            <?php else: ?>

                <div style="overflow-x:auto;">

                    <table class="data-table">

                        <thead>
                            <tr>
                                <th style="width:50px;">NO</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Estimasi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($h['wishlist'] as $i => $w): ?>

                                <tr>
                                    <td><?= $i + 1 ?>.</td>

                                    <td>
                                        <?= h($w['nama_barang']) ?>
                                    </td>

                                    <td>
                                        <?= h($w['kategori']) ?>
                                    </td>

                                    <td>
                                        Rp<?= number_format(
                                            (float) $w['estimasi_harga'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    <?php endforeach; ?>

<?php endif; ?>

<?php
require_once __DIR__ . '/../../includes/user_layout_bottom.php';
?>