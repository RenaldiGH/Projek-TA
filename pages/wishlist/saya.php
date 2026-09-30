<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_peserta();

$user_id = (int) $_SESSION['user_id'];

$my_events = get_user_events($conn, $user_id);

$event_id   = 0;
$peserta_id = 0;

if ($my_events) {
    $requested = (int) ($_GET['event_id'] ?? $_POST['event_id'] ?? 0);
    $picked    = $my_events[0];

    foreach ($my_events as $e) {
        if ((int) $e['id'] === $requested) {
            $picked = $e;
            break;
        }
    }

    $event_id   = (int) $picked['id'];
    $peserta_id = (int) $picked['peserta_id'];
}

$self = base_url('pages/wishlist/saya.php?event_id=' . $event_id);

function ambil_item_wishlist(mysqli $conn, int $id, int $peserta_id): ?array
{
    $stmt = $conn->prepare("SELECT * FROM wishlist WHERE id = ? AND peserta_id = ?");
    $stmt->bind_param('ii', $id, $peserta_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row ?: null;
}

$error = '';
$form  = [
    'id'             => 0,
    'nama_barang'    => '',
    'kategori'       => '',
    'estimasi_harga' => '',
    'deskripsi'      => '',
    'link_referensi' => '',
];

if ($peserta_id && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $aksi = $_POST['aksi'] ?? '';
    $id   = (int) ($_POST['id'] ?? 0);

    if ($aksi === 'hapus') {

        $item = ambil_item_wishlist($conn, $id, $peserta_id);

        if (!$item) {
            set_flash('error', 'Wishlist tidak ditemukan.');
        } elseif ($item['status'] === 'dipilih') {
            set_flash('error', 'Barang ini sudah dipilih Secret Santa-mu, tidak bisa dihapus.');
        } else {
            $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND peserta_id = ?");
            $stmt->bind_param('ii', $id, $peserta_id);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Wishlist berhasil dihapus.');
        }

        header('Location: ' . $self);
        exit;
    }

    if ($aksi === 'tambah' || $aksi === 'ubah') {

        $form = [
            'id'             => $id,
            'nama_barang'    => trim($_POST['nama_barang'] ?? ''),
            'kategori'       => trim($_POST['kategori'] ?? ''),
            'estimasi_harga' => trim($_POST['estimasi_harga'] ?? ''),
            'deskripsi'      => trim($_POST['deskripsi'] ?? ''),
            'link_referensi' => trim($_POST['link_referensi'] ?? ''),
        ];

        if ($form['nama_barang'] === '') {
            $error = 'Nama barang wajib diisi.';
        } elseif (!is_numeric($form['estimasi_harga']) || $form['estimasi_harga'] <= 0) {
            $error = 'Estimasi harga harus berupa angka dan tidak boleh nol.';
        } elseif ($form['link_referensi'] !== '' && !preg_match('#^https?://#i', $form['link_referensi'])) {
            $error = 'Link referensi harus diawali http:// atau https://';
        } elseif ($aksi === 'tambah') {

            $stmt = $conn->prepare(
                "INSERT INTO wishlist (peserta_id, nama_barang, kategori, estimasi_harga, deskripsi, link_referensi)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                'issdss',
                $peserta_id,
                $form['nama_barang'],
                $form['kategori'],
                $form['estimasi_harga'],
                $form['deskripsi'],
                $form['link_referensi']
            );
            $stmt->execute();
            $stmt->close();

            set_flash('success', 'Wishlist berhasil ditambahkan.');
            header('Location: ' . $self);
            exit;

        } else {

            $item = ambil_item_wishlist($conn, $id, $peserta_id);

            if (!$item) {
                set_flash('error', 'Wishlist tidak ditemukan.');
            } elseif ($item['status'] === 'dipilih') {
                set_flash('error', 'Barang ini sudah dipilih Secret Santa-mu, tidak bisa diubah.');
            } else {
                $stmt = $conn->prepare(
                    "UPDATE wishlist
                     SET nama_barang = ?, kategori = ?, estimasi_harga = ?, deskripsi = ?, link_referensi = ?
                     WHERE id = ? AND peserta_id = ?"
                );
                $stmt->bind_param(
                    'ssdssii',
                    $form['nama_barang'],
                    $form['kategori'],
                    $form['estimasi_harga'],
                    $form['deskripsi'],
                    $form['link_referensi'],
                    $id,
                    $peserta_id
                );
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'Wishlist berhasil diperbarui.');
            }

            header('Location: ' . $self);
            exit;
        }
    }
}


if ($peserta_id && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit'])) {
    $item = ambil_item_wishlist($conn, (int) $_GET['edit'], $peserta_id);

    if ($item && $item['status'] !== 'dipilih') {
        $form = [
            'id'             => (int) $item['id'],
            'nama_barang'    => $item['nama_barang'],
            'kategori'       => $item['kategori'],
            'estimasi_harga' => $item['estimasi_harga'] !== null ? (string) (int) $item['estimasi_harga'] : '',
            'deskripsi'      => $item['deskripsi'],
            'link_referensi' => $item['link_referensi'],
        ];
    }
}

