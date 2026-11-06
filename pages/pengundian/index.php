<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$events   = get_events($conn);
$event_id = resolve_event_id($conn, $_GET['event_id'] ?? $_POST['event_id'] ?? null);

$pesan = '';
$tipe_pesan = 'success';
$baru_diundi = false; // true -> animasi reveal + confetti setelah undian

// Pesan hasil undian (dari redirect setelah POST)
if (!empty($_SESSION['flash_undi'])) {
    $pesan       = $_SESSION['flash_undi']['pesan'];
    $tipe_pesan  = $_SESSION['flash_undi']['tipe'];
    $baru_diundi = $tipe_pesan === 'success';
    unset($_SESSION['flash_undi']);
}

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

    // Post/Redirect/Get: refresh halaman tidak mengulang undian
    $_SESSION['flash_undi'] = ['pesan' => $pesan, 'tipe' => $tipe_pesan];
    header('Location: ' . $_SERVER['PHP_SELF'] . '?event_id=' . (int) $event_id);
    exit;
}

$hasil = [];
$nama_peserta = [];

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

    // Nama peserta untuk animasi pengacakan
    $stmt = $conn->prepare("SELECT nama FROM peserta WHERE event_id = ? ORDER BY nama ASC");
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $resNama = $stmt->get_result();
    while ($rn = $resNama->fetch_assoc()) {
        $nama_peserta[] = $rn['nama'];
    }
    $stmt->close();
}

// Dari tombol "Pengundian Cepat" di dashboard: langsung jalankan animasi
$auto_mulai = ($_GET['auto'] ?? '') === '1';

