<?php
// total_kontrak_karyawan.php
// Terhubung ke database dan menampilkan total karyawan,
// dengan filter Bulan, Tahun, Unit Kerja, dan pilihan Kontrak (SPK / SPKWT / Semua).
// Server: 127.0.0.1, Database: karyawan_rs

// -------------------------
// Koneksi database
// -------------------------
include 'config.php';


// -------------------------
// Mapping unit => tabel database (kode lama Anda tetap dipertahankan)
// -------------------------
$tabel_unit = [
    "Kantor Direksi PT.TDM"            => "karyawan_kantor_pttdm",
    "Klinik Pratama Batang Serangan"   => "karyawan_klinik_bts",
    "Klinik Rambutan Deli Binjai"      => "karyawan_klinik_rdb",
    "Klinik Pratama Garuda"            => "karyawan_klinik_grd",
    "Rs Tanjung Selamat"               => "karyawan_rsts"
];

// -------------------------
// Deteksi apakah form sudah disubmit / ada filter aktif
// Kita anggap query hanya dijalankan jika ada parameter GET (user menekan Filter)
// -------------------------
$is_filtered = count($_GET) > 0; // awal masuk tanpa parameter -> false

// -------------------------
// Ambil filter dari GET (tetap ambil untuk menjaga seleksi form)
// -------------------------
$bulan  = isset($_GET['bulan']) && $_GET['bulan'] !== '' ? (int)$_GET['bulan'] : 0;
$tahun  = isset($_GET['tahun']) && $_GET['tahun'] !== '' ? (int)$_GET['tahun'] : 0;
$unit   = isset($_GET['unit']) ? trim($_GET['unit']) : '';
// pilihan kontrak: 'all' (default), 'spk', 'spkwt'
$kontrak = isset($_GET['kontrak']) ? strtolower(trim($_GET['kontrak'])) : 'all';
if (!in_array($kontrak, ['all','spk','spkwt'])) $kontrak = 'all';

// -------------------------
// Helper: kondisi tanggal_input
// -------------------------
function whereTanggalInput($bulan, $tahun) {
    $conds = [];
    if ($bulan > 0) $conds[] = "MONTH(tanggal_input) = ". intval($bulan);
    if ($tahun > 0)  $conds[] = "YEAR(tanggal_input) = ". intval($tahun);
    if (count($conds) === 0) return "1=1";
    return implode(" AND ", $conds);
}

// -------------------------
// Fungsi: COUNT total dari sebuah tabel (untuk ringkasan spk/spkwt)
// -------------------------
function total_from_table($conn, $table, $bulan=0, $tahun=0, $unit_filter = '') {
    $table = preg_replace('/[^a-z0-9_]/i','', $table);
    $where = whereTanggalInput($bulan, $tahun);
    if ($unit_filter !== '') {
        $where .= " AND unit_kerja = '" . $conn->real_escape_string($unit_filter) . "'";
    }
    $sql = "SELECT COUNT(DISTINCT nik) AS total FROM $table WHERE $where";
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) return (int)$row['total'];
    return 0;
}

// -------------------------
// Helper: normalisasi nama unit => canonical name (gunakan mapping $tabel_unit)
// Menghapus tanda baca, lower-case, dan cocokkan jika ada substring match.
// -------------------------
function normalize_string_for_compare($s) {
    $s = strtolower(trim($s));
    // ubah multiple whitespace, hapus tanda baca
    $s = preg_replace('/[^a-z0-9]+/','', $s);
    return $s;
}
function canonicalize_unit($raw_name, $tabel_unit) {
    $r = trim($raw_name);
    if ($r === '' || $r === null) return '(Tidak diisi)';
    // jika persis sama dengan salah satu mapping key
    foreach ($tabel_unit as $canon => $tbl) {
        if (strcasecmp(trim($canon), $r) === 0) return $canon;
    }
    // normalisasi dan cari kecocokan heuristik
    $norm = normalize_string_for_compare($r);
    foreach ($tabel_unit as $canon => $tbl) {
        $norm_canon = normalize_string_for_compare($canon);
        // jika normalized input mengandung normalized canon atau sebaliknya -> anggap sama
        if ($norm !== '' && (strpos($norm, $norm_canon) !== false || strpos($norm_canon, $norm) !== false)) {
            return $canon;
        }
    }
    // beberapa aturan khusus singkat (opsional)
    // contoh: "rumah sakit ..." => mapping "Rs ..."
    if (stripos($r, 'rumah') !== false && stripos($r, 'tanjung') !== false && stripos($r, 'selamat') !== false) {
        if (isset($tabel_unit['Rs Tanjung Selamat'])) return 'Rs Tanjung Selamat';
    }
    // kalau tidak cocok, kembalikan nama original (trim)
    return $r;
}