$sedang_edit = $form['id'] > 0;

$wishlist = [];

if ($peserta_id) {
    $stmt = $conn->prepare(
        "SELECT id, nama_barang, kategori, estimasi_harga, deskripsi, link_referensi, status
         FROM wishlist
         WHERE peserta_id = ?
         ORDER BY id DESC"
    );
    $stmt->bind_param('i', $peserta_id);
    $stmt->execute();
    $wishlist = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$page_title    = 'Wishlist Saya';
$page_subtitle = 'Tulis barang yang kamu harapkan dari Secret Santa-mu.';
$active_menu   = 'wishlist';

require_once __DIR__ . '/../../includes/user_layout_top.php';
?>

<?php if (!$my_events): ?>

    <div class="panel">
        <div class="empty">
            <h3>Kamu Belum Ikut Event</h3>
            <p>Ikut minimal satu event dulu untuk mengisi wishlist bosq.</p>
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

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= h($error) ?></div>
    <?php endif; ?>

    <section class="panel">
        <h2><?= $sedang_edit ? 'Edit Wishlist' : 'Tambah Wishlist' ?></h2>

        <form method="POST" action="<?= h($self) ?>">
            <input type="hidden" name="event_id" value="<?= $event_id ?>">
            <input type="hidden" name="aksi" value="<?= $sedang_edit ? 'ubah' : 'tambah' ?>">
            <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="nama_barang">Nama Barang</label>
                    <input type="text" id="nama_barang" name="nama_barang" value="<?= h($form['nama_barang']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <input type="text" id="kategori" name="kategori" value="<?= h($form['kategori']) ?>" placeholder="Beauty, Lifestyle, dsb.">
                </div>

                <div class="form-group">
                    <label for="estimasi_harga">Estimasi Harga (Rp)</label>
                    <input type="number" id="estimasi_harga" name="estimasi_harga" value="<?= h($form['estimasi_harga']) ?>" min="1" step="1000" required>
                </div>

                <div class="form-group">
                    <label for="link_referensi">Link Referensi (opsional)</label>
                    <input type="text" id="link_referensi" name="link_referensi" value="<?= h($form['link_referensi']) ?>" placeholder="https://...">
                </div>

                <div class="form-group full">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3"><?= h($form['deskripsi']) ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $sedang_edit ? 'Simpan Perubahan' : 'Tambah ke Wishlist' ?></button>
                <?php if ($sedang_edit): ?>
                    <a href="<?= h($self) ?>" class="btn btn-secondary">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="panel">
        <h2>Daftar Wishlist (<?= count($wishlist) ?>)</h2>

        <?php if (!$wishlist): ?>

            <div class="empty">
                <h3>Belum Ada Wishlist</h3>
                <p>Tambahkan barang pertamamu lewat form di atas.</p>
            </div>

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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wishlist as $i => $w): ?>
                            <?php $terkunci = $w['status'] === 'dipilih'; ?>
                            <tr>
                                <td><?= $i + 1 ?>.</td>
                                <td>
                                    <strong><?= h($w['nama_barang']) ?></strong>
                                    <?php if (!empty($w['deskripsi'])): ?>
                                        <br><small style="color:#6b7280;"><?= h($w['deskripsi']) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($w['link_referensi']) && preg_match('#^https?://#i', $w['link_referensi'])): ?>
                                        <br><a href="<?= h($w['link_referensi']) ?>" target="_blank" rel="noopener noreferrer" style="font-size:12px;">Lihat referensi</a>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($w['kategori'] ?: '-') ?></td>
                                <td><?= h(rupiah($w['estimasi_harga'])) ?></td>
                                <td>
                                    <span class="tag <?= $terkunci ? 'ok' : '' ?>">
                                        <?= $terkunci ? 'Dipilih' : 'Belum dipilih' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($terkunci): ?>
                                        <small style="color:#6b7280;">Terkunci</small>
                                    <?php else: ?>
                                        <div class="aksi">
                                            <a href="<?= h(base_url('pages/wishlist/saya.php?event_id=' . $event_id . '&edit=' . (int) $w['id'])) ?>" class="btn btn-secondary btn-small">Edit</a>
                                            <form method="POST" action="<?= h($self) ?>">
                                                <input type="hidden" name="event_id" value="<?= $event_id ?>">
                                                <input type="hidden" name="aksi" value="hapus">
                                                <input type="hidden" name="id" value="<?= (int) $w['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-small"
                                                        onclick="return confirm('Yakin ingin menghapus wishlist ini?');">Hapus</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </section>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/user_layout_bottom.php'; ?>