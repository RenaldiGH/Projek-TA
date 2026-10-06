<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$page_title = 'Laporan';
$page_subtitle = 'Lihat laporan data pengundian';
$active_menu = 'laporan';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<div class="table-card">

    <!-- Header -->
    <div style="
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:20px;
    ">

        <div>
            <h2 style="margin:0;">Laporan</h2>
            <p style="margin:5px 0 0;">
                Lihat laporan data pengundian
            </p>
        </div>

        <button
            type="button"
            class="btn-add"
            disabled
            style="opacity:0.6; cursor:not-allowed;"
        >
            ⬇ Unduh Pdf
        </button>

    </div>


    <!-- Pilih Event -->
    <div class="filter-bar">

        <div class="filter-group">

            <label for="event_id">
                Pilih Event
            </label>

            <select id="event_id" disabled>

                <option>
                    Sistem Secret Santa SKARIGA 2025
                </option>

            </select>

        </div>

    </div>


    <!-- Tabel Kosong -->
    <table class="data-table">

        <thead>
            <tr>

                <th style="width:70px;">
                    No.
                </th>

                <th>
                    Pemberi
                </th>

                <th>
                    Penerima
                </th>

                <th>
                    Tanggal Undi
                </th>

            </tr>
        </thead>

        <tbody>

            <!-- Data pengundian nanti masuk di sini -->

        </tbody>

    </table>


    <!-- Empty State -->
    <div class="empty-state">

        <div style="
            font-size:80px;
            line-height:1;
            margin-bottom:15px;
        ">
            ↓
        </div>

        <h3>
            Belum Ada Laporan
        </h3>

        <p>
            Lakukan pengundian untuk melihat laporan
        </p>

    </div>

</div>

<?php
require_once __DIR__ . '/../../includes/admin_layout_bottom.php';
?>