// Siapkan variabel hasil (default kosong / nol)
$data_total = [];
$grand_total = 0;
$total_spk = 0;
$total_spkwt = 0;

// -------------------------
// Hanya jalankan query jika user sudah menggunakan filter (is_filtered true)
// Jika is_filtered false -> jangan panggil query sama sekali, tampilkan kosong/pesan
// -------------------------
if ($is_filtered) {
    // Ambil data untuk tabel utama sesuai pilihan kontrak
    if ($kontrak === 'spk' || $kontrak === 'spkwt') {
        // Bila user memilih SPK atau SPKWT, ambil langsung dari tabel spk / spkwt
        $tbl = ($kontrak === 'spk') ? 'spk' : 'spkwt';
        if ($unit) {
            $unit_esc = $conn->real_escape_string($unit);
            $sql = "SELECT '" . $unit_esc . "' AS unit_kerja, COUNT(DISTINCT nik) AS total_kontrak_karyawan
                    FROM $tbl
                    WHERE " . whereTanggalInput($bulan, $tahun) . " AND unit_kerja = '{$unit_esc}'";
            $res = $conn->query($sql);
            if ($res && $row = $res->fetch_assoc()) {
                $data_total[] = $row;
            } else {
                $data_total[] = ['unit_kerja' => $unit, 'total_kontrak_karyawan' => 0];
            }
        } else {
            $sql = "SELECT IFNULL(NULLIF(TRIM(unit_kerja),''),'(Tidak diisi)') AS unit_kerja, 
                           COUNT(DISTINCT nik) AS total_kontrak_karyawan
                    FROM $tbl
                    WHERE " . whereTanggalInput($bulan, $tahun) . "
                    GROUP BY unit_kerja
                    ORDER BY total_kontrak_karyawan DESC, unit_kerja ASC";
            $res = $conn->query($sql);
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $data_total[] = $row;
                }
            }
            // fallback: tampilkan mapping dengan nol bila tidak ada data
            if (count($data_total) === 0) {
                foreach ($tabel_unit as $nama_unit => $tblmap) {
                    $data_total[] = ['unit_kerja' => $nama_unit, 'total_kontrak_karyawan' => 0];
                }
            }
        }
    } else {
        // kontrak == 'all' : ambil mentah dari spk + spkwt, normalkan nama unit,
        // lalu hitung DISTINCT nik per canonical unit agar tidak terpecah karena varian nama.
        $where = whereTanggalInput($bulan, $tahun);

        if ($unit) {
            // jika user memilih satu unit, kita ambil data mentah untuk unit itu (dengan kemungkinan ejaan berbeda),
            // kemudian hitung distinct nik setelah canonicalize.
            $unit_esc = $conn->real_escape_string($unit);
            $sql = "SELECT unit_kerja, nik FROM (
                        SELECT unit_kerja, nik, tanggal_input FROM spk WHERE $where
                        UNION ALL
                        SELECT unit_kerja, nik, tanggal_input FROM spkwt WHERE $where
                    ) AS u
                    WHERE unit_kerja IS NOT NULL";
            $res = $conn->query($sql);
            $unit_niks = []; // canonical => [nik => true]
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $raw_unit = trim($row['unit_kerja'] ?? '');
                    $canonical = canonicalize_unit($raw_unit, $tabel_unit);
                    // hanya hitung jika canonical sama dengan unit pilihan (bisa berbeda ejaan)
                    // lakukan perbandingan normalized untuk memutuskan apakah termasuk unit yang dipilih
                    $sel_norm = normalize_string_for_compare($unit);
                    $canon_norm = normalize_string_for_compare($canonical);
                    if ($sel_norm === '' || strpos($canon_norm, $sel_norm) !== false || strpos($sel_norm, $canon_norm) !== false) {
                        $nik = trim($row['nik']);
                        if ($nik === '') continue;
                        if (!isset($unit_niks[$canonical])) $unit_niks[$canonical] = [];
                        $unit_niks[$canonical][$nik] = true;
                    }
                }
            }
            // siapkan data_total; utamakan menampilkan nama unit pilihan (canonical) jika ada
            if (count($unit_niks) === 0) {
                $data_total[] = ['unit_kerja' => $unit, 'total_kontrak_karyawan' => 0];
            } else {
                foreach ($unit_niks as $canon => $nset) {
                    $data_total[] = ['unit_kerja' => $canon, 'total_kontrak_karyawan' => count($nset)];
                }
            }
        } else {
            // Semua unit: ambil mentah semua baris lalu agregasi distinct nik per canonical unit
            $sql = "SELECT unit_kerja, nik FROM (
                        SELECT unit_kerja, nik, tanggal_input FROM spk
                        UNION ALL
                        SELECT unit_kerja, nik, tanggal_input FROM spkwt
                    ) AS u
                    WHERE $where";
            $res = $conn->query($sql);
            $unit_niks = []; // canonical => [nik => true]
            $others_present = []; // untuk unit yang tidak cocok mapping

            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $raw_unit = trim($row['unit_kerja'] ?? '');
                    $canonical = canonicalize_unit($raw_unit, $tabel_unit);
                    $nik = trim($row['nik']);
                    if ($nik === '') continue;
                    if (!isset($unit_niks[$canonical])) $unit_niks[$canonical] = [];
                    $unit_niks[$canonical][$nik] = true;
                    // catat jika canonical tidak ada di mapping (untuk ditampilkan juga)
                    if (!array_key_exists($canonical, $tabel_unit)) {
                        $others_present[$canonical] = true;
                    }
                }
            }

            // Pastikan semua unit mapping tampil: jika tidak ada, beri 0
            foreach ($tabel_unit as $nama_unit => $_) {
                if (isset($unit_niks[$nama_unit])) {
                    $data_total[] = ['unit_kerja' => $nama_unit, 'total_kontrak_karyawan' => count($unit_niks[$nama_unit])];
                    unset($unit_niks[$nama_unit]); // sudah diproses
                } else {
                    $data_total[] = ['unit_kerja' => $nama_unit, 'total_kontrak_karyawan' => 0];
                }
            }
            // Tambahkan unit lain yang muncul di data (non-mapped), agar tidak hilang
            foreach ($unit_niks as $canon => $nset) {
                // hanya tambahkan jika memiliki NIK (bukan empty)
                $data_total[] = ['unit_kerja' => $canon, 'total_kontrak_karyawan' => count($nset)];
            }
        }
    }

    // Hitung grand total (hanya saat menampilkan semua unit)
    if (!$unit) {
        $grand_total = 0;
        foreach ($data_total as $r) {
            $grand_total += (int) $r['total_kontrak_karyawan'];
        }
    }

    // Ambil ringkasan total dari tabel spk dan spkwt
    $total_spk = total_from_table($conn, 'spk', $bulan, $tahun, ($unit ? $unit : ''));
    $total_spkwt = total_from_table($conn, 'spkwt', $bulan, $tahun, ($unit ? $unit : ''));
} // end is_filtered