$page_title    = 'Pengundian';
$page_subtitle = 'Kelola pengundian Secret Santa SKARIGA';
$active_menu   = 'pengundian';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<style>
    /* ===== Overlay animasi pengundian ===== */
    #undi-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at 50% 30%, rgba(60, 20, 160, .96), rgba(10, 0, 50, .98));
        overflow: hidden;
    }

    #undi-overlay.show {
        display: flex;
        animation: undiFadeIn .35s ease both;
    }

    @keyframes undiFadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }

    .undi-box {
        position: relative;
        z-index: 2;
        width: min(460px, 90vw);
        text-align: center;
        color: #fff;
    }

    .undi-gift {
        font-size: 76px;
        line-height: 1;
        display: inline-block;
        animation: undiShake .6s ease-in-out infinite;
        transform-origin: 50% 90%;
    }

    @keyframes undiShake {
        0%, 100% { transform: rotate(0) scale(1); }
        20%      { transform: rotate(-12deg) scale(1.08); }
        40%      { transform: rotate(10deg) scale(1.08); }
        60%      { transform: rotate(-8deg) scale(1.04); }
        80%      { transform: rotate(6deg) scale(1.04); }
    }

    .undi-title {
        margin: 14px 0 4px;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: .3px;
    }

    .undi-sub {
        margin: 0 0 22px;
        font-size: 13px;
        opacity: .8;
    }

    .undi-slot {
        background: #fff;
        color: #1a0066;
        border-radius: 14px;
        padding: 6px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, .4);
    }

    .undi-slot-inner {
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px dashed #c9b8ff;
        border-radius: 10px;
        font-size: 24px;
        font-weight: 800;
        overflow: hidden;
        padding: 0 12px;
    }

    .undi-slot-inner span {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .undi-slot-inner.tick span {
        animation: undiPop .12s ease-out;
    }

    @keyframes undiPop {
        from { transform: translateY(-14px); opacity: .2; }
        to   { transform: translateY(0); opacity: 1; }
    }

    .undi-slot-inner.done {
        border-style: solid;
        border-color: #34c759;
        color: #1e6b34;
        background: #eafbee;
    }

    .undi-progress {
        margin-top: 20px;
        height: 8px;
        background: rgba(255, 255, 255, .18);
        border-radius: 99px;
        overflow: hidden;
    }

    .undi-progress-bar {
        height: 100%;
        width: 0;
        background: linear-gradient(90deg, #ffd34d, #ff6b6b);
        border-radius: 99px;
    }

    .undi-status {
        margin-top: 12px;
        font-size: 13px;
        opacity: .85;
        min-height: 18px;
    }

    /* Salju / kilau latar overlay */
    .undi-flake {
        position: absolute;
        top: -20px;
        color: #fff;
        opacity: .7;
        pointer-events: none;
        animation: undiFall linear infinite;
    }

    @keyframes undiFall {
        to { transform: translateY(110vh) rotate(360deg); }
    }

    /* ===== Reveal hasil ===== */
    .undi-row-anim {
        opacity: 0;
        transform: translateY(10px);
        animation: undiRow .5s ease forwards;
    }

    @keyframes undiRow {
        to { opacity: 1; transform: translateY(0); }
    }

    .undi-arrow {
        display: inline-block;
        margin: 0 6px;
        color: #8a6dff;
    }

    /* Confetti */
    .confetti-piece {
        position: fixed;
        top: -12px;
        width: 9px;
        height: 14px;
        z-index: 9998;
        pointer-events: none;
        animation: confettiFall linear forwards;
    }

    @keyframes confettiFall {
        to { transform: translateY(105vh) rotate(720deg); opacity: .9; }
    }

    @media (prefers-reduced-motion: reduce) {
        .undi-gift, .undi-flake, .confetti-piece { animation: none; }
        .undi-row-anim { animation-duration: .01s; }
    }
</style>

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
        <form method="POST" id="form-undi">
            <input type="hidden" name="event_id" value="<?= (int) $event_id ?>">
            <button type="submit" id="btn-undi"
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
                        <tr style="border-bottom:1px solid #eee;"
                            <?php if ($baru_diundi): ?>
                            class="undi-row-anim"
                            data-delay="<?= $i * 120 ?>"
                            <?php endif; ?>>
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

    <!-- Overlay animasi pengundian -->
    <div id="undi-overlay" aria-live="polite" role="status">
        <div class="undi-box">
            <div class="undi-gift">🎁</div>
            <h3 class="undi-title" id="undi-title">Mengacak Peserta...</h3>
            <p class="undi-sub">Mohon tunggu, undian sedang berjalan</p>
            <div class="undi-slot">
                <div class="undi-slot-inner" id="undi-slot"><span>...</span></div>
            </div>
            <div class="undi-progress"><div class="undi-progress-bar" id="undi-bar"></div></div>
            <div class="undi-status" id="undi-status"></div>
        </div>
    </div>

    <script>
        (function () {
            var names      = <?= json_encode($nama_peserta, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            var sudahAda   = <?= $hasil ? 'true' : 'false' ?>;
            var autoMulai  = <?= $auto_mulai ? 'true' : 'false' ?>;
            var barusaja   = <?= $baru_diundi ? 'true' : 'false' ?>;

            var form    = document.getElementById('form-undi');
            var btn     = document.getElementById('btn-undi');
            var overlay = document.getElementById('undi-overlay');
            var slot    = document.getElementById('undi-slot');
            var bar     = document.getElementById('undi-bar');
            var title   = document.getElementById('undi-title');
            var status  = document.getElementById('undi-status');
            var running = false;

            if (!form) return;

            // Salju latar overlay
            (function makeFlakes() {
                var chars = ['❄', '✦', '❅', '•'];
                for (var i = 0; i < 26; i++) {
                    var f = document.createElement('span');
                    f.className = 'undi-flake';
                    f.textContent = chars[i % chars.length];
                    f.style.left = (Math.random() * 100) + '%';
                    f.style.fontSize = (10 + Math.random() * 16) + 'px';
                    f.style.animationDuration = (4 + Math.random() * 5) + 's';
                    f.style.animationDelay = (-Math.random() * 6) + 's';
                    overlay.appendChild(f);
                }
            })();

            function setSlot(text, tick) {
                slot.innerHTML = '';
                var s = document.createElement('span');
                s.textContent = text;
                slot.appendChild(s);
                if (tick) {
                    slot.classList.remove('tick');
                    void slot.offsetWidth;
                    slot.classList.add('tick');
                }
            }

            function mulaiUndi() {
                if (running) return;

                var konfirmasi = sudahAda
                    ? 'Hasil undian sebelumnya akan diganti. Lanjutkan?'
                    : 'Mulai pengundian sekarang?';
                if (!window.confirm(konfirmasi)) return;

                // Peserta kurang dari 2: biarkan server menampilkan pesan error
                if (names.length < 2) {
                    form.submit();
                    return;
                }

                running = true;
                btn.disabled = true;
                overlay.classList.add('show');
                slot.classList.remove('done');
                title.textContent = 'Mengacak Peserta...';
                status.textContent = '';
                bar.style.transition = 'none';
                bar.style.width = '0';

                var durasi = 3800;           // total lama animasi (ms)
                var mulai  = Date.now();
                var terakhir = -1;

                // animasi progress bar
                requestAnimationFrame(function () {
                    bar.style.transition = 'width ' + durasi + 'ms linear';
                    bar.style.width = '100%';
                });

                // nama berputar, makin lama makin lambat
                function putar() {
                    var lewat = Date.now() - mulai;
                    if (lewat >= durasi) {
                        selesai();
                        return;
                    }
                    var idx;
                    do { idx = Math.floor(Math.random() * names.length); }
                    while (names.length > 1 && idx === terakhir);
                    terakhir = idx;
                    setSlot(names[idx], true);

                    var progres = lewat / durasi;                 // 0..1
                    status.textContent = 'Memasangkan pemberi & penerima...';
                    var jeda = 60 + progres * progres * 340;      // 60ms -> 400ms
                    setTimeout(putar, jeda);
                }

                function selesai() {
                    title.textContent = 'Undian Selesai! 🎉';
                    slot.classList.add('done');
                    setSlot('Menyimpan hasil...', false);
                    status.textContent = '';
                    // beri jeda singkat agar "Selesai" terlihat, lalu kirim form
                    setTimeout(function () {
                        HTMLFormElement.prototype.submit.call(form);
                    }, 700);
                }

                putar();
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                mulaiUndi();
            });

            // Delay bertahap untuk baris hasil baru
            var rows = document.querySelectorAll('.undi-row-anim');
            rows.forEach(function (r) {
                r.style.animationDelay = (r.getAttribute('data-delay') || 0) + 'ms';
            });

            // Confetti setelah undian berhasil
            function confetti() {
                var warna = ['#ff6b6b', '#ffd34d', '#4dd2ff', '#8a6dff', '#34c759', '#ff9ff3'];
                for (var i = 0; i < 90; i++) {
                    var c = document.createElement('div');
                    c.className = 'confetti-piece';
                    c.style.left = (Math.random() * 100) + 'vw';
                    c.style.background = warna[i % warna.length];
                    c.style.animationDuration = (2.2 + Math.random() * 2.4) + 's';
                    c.style.animationDelay = (Math.random() * 0.8) + 's';
                    c.style.transform = 'rotate(' + (Math.random() * 360) + 'deg)';
                    document.body.appendChild(c);
                    (function (el) {
                        setTimeout(function () { el.remove(); }, 6000);
                    })(c);
                }
            }
            if (barusaja) confetti();

            // Mulai otomatis dari "Pengundian Cepat" di dashboard
            if (autoMulai) {
                if (window.history && history.replaceState) {
                    var url = new URL(window.location.href);
                    url.searchParams.delete('auto');
                    history.replaceState(null, '', url.toString());
                }
                setTimeout(mulaiUndi, 250);
            }
        })();
    </script>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>