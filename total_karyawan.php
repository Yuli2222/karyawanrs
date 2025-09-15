<?php
// Koneksi database
include 'config.php';

// Mapping unit => tabel database
$tabel_unit = [
    "Kantor Direksi PT.TDM"            => "karyawan_kantor_pttdm",
    "Klinik Pratama Batang Serangan"   => "karyawan_klinik_bts",
    "Klinik Rambutan Deli Binjai"      => "karyawan_klinik_rdb",
    "Klinik Pratama Garuda"            => "karyawan_klinik_grd",
    "Rs Tanjung Selamat"               => "karyawan_rsts",
    "Karyawan Pimpinan"                => "karyawan_karpim_pttdm",
    "Karyawan Pelaksana"               => "karyawan_karpel_pttdm",
];

// Filter
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;
$unit  = isset($_GET['unit']) ? $_GET['unit'] : '';

function whereTanggalInput($bulan, $tahun) {
    $w = "1=1";
    if ($bulan > 0) $w .= " AND MONTH(tanggal_input) = ". intval($bulan);
    if ($tahun > 0) $w .= " AND YEAR(tanggal_input) = ". intval($tahun);
    return $w;
}

// Ambil data total
$data_total = [];

if ($unit && isset($tabel_unit[$unit])) {
    $tbl = $tabel_unit[$unit];
    $sql = "SELECT '". $conn->real_escape_string($unit) ."' AS unit_kerja, 
                   COUNT(DISTINCT nik) AS total_karyawan 
            FROM $tbl 
            WHERE ".whereTanggalInput($bulan, $tahun)." 
              AND status_kerja = 'Aktif'";
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        $data_total[] = $row;
    }
} else {
    foreach ($tabel_unit as $nama_unit => $tbl) {
        $sql = "SELECT '". $conn->real_escape_string($nama_unit) ."' AS unit_kerja, 
                       COUNT(DISTINCT nik) AS total_karyawan 
                FROM $tbl 
                WHERE ".whereTanggalInput($bulan, $tahun)." 
                  AND status_kerja = 'Aktif'";
        $res = $conn->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            $data_total[] = $row;
        } else {
            $data_total[] = ['unit_kerja' => $nama_unit, 'total_karyawan' => 0];
        }
    }
}

// Hitung grand total hanya jika menampilkan semua unit (unit kosong)
$grand_total = 0;
if (!$unit) {
    foreach ($data_total as $r) {
        $grand_total += (int) $r['total_karyawan'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Filter Total Karyawan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="m-0">Filter Total Karyawan</h3>
        <div>
            <!-- Tombol Keluar menuju data_karyawan.php -->
            <a href="data_karyawan.php" class="btn btn-secondary me-2">Keluar</a>
            <!-- Tombol Reset filter (kembali ke semua) -->
            <a href="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>

    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-3">
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
       <div class="col-md-3">
    <label class="form-label">Tahun</label>
    <select name="tahun" class="form-select">
        <option value="">Semua</option>
        <?php 
        for($t = 2025; $t <= 2050; $t++): ?>
            <option value="<?=$t?>" <?=($tahun==$t?'selected':'')?>><?=$t?></option>
        <?php endfor; ?>
    </select>
</div>

        <div class="col-md-3">
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
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Unit Kerja</th>
                <th style="width:160px;">Total Karyawan</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($data_total)>0): ?>
                <?php foreach($data_total as $row): ?>
                    <tr>
                        <td><?=htmlspecialchars($row['unit_kerja'])?></td>
                        <td class="text-end"><?=number_format((int)$row['total_karyawan'])?></td>
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
        </tbody>
    </table>
    </div>
</div>

</body>
</html>