<?php
function h($str)
{
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

function get_events(mysqli $conn): array
{
    $events = [];
    $result = $conn->query("SELECT id, nama_event, tanggal_event FROM events ORDER BY tanggal_event DESC, id DESC");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }

    return $events;
}


function resolve_event_id(mysqli $conn, $requested): ?int
{
    if ($requested !== null && ctype_digit((string) $requested)) {
        return (int) $requested;
    }

    $events = get_events($conn);

    return $events ? (int) $events[0]['id'] : null;
}


function set_flash(string $type, string $message): void
{
    $_SESSION['flash_' . $type] = $message;
}

function render_flash(): void
{
    if (!empty($_SESSION['flash_success'])) {
        echo '<div class="alert alert-success">' . h($_SESSION['flash_success']) . '</div>';
        unset($_SESSION['flash_success']);
    }

    if (!empty($_SESSION['flash_error'])) {
        echo '<div class="alert alert-error">' . h($_SESSION['flash_error']) . '</div>';
        unset($_SESSION['flash_error']);
    }
}

function tanggal_id($date, bool $short = false): string
{
    if (!$date) {
        return '-';
    }

    $bulan = $short
        ? ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
        : ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $ts = strtotime($date);

    return date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

function rupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function get_max_peserta(mysqli $conn): int
{
    $row = $conn->query("SELECT max_peserta FROM pengaturan LIMIT 1")->fetch_assoc();

    return $row ? (int) $row['max_peserta'] : 10;
}

function get_events_for_user(mysqli $conn, int $user_id): array
{
    $stmt = $conn->prepare(
        "SELECT e.id, e.nama_event, e.deskripsi, e.budget, e.biaya_pendaftaran, e.tanggal_event, e.status,
                (SELECT COUNT(*) FROM peserta px WHERE px.event_id = e.id AND px.status = 'aktif') AS jumlah,
                (SELECT px.id FROM peserta px WHERE px.event_id = e.id AND px.user_id = ? LIMIT 1) AS peserta_id
         FROM events e
         WHERE e.status <> 'draft'
         ORDER BY (e.status = 'selesai') ASC, e.tanggal_event ASC, e.id DESC"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}

function get_user_events(mysqli $conn, int $user_id): array
{
    $stmt = $conn->prepare(
        "SELECT e.id, e.nama_event, e.deskripsi, e.budget, e.tanggal_event, e.status,
                p.id AS peserta_id, p.created_at AS tanggal_ikut
         FROM peserta p
         JOIN events e ON e.id = p.event_id
         WHERE p.user_id = ?
         ORDER BY (e.status = 'selesai') ASC, e.tanggal_event ASC, e.id DESC"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}

function event_palette(int $id): string
{
    $palettes = ['pal-merah', 'pal-biru', 'pal-hijau', 'pal-ungu', 'pal-emas'];

    return $palettes[$id % count($palettes)];
}
function validasi_user(mysqli $conn, string $nama, string $email, string $password, string $role, int $ignore_id = 0, bool $password_wajib = true): string
{
    if ($nama === '' || $email === '') {
        return 'Nama dan email wajib diisi.';
    }

    if (mb_strlen($nama) < 2 || mb_strlen($nama) > 100) {
        return 'Nama harus 2 sampai 100 karakter.';
    }

    if (mb_strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Format email tidak valid (maksimal 100 karakter).';
    }

    if (!in_array($role, ['peserta', 'admin'], true)) {
        return 'Role tidak valid.';
    }

    if ($password_wajib || $password !== '') {
        if (strlen($password) < 6) {
            return 'Password minimal 6 karakter.';
        }

        if (strlen($password) > 72) {
            return 'Password maksimal 72 karakter.';
        }
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
    $stmt->bind_param('si', $email, $ignore_id);
    $stmt->execute();
    $dobel = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($dobel) {
        return 'Email sudah dipakai akun lain.';
    }

    return '';
}


function catat_riwayat(mysqli $conn, string $modul, string $aksi, string $keterangan, ?int $event_id = null): void
{
    $user_id = (int) ($_SESSION['user_id'] ?? 0) ?: null;
    $event_id = $event_id ?: null;
    $keterangan = mb_substr($keterangan, 0, 255);

    try {
        $stmt = $conn->prepare(
            "INSERT INTO riwayat (user_id, event_id, modul, aksi, keterangan) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('iisss', $user_id, $event_id, $modul, $aksi, $keterangan);
        $stmt->execute();
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        
    }
}

/* ---------- Teks kaya (deskripsi event) ---------- */

function rich_style_aman(string $tag, string $style): string
{
    $hasil = [];

    foreach (explode(';', $style) as $deklarasi) {
        $bagian = explode(':', $deklarasi, 2);
        if (count($bagian) < 2) {
            continue;
        }

        $prop  = strtolower(trim($bagian[0]));
        $nilai = strtolower(trim($bagian[1]));

        if ($prop === 'background-color'
            && preg_match('/^(transparent|#[0-9a-f]{3,8}|rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*[0-9.]+\s*)?\))$/', $nilai)) {
            $hasil[] = 'background-color: ' . $nilai;
        } elseif ($prop === 'list-style-type'
            && in_array($tag, ['ul', 'ol'], true)
            && in_array($nilai, ['disc', 'circle', 'square', 'decimal', 'lower-alpha', 'upper-alpha', 'lower-roman', 'upper-roman'], true)) {
            $hasil[] = 'list-style-type: ' . $nilai;
        }
    }

    return implode('; ', $hasil);
}

function rich_bersihkan_node(DOMNode $induk): void
{
    $boleh = ['b', 'strong', 'i', 'em', 'u', 'ul', 'ol', 'li', 'br', 'p', 'div', 'span', 'mark'];
    $buang = ['script', 'style', 'iframe', 'object', 'embed', 'link', 'meta', 'svg', 'math'];

    foreach (iterator_to_array($induk->childNodes) as $anak) {
        if ($anak->nodeType === XML_TEXT_NODE) {
            continue;
        }
        if ($anak->nodeType !== XML_ELEMENT_NODE) {
            $induk->removeChild($anak);
            continue;
        }

        $tag = strtolower($anak->nodeName);

        if (in_array($tag, $buang, true)) {
            $induk->removeChild($anak);
            continue;
        }

        rich_bersihkan_node($anak);

        // tag di luar daftar aman: buang tag-nya, isinya dipertahankan
        if (!in_array($tag, $boleh, true)) {
            while ($anak->firstChild) {
                $induk->insertBefore($anak->firstChild, $anak);
            }
            $induk->removeChild($anak);
            continue;
        }

        // semua atribut dibuang, kecuali style yang lolos daftar aman
        $style     = $anak->getAttribute('style');
        $nama_attr = [];
        foreach ($anak->attributes as $attr) {
            $nama_attr[] = $attr->nodeName;
        }
        foreach ($nama_attr as $nama) {
            $anak->removeAttribute($nama);
        }

        $aman = rich_style_aman($tag, $style);
        if ($aman !== '') {
            $anak->setAttribute('style', $aman);
        }
    }
}

// Bersihkan HTML dari editor sebelum disimpan / ditampilkan
function bersihkan_html_rich(?string $html): string
{
    $html = trim(str_replace("\u{200B}", '', (string) $html));
    if ($html === '') {
        return '';
    }

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $akar = $dom->getElementsByTagName('div')->item(0);
    if (!$akar) {
        return '';
    }

    rich_bersihkan_node($akar);

    $hasil = '';
    foreach ($akar->childNodes as $anak) {
        $hasil .= $dom->saveHTML($anak);
    }
    $hasil = trim($hasil);

    // isinya kosong (hanya <br>/<div> kosong) -> simpan sebagai string kosong
    if (trim(strip_tags($hasil)) === '' && stripos($hasil, '<li') === false) {
        return '';
    }

    return $hasil;
}

// Untuk ditampilkan penuh (halaman review). Teks lama tanpa tag tetap aman.
function tampilkan_rich(?string $teks): string
{
    $teks = trim((string) $teks);
    if ($teks === '') {
        return '';
    }

    if (!preg_match('/<(?:b|strong|i|em|u|ul|ol|li|br|p|div|span|mark)\b/i', $teks)) {
        return nl2br(h($teks));
    }

    return bersihkan_html_rich($teks);
}

// Ringkasan teks polos untuk tabel dan kartu (tanpa format)
function rich_ringkas(?string $teks, int $maks = 80): string
{
    $teks = (string) $teks;
    $teks = preg_replace('/<\/(p|div|li)>|<br\s*\/?>/i', ' ', $teks);
    $teks = html_entity_decode(strip_tags($teks), ENT_QUOTES, 'UTF-8');
    $teks = trim(preg_replace('/\s+/u', ' ', $teks));

    return mb_strimwidth($teks, 0, $maks, '…', 'UTF-8');
}

// Deskripsi berformat untuk sel tabel: format tetap, baris dilebur jadi satu alur
function rich_ringkas_html(?string $teks): string
{
    $html = tampilkan_rich($teks);
    return preg_replace('/<br\s*\/?>/i', ' ', $html);
}

?>