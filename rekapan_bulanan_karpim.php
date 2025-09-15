<?php
date_default_timezone_set('Asia/Jakarta');
session_start();

// KONEKSI DATABASE
include 'config.php';

// Tentukan bulan yang ingin direkap
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');

// Query untuk ambil data absensi bulanan
$sql = "SELECT 
            nik, 
            nama, 
            jabatan, 
            unit_kerja, 
            SUM(CASE WHEN jenis_absen = 'Masuk'  AND keterangan = 'Masuk' THEN 1 ELSE 0 END) as masuk_masuk,
            SUM(CASE WHEN jenis_absen = 'Masuk'  AND keterangan = 'Sakit' THEN 1 ELSE 0 END) as masuk_sakit,
            SUM(CASE WHEN jenis_absen = 'Masuk'  AND keterangan = 'Izin' THEN 1 ELSE 0 END) as masuk_izin,
            SUM(CASE WHEN jenis_absen = 'Masuk'  AND keterangan = 'Libur' THEN 1 ELSE 0 END) as masuk_libur,
            SUM(CASE WHEN jenis_absen = 'Masuk'  AND keterangan = 'Cuti' THEN 1 ELSE 0 END) as masuk_cuti,
            SUM(CASE WHEN jenis_absen = 'Masuk'  AND keterangan = 'P-3' THEN 1 ELSE 0 END) as masuk_p3,
            SUM(CASE WHEN jenis_absen = 'Masuk'  AND keterangan = 'Dinas Luar' THEN 1 ELSE 0 END) as masuk_dinas,

            SUM(CASE WHEN jenis_absen = 'Pulang' AND keterangan = 'Masuk' THEN 1 ELSE 0 END) as pulang_masuk,
            SUM(CASE WHEN jenis_absen = 'Pulang' AND keterangan = 'Sakit' THEN 1 ELSE 0 END) as pulang_sakit,
            SUM(CASE WHEN jenis_absen = 'Pulang' AND keterangan = 'Izin' THEN 1 ELSE 0 END) as pulang_izin,
            SUM(CASE WHEN jenis_absen = 'Pulang' AND keterangan = 'Libur' THEN 1 ELSE 0 END) as pulang_libur,
            SUM(CASE WHEN jenis_absen = 'Pulang' AND keterangan = 'Cuti' THEN 1 ELSE 0 END) as pulang_cuti,
            SUM(CASE WHEN jenis_absen = 'Pulang' AND keterangan = 'P-3' THEN 1 ELSE 0 END) as pulang_p3,
            SUM(CASE WHEN jenis_absen = 'Pulang' AND keterangan = 'Dinas Luar' THEN 1 ELSE 0 END) as pulang_dinas
        FROM absensi_karpim_pttdm
        WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulan' 
        GROUP BY nik, nama, jabatan, unit_kerja 
        ORDER BY nama ASC";

$result = $conn->query($sql);

