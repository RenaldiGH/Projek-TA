<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_login();

$user_id = (int) ($_SESSION['user_id'] ?? 0);

/*
|--------------------------------------------------------------------------
| SIMPAN PROGRES KADO (status + checklist) oleh pemberi
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id > 0) {
    $undian_id = (int) ($_POST['undian_id'] ?? 0);
    $status    = (string) ($_POST['status_kado'] ?? '');
    $cek_tema    = isset($_POST['cek_tema']) ? 1 : 0;
    $cek_budget  = isset($_POST['cek_budget']) ? 1 : 0;
    $cek_bungkus = isset($_POST['cek_bungkus']) ? 1 : 0;

    if (!array_key_exists($status, status_kado_daftar())) {
        set_flash('error', 'Status kado tidak valid.');
    } elseif (status_kado_butuh_checklist($status) && !($cek_tema && $cek_budget && $cek_bungkus)) {
        set_flash('error', 'Centang ketiga checklist dulu sebelum memilih "Sudah dibungkus" atau "Sudah diserahkan".');
    } else {
        // pastikan undian ini memang milik user yang sedang login (sebagai pemberi)
        $cek = $conn->prepare(
            "SELECT u.event_id
             FROM pengundian u
             INNER JOIN peserta me ON me.id = u.pemberi_id
             WHERE u.id = ? AND me.user_id = ?"
        );
        $cek->bind_param('ii', $undian_id, $user_id);
        $cek->execute();
        $baris = $cek->get_result()->fetch_assoc();
        $cek->close();

        if (!$baris) {
            set_flash('error', 'Data pengundian tidak ditemukan.');
        } else {
            $upd = $conn->prepare(
                "UPDATE pengundian
                 SET status_kado = ?, cek_tema = ?, cek_budget = ?, cek_bungkus = ?
                 WHERE id = ?"
            );
            $upd->bind_param('siiii', $status, $cek_tema, $cek_budget, $cek_bungkus, $undian_id);
            $upd->execute();
            $upd->close();

            catat_riwayat($conn, 'pengundian', 'ubah', 'Status kado: ' . status_kado_label($status), (int) $baris['event_id']);
            set_flash('success', 'Progres kado disimpan.');
        }
    }

    header('Location: ' . base_url('pages/hasilundian/hasil_undian.php'));
    exit;
}

$hasil = [];
$jumlah_event_diikuti = 0;
$error_message = '';

// Set true hanya saat development. Biarkan false saat demo / server asli.
$debug = false;

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
                pn.nama AS penerima_nama,
                e.tema AS tema,
                e.aturan_kado AS aturan_kado,
                e.budget AS budget,
                e.tanggal_event AS tanggal_event,
                u.status_kado AS status_kado,
                u.cek_tema AS cek_tema,
                u.cek_budget AS cek_budget,
                u.cek_bungkus AS cek_bungkus

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

    /* Info acara */
    .us-info {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        margin: 0 0 22px;
    }
    .us-info-grid { display: flex; flex-wrap: wrap; gap: 12px 36px; }
    .us-info-item small { display: block; color: #6b7280; font-size: 12px; margin-bottom: 2px; }
    .us-info-item span { font-weight: 700; color: #11152f; }
    .us-aturan { margin-top: 14px; padding-top: 14px; border-top: 1px dashed #cbd5e1; }
    .us-aturan > small { display: block; color: #6b7280; font-size: 12px; margin-bottom: 4px; }

    /* Progres kado */
    .us-progress { margin-top: 26px; padding-top: 22px; border-top: 1px solid #e5e7eb; }
    .us-progress h4 { margin: 0 0 10px; }
    .us-progress select {
        padding: 9px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        background: #fff;
        margin: 4px 0 14px;
    }
    .us-progress label.us-lbl { display: block; font-size: 13px; color: #6b7280; }
    .us-check { display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px; }
    .us-check label { display: flex; align-items: flex-start; gap: 8px; font-size: 14px; color: #11152f; cursor: pointer; }
    .us-check input { margin-top: 3px; accent-color: #e00000; }
    .us-progress .us-hint { font-size: 12px; color: #6b7280; margin: 0 0 14px; }
    button.us-btn { border: none; cursor: pointer; font-family: inherit; }
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

    <?php
    $cari = trim($_GET['q'] ?? '');
    $tampil = $hasil;
    if ($cari !== '') {
        $tampil = array_values(array_filter($hasil, function ($row) use ($cari) {
            return stripos($row['nama_event'], $cari) !== false
                || stripos($row['penerima_nama'], $cari) !== false;
        }));
    }
    ?>

    <?php if (count($hasil) > 1 || $cari !== ''): ?>
        <form method="GET" class="us-card" style="display:flex; gap:10px; align-items:center; margin-bottom:16px;">
            <input type="text" name="q" value="<?= h($cari) ?>" placeholder="Cari nama event atau penerima"
                   style="flex:1; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; font-family:inherit;">
            <button type="submit" class="us-btn" style="border:none; cursor:pointer;">Cari</button>
        </form>
    <?php endif; ?>

    <?php if (!$tampil): ?>
        <div class="us-card us-empty">
            <div>
                <h3>Tidak Ditemukan</h3>
                <p>Tidak ada hasil undian yang cocok dengan "<?= h($cari) ?>".</p>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($tampil as $h): ?>

        <div class="us-card us-result">

            <h3>
                <?= h($h['nama_event']) ?>
            </h3>

            <p>
                Kamu memberi hadiah kepada:
                <strong><?= h($h['penerima_nama']) ?></strong>
            </p>

            <!-- Info acara -->
            <div class="us-info">
                <div class="us-info-grid">
                    <?php if (!empty($h['tema'])): ?>
                        <div class="us-info-item"><small>Tema</small><span><?= h($h['tema']) ?></span></div>
                    <?php endif; ?>
                    <div class="us-info-item"><small>Budget kado</small><span><?= h(rupiah($h['budget'])) ?></span></div>
                    <?php if (!empty($h['tanggal_event'])): ?>
                        <div class="us-info-item"><small>Tanggal tukar kado</small><span><?= h(tanggal_id($h['tanggal_event'])) ?></span></div>
                    <?php endif; ?>
                </div>
                <?php if (trim((string) $h['aturan_kado']) !== ''): ?>
                    <div class="us-aturan">
                        <small>Aturan kado</small>
                        <div class="rich-content"><?= tampilkan_rich($h['aturan_kado']) ?></div>
                    </div>
                <?php endif; ?>
            </div>

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

            <!-- Progres kado -->
            <form method="POST" class="us-progress">
                <input type="hidden" name="undian_id" value="<?= (int) $h['id'] ?>">
                <h4>Progres Kado Kamu</h4>
                <p style="margin-bottom:12px;">Status saat ini: <?= status_kado_badge($h['status_kado']) ?></p>

                <div class="us-check">
                    <label>
                        <input type="checkbox" name="cek_tema" value="1" <?= !empty($h['cek_tema']) ? 'checked' : '' ?>>
                        <span>Kado sesuai tema<?= !empty($h['tema']) ? ' (' . h($h['tema']) . ')' : '' ?></span>
                    </label>
                    <label>
                        <input type="checkbox" name="cek_budget" value="1" <?= !empty($h['cek_budget']) ? 'checked' : '' ?>>
                        <span>Harga kado tidak melebihi budget (<?= h(rupiah($h['budget'])) ?>)</span>
                    </label>
                    <label>
                        <input type="checkbox" name="cek_bungkus" value="1" <?= !empty($h['cek_bungkus']) ? 'checked' : '' ?>>
                        <span>Kado sudah dibungkus rapi</span>
                    </label>
                </div>

                <label class="us-lbl" for="status_kado_<?= (int) $h['id'] ?>">Ubah status</label>
                <select name="status_kado" id="status_kado_<?= (int) $h['id'] ?>">
                    <?php foreach (status_kado_daftar() as $kode => $label): ?>
                        <option value="<?= h($kode) ?>" <?= $h['status_kado'] === $kode ? 'selected' : '' ?>><?= h($label) ?></option>
                    <?php endforeach; ?>
                </select>

                <p class="us-hint">Ketiga checklist harus dicentang sebelum memilih "Sudah dibungkus" atau "Sudah diserahkan".</p>
                <button type="submit" class="us-btn">Simpan Progres</button>
            </form>

        </div>

    <?php endforeach; ?>

<?php endif; ?>

<?php
require_once __DIR__ . '/../../includes/user_layout_bottom.php';
?>