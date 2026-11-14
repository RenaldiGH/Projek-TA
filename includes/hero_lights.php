<?php
/*
 * Pembatas bawah hero: tepi biru bergelombang yang menutupi area putih,
 * dengan tiga untai lampu bohlam hangat yang saling bersilang.
 * Setiap untai = deretan lengkungan (swag): [x_awal, lebar, y_ujung, kedalaman]
 */
$W = 1440;

// Tepi biru (untai 3 berada tepat di garis batas)
$edge_count = 8;
$edge_w     = $W / $edge_count;
$edge_y     = 40;
$edge_sag   = 26;

// Definisi untai: jumlah lengkungan, x awal, y ujung, kedalaman
$strings = [
    ['count' => 3, 'x0' => 0,    'w' => 480, 'y' => 6,  'sag' => 28],  // untai atas (lebar)
    ['count' => 4, 'x0' => -120, 'w' => 390, 'y' => 14, 'sag' => 24],  // untai tengah (bersilang)
    ['count' => 8, 'x0' => 0,    'w' => $edge_w, 'y' => $edge_y, 'sag' => $edge_sag], // untai garis batas
];

// Path biru
$fill = 'M0 0 H' . $W . ' V' . $edge_y;
for ($i = $edge_count - 1; $i >= 0; $i--) {
    $x0 = $i * $edge_w;
    $x1 = $x0 + $edge_w;
    $fill .= sprintf(' Q%.2f %.2f %.2f %d', ($x0 + $x1) / 2, $edge_y + 2 * $edge_sag, $x0, $edge_y);
}
$fill .= ' Z';

$wires = [];
$bulbs = [];
foreach ($strings as $si => $s) {
    $d = '';
    for ($i = 0; $i < $s['count']; $i++) {
        $x0 = $s['x0'] + $i * $s['w'];
        $x1 = $x0 + $s['w'];
        $d .= sprintf('M%.2f %d Q%.2f %.2f %.2f %d ', $x0, $s['y'], ($x0 + $x1) / 2, $s['y'] + 2 * $s['sag'], $x1, $s['y']);

        $per = max(3, (int) round($s['w'] / 48));
        for ($k = 1; $k < $per; $k++) {
            $t = $k / $per;
            $x = $x0 + $t * $s['w'];
            if ($x < 0 || $x > $W) {
                continue;
            }
            $bulbs[] = [$x / $W * 100, $s['y'] + 4 * $s['sag'] * $t * (1 - $t) + 4];
        }
    }
    $wires[] = trim($d);
}

// Kilau bintang kecil (posisi tetap, % dan px)
$sparkles = [[6, 26], [17, 52], [29, 20], [38, 58], [49, 30], [58, 62], [69, 24], [78, 50], [88, 22], [95, 56]];
?>
<div class="hero-divider" aria-hidden="true">

    <svg viewBox="0 0 <?= $W ?> 84" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="<?= $fill ?>" fill="#050029"/>
        <?php foreach ($wires as $d): ?>
            <path d="<?= $d ?>" fill="none" stroke="#e8d3a0" stroke-opacity="0.85" stroke-width="1.2"/>
        <?php endforeach; ?>
    </svg>

    <?php foreach ($bulbs as $n => [$left, $top]): ?>
        <span class="hero-bulb" style="left:<?= number_format($left, 3, '.', '') ?>%;top:<?= number_format($top, 1, '.', '') ?>px;animation-delay:-<?= number_format(($n * 0.41) % 3, 2, '.', '') ?>s"></span>
    <?php endforeach; ?>

    <?php foreach ($sparkles as $n => [$left, $top]): ?>
        <span class="hero-sparkle" style="left:<?= $left ?>%;top:<?= $top ?>px;animation-delay:-<?= number_format(($n * 0.63) % 3, 2, '.', '') ?>s"></span>
    <?php endforeach; ?>

</div>