// EXPORT EXCEL
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=rekapan_absensi_bulanan_$bulan.xls");

    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr><th colspan='19' style='text-align:center; font-size:16px; background:#f2f2f2;'>Rekapan Harian BTS - $bulan</th></tr>";

    echo "<tr>
            <th rowspan='2'>No</th>
            <th rowspan='2'>NIK</th>
            <th rowspan='2'>Nama</th>
            <th rowspan='2'>Jabatan</th>
            <th rowspan='2'>Unit Kerja</th>
            <th colspan='7'>Masuk</th>
            <th colspan='7'>Pulang</th>
          </tr>";
    echo "<tr>
            <th>Masuk</th><th>Sakit</th><th>Izin</th><th>Libur</th><th>Cuti</th><th>P-3</th><th>Dinas Luar</th>
            <th>Masuk</th><th>Sakit</th><th>Izin</th><th>Libur</th><th>Cuti</th><th>P-3</th><th>Dinas Luar</th>
          </tr>";

    if ($result && $result->num_rows > 0) {
        $no = 1;
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>".$no++."</td>
                    <td style='mso-number-format:\"\\@\"'>".$row['nik']."</td>
                    <td>".htmlspecialchars($row['nama'])."</td>
                    <td>".htmlspecialchars($row['jabatan'])."</td>
                    <td>".htmlspecialchars($row['unit_kerja'])."</td>

                    <td>".$row['masuk_masuk']."</td>
                    <td>".$row['masuk_sakit']."</td>
                    <td>".$row['masuk_izin']."</td>
                    <td>".$row['masuk_libur']."</td>
                    <td>".$row['masuk_cuti']."</td>
                    <td>".$row['masuk_p3']."</td>
                    <td>".$row['masuk_dinas']."</td>

                    <td>".$row['pulang_masuk']."</td>
                    <td>".$row['pulang_sakit']."</td>
                    <td>".$row['pulang_izin']."</td>
                    <td>".$row['pulang_libur']."</td>
                    <td>".$row['pulang_cuti']."</td>
                    <td>".$row['pulang_p3']."</td>
                    <td>".$row['pulang_dinas']."</td>
                  </tr>";
        }
    }

    echo "</table>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapan Absensi Bulanan Karyawan Pimpinan Kantor Direksi PT.TDM</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        h2 { text-align: center; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 8px; text-align: center; white-space: nowrap; }
        th { background: #007BFF; color: white; }
        tr:nth-child(even) { background: #f2f2f2; }
        .filter { margin-bottom: 20px; text-align:center; }
        input[type=month], select { padding: 5px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 6px 12px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-primary { background: #007BFF; color: white; }
        .btn-primary:hover { background: #0069d9; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-warning:hover { background: #e0a800; }
        .nav-btn { position: absolute; top: 20px; left: 20px; background:#28a745; color:white; padding:8px 15px; text-decoration:none; border-radius:5px; font-weight:bold; transition: 0.3s; }
        .nav-btn:hover { background:#218838; }
    </style>
</head>
<body>

<a href="absensi_karpim.php" class="nav-btn">⬅ Kembali</a>

<h2>Rekapan Absensi Bulanan Karyawan Pimpinan Kantor Direksi PT.TDM</h2>

<div class="filter">
    <form method="get" action="">
        Pilih Bulan: 
        <input type="month" name="bulan" value="<?php echo htmlspecialchars($bulan); ?>">
        <button type="submit" class="btn btn-primary">Tampilkan</button>
        <button type="submit" name="export" value="excel" class="btn btn-warning">Export Excel</button>
    </form>
</div>

<table>
    <tr>
        <th rowspan="2">No</th>
        <th rowspan="2">NIK</th>
        <th rowspan="2">Nama</th>
        <th rowspan="2">Jabatan</th>
        <th rowspan="2">Unit Kerja</th>
        <th colspan="7">Masuk</th>
        <th colspan="7">Pulang</th>
    </tr>
    <tr>
        <th>Masuk</th><th>Sakit</th><th>Izin</th><th>Libur</th><th>Cuti</th><th>P-3</th><th>Dinas Luar</th>
        <th>Masuk</th><th>Sakit</th><th>Izin</th><th>Libur</th><th>Cuti</th><th>P-3</th><th>Dinas Luar</th>
    </tr>
    <?php
    if ($result && $result->num_rows > 0) {
        $no = 1;
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>".$no++."</td>
                <td>".htmlspecialchars($row['nik'])."</td>
                <td>".htmlspecialchars($row['nama'])."</td>
                <td>".htmlspecialchars($row['jabatan'])."</td>
                <td>".htmlspecialchars($row['unit_kerja'])."</td>

                <td>".$row['masuk_masuk']."</td>
                <td>".$row['masuk_sakit']."</td>
                <td>".$row['masuk_izin']."</td>
                <td>".$row['masuk_libur']."</td>
                <td>".$row['masuk_cuti']."</td>
                <td>".$row['masuk_p3']."</td>
                <td>".$row['masuk_dinas']."</td>

                <td>".$row['pulang_masuk']."</td>
                <td>".$row['pulang_sakit']."</td>
                <td>".$row['pulang_izin']."</td>
                <td>".$row['pulang_libur']."</td>
                <td>".$row['pulang_cuti']."</td>
                <td>".$row['pulang_p3']."</td>
                <td>".$row['pulang_dinas']."</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='19'>Belum ada data absensi pada bulan ini.</td></tr>";
    }
    ?>
</table>

</body>
</html>