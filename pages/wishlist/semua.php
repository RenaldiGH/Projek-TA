<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_peserta();

$user_id = (int) $_SESSION['user_id'];

// Hanya event yang diikuti user ini yang boleh dilihat
$my_events = get_user_events($conn, $user_id);

$event_id = 0;
$picked   = null;

if ($my_events) {
    $requested = (int) ($_GET['event_id'] ?? 0);
    $picked    = $my_events[0];

    foreach ($my_events as $e) {
        if ((int) $e['id'] === $requested) {
            $picked = $e;
            break;
        }
    }

    $event_id = (int) $picked['id'];
}

/* ---------- Wishlist semua peserta aktif di event ini ---------- */
$peserta_list = [];   // peserta_id => ['nama', 'user_id', 'items' => []]
$total_item   = 0;

if ($event_id) {
    $stmt = $conn->prepare(
        "SELECT p.id AS peserta_id, p.user_id, p.nama,
                w.id AS wishlist_id, w.nama_barang, w.kategori, w.estimasi_harga,
                w.deskripsi, w.link_referensi, w.status
         FROM peserta p
         LEFT JOIN wishlist w ON w.peserta_id = p.id
         WHERE p.event_id = ? AND p.status = 'aktif'
         ORDER BY (p.user_id = ?) DESC, p.nama ASC, w.id ASC"
    );
    $stmt->bind_param('ii', $event_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $pid = (int) $row['peserta_id'];

        if (!isset($peserta_list[$pid])) {
            $peserta_list[$pid] = [
                'nama'    => $row['nama'],
                'user_id' => (int) $row['user_id'],
                'items'   => [],
            ];
        }

        if ($row['wishlist_id'] !== null) {
            $peserta_list[$pid]['items'][] = $row;
            $total_item++;
        }
    }

    $stmt->close();
}

$page_title    = 'Wishlist Peserta';
$page_subtitle = 'Lihat wishlist semua peserta di event yang kamu ikuti.';
$active_menu   = 'wishlist';

require_once __DIR__ . '/../../includes/user_layout_top.php';
?>

<?php if (!$my_events): ?>

    <div class="panel">
        <div class="empty">
            <h3>Kamu Belum Ikut Event</h3>
            <p>Ikut minimal satu event dulu untuk melihat wishlist peserta.</p>
            <a href="<?= base_url('pages/dashboard/dashboarduser.php') ?>" class="btn btn-primary">Pilih Event</a>
        </div>
    </div>