// Tutup koneksi nanti di akhir setelah output HTML
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Total Kontrak Karyawan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap (tetap sama tampilan Anda) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Minimal styling agar rapi namun tidak mengubah layout utama Anda */
        .card-summary { font-size: 1.4rem; font-weight: 700; }
        .small-muted { font-size: .9rem; color: #666; }
        .table-fixed { max-height: 420px; overflow:auto; }
    </style>
</head>
<body class="p-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="m-0">Filter Total Kontrak Karyawan</h3>
        <div>
            <a href="kontrak.php" class="btn btn-secondary me-2">Keluar</a>
            <a href="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>

    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-2">
            <label class="form-label">Bulan</label>
            <select name="bulan" class="form-select">
                <option value="">Semua</option>
                <?php for($i=1;$i<=12;$i++): ?>
                    <option value="<?=$i?>" <?=($bulan==$i?'selected':'')?>>
                        <?=date('F', mktime(0,0,0,$i,1))?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Tahun</label>
            <select name="tahun" class="form-select">
                <option value="">Semua</option>
                <?php 
                for($t = 2025; $t <= 2050; $t++): ?>
                    <option value="<?=$t?>" <?=($tahun==$t?'selected':'')?>><?=$t?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Unit Kerja</label>
            <select name="unit" class="form-select">
                <option value="">---PILIH UNIT---</option>
                <?php foreach($tabel_unit as $nama_unit => $tbl): ?>
                    <option value="<?=htmlspecialchars($nama_unit)?>" <?=($unit==$nama_unit?'selected':'')?>>
                        <?=htmlspecialchars($nama_unit)?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Jenis Kontrak</label>
            <select name="kontrak" class="form-select">
                <option value="all"   <?=($kontrak==='all'?'selected':'')?>>Semua (Default)</option>
                <option value="spk"   <?=($kontrak==='spk'?'selected':'')?>>SPK</option>
                <option value="spkwt" <?=($kontrak==='spkwt'?'selected':'')?>>SPKWT</option>
            </select>
        </div>

        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Ringkasan total spk & spkwt -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card p-3">
                <div class="small-muted">Total (SPK)<?= $is_filtered && ($bulan||$tahun) ? ' — filter waktu aktif' : '' ?></div>
                <div class="card-summary">
                    <?php echo $is_filtered ? number_format($total_spk) : '-'; ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="small-muted">Total (SPKWT)<?= $is_filtered && ($bulan||$tahun) ? ' — filter waktu aktif' : '' ?></div>
                <div class="card-summary">
                    <?php echo $is_filtered ? number_format($total_spkwt) : '-'; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <div class="small-muted">
                    <?php if ($is_filtered): ?>
                        Menampilkan (Kontrak: <?=strtoupper(htmlspecialchars($kontrak))?><?= $unit ? ' — '.htmlspecialchars($unit) : '' ?>)
                    <?php else: ?>
                        Silakan gunakan filter untuk menampilkan data
                    <?php endif; ?>
                </div>
                <div class="card-summary">
                    <?php
                    if (!$is_filtered) {
                        echo '-';
                    } else {
                        // ketika filter aktif, tampilkan grand total (atau total unit tunggal)
                        if (!$unit) {
                            echo number_format($grand_total);
                        } else {
                            echo number_format((isset($data_total[0]) ? (int)$data_total[0]['total_kontrak_karyawan'] : 0));
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel hasil -->
    <div class="table-responsive table-fixed border rounded bg-white p-2">
    <table class="table table-bordered mb-0">
        <thead class="table-light">
            <tr>
                <th>Unit Kerja</th>
                <th style="width:180px;">Total Kontrak Karyawan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$is_filtered): ?>
                <tr><td colspan="2" class="text-center small-muted">Halaman kosong. Silakan gunakan filter di atas lalu tekan <strong>Filter</strong> untuk melihat data.</td></tr>
            <?php else: ?>
                <?php if(count($data_total)>0): ?>
                    <?php foreach($data_total as $row): ?>
                        <tr>
                            <td><?=htmlspecialchars($row['unit_kerja'])?></td>
                            <td class="text-end"><?=number_format((int)$row['total_kontrak_karyawan'])?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if(!$unit): // Tampilkan grand total hanya saat menampilkan semua unit ?>
                        <tr class="table-success fw-bold">
                            <td class="text-end">Total (Semua Unit)</td>
                            <td class="text-end"><?=number_format($grand_total)?></td>
                        </tr>
                    <?php endif; ?>

                <?php else: ?>
                    <tr><td colspan="2" class="text-center">Tidak ada data</td></tr>
                <?php endif; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

<!-- Bootstrap JS (opsional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Tutup koneksi
$conn->close();
?>