<?php else: ?>

    <form method="GET" class="filter-bar">
        <label for="event_id">Event</label>
        <select name="event_id" id="event_id" onchange="this.form.submit()">
            <?php foreach ($my_events as $e): ?>
                <option value="<?= (int) $e['id'] ?>" <?= (int) $e['id'] === $event_id ? 'selected' : '' ?>>
                    <?= h($e['nama_event']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <nav class="wl-tabs">
        <a href="<?= h(base_url('pages/wishlist/saya.php?event_id=' . $event_id)) ?>">Wishlist Saya</a>
        <a href="<?= h(base_url('pages/wishlist/semua.php?event_id=' . $event_id)) ?>" class="active">Wishlist Semua Peserta</a>
    </nav>

    <?php if (!empty($picked['tema']) || !empty($picked['aturan_kado'])): ?>
        <section class="panel rv-rules">
            <?php if (!empty($picked['tema'])): ?>
                <div class="rv-rules-tema"><span>Tema Kado</span> <?= h($picked['tema']) ?></div>
            <?php endif; ?>
            <?php if (!empty($picked['aturan_kado'])): ?>
                <div class="rv-rules-title">Aturan Kado</div>
                <div class="rich-content"><?= tampilkan_rich($picked['aturan_kado']) ?></div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if (!$peserta_list): ?>

        <div class="panel">
            <div class="empty">
                <h3>Belum Ada Peserta</h3>
                <p>Belum ada peserta aktif di event ini.</p>
            </div>
        </div>

    <?php else: ?>

        <div class="wl-toolbar">
            <div class="wl-summary">
                <strong><?= count($peserta_list) ?></strong> peserta &middot;
                <strong><?= $total_item ?></strong> item wishlist
            </div>
            <input type="text" id="wl-cari" class="wl-search" placeholder="Cari peserta atau barang..." autocomplete="off">
        </div>

        <?php foreach ($peserta_list as $pid => $p): ?>
            <?php $milikku = $p['user_id'] === $user_id; ?>

            <section class="panel wl-peserta" data-nama="<?= h(mb_strtolower($p['nama'], 'UTF-8')) ?>">
                <h2>
                    <?= h($p['nama']) ?>
                    <?php if ($milikku): ?>
                        <span class="tag ok">Kamu</span>
                    <?php endif; ?>
                    <small class="wl-count"><?= count($p['items']) ?> item</small>
                    <?php if ($milikku): ?>
                        <a href="<?= h(base_url('pages/wishlist/saya.php?event_id=' . $event_id)) ?>" class="btn btn-secondary btn-small wl-kelola">Kelola wishlist saya</a>
                    <?php endif; ?>
                </h2>

                <?php if (!$p['items']): ?>

                    <p class="wl-kosong">
                        <?= $milikku ? 'Kamu belum mengisi wishlist di event ini.' : 'Belum mengisi wishlist.' ?>
                    </p>

                <?php else: ?>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:50px;">No</th>
                                    <th>Barang</th>
                                    <th>Kategori</th>
                                    <th>Estimasi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($p['items'] as $i => $w): ?>
                                    <?php $dipilih = $w['status'] === 'dipilih'; ?>
                                    <tr class="wl-row" data-barang="<?= h(mb_strtolower($w['nama_barang'], 'UTF-8')) ?>">
                                        <td><?= $i + 1 ?>.</td>
                                        <td>
                                            <strong><?= h($w['nama_barang']) ?></strong>
                                            <?php if (!empty($w['deskripsi'])): ?>
                                                <br><small style="color:#6b7280;"><?= h(rich_ringkas($w['deskripsi'], 80)) ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($w['link_referensi']) && preg_match('#^https?://#i', $w['link_referensi'])): ?>
                                                <br><a href="<?= h($w['link_referensi']) ?>" target="_blank" rel="noopener noreferrer" style="font-size:12px;">Lihat referensi</a>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= h($w['kategori'] ?: '-') ?></td>
                                        <td><?= h(rupiah($w['estimasi_harga'])) ?></td>
                                        <td>
                                            <span class="tag <?= $dipilih ? 'ok' : '' ?>">
                                                <?= $dipilih ? 'Dipilih' : 'Belum dipilih' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>
            </section>
        <?php endforeach; ?>

        <div class="panel wl-tidak-ada" id="wl-tidak-ada" style="display:none;">
            <div class="empty">
                <h3>Tidak Ditemukan</h3>
                <p>Tidak ada peserta atau barang yang cocok dengan pencarian.</p>
            </div>
        </div>

        <script>
            (function () {
                var input   = document.getElementById('wl-cari');
                var panels  = document.querySelectorAll('.wl-peserta');
                var kosong  = document.getElementById('wl-tidak-ada');
                if (!input) return;

                input.addEventListener('input', function () {
                    var q = input.value.trim().toLowerCase();
                    var tampil = 0;

                    panels.forEach(function (panel) {
                        var cocokNama = q === '' || panel.getAttribute('data-nama').indexOf(q) !== -1;
                        var ada = false;

                        panel.querySelectorAll('.wl-row').forEach(function (row) {
                            var cocok = cocokNama || row.getAttribute('data-barang').indexOf(q) !== -1;
                            row.style.display = cocok ? '' : 'none';
                            if (cocok) ada = true;
                        });

                        var lihat = cocokNama || ada;
                        panel.style.display = lihat ? '' : 'none';
                        if (lihat) tampil++;
                    });

                    kosong.style.display = tampil === 0 ? '' : 'none';
                });
            })();
        </script>

    <?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/user_layout_bottom.php'; ?>