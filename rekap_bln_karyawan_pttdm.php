<?php
include 'config.php';



// Ambil filter, pastikan format bulan 2-digit
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT); // '08' bukan '8'
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : intval(date('Y'));
$export = isset($_GET['export']) ? $_GET['export'] : '';

// Untuk query gunakan nilai integer untuk bulan/tahun
$bulanInt = intval($bulan);
$query = "SELECT * FROM karyawan_kantor_pttdm 
          WHERE MONTH(tanggal_input) = $bulanInt 
          AND YEAR(tanggal_input) = $tahun 
          ORDER BY nama ASC";
$result = $conn->query($query);

// Export Excel (tanpa kolom tanggal_input)
if ($export === 'excel') {
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=rekap_karyawan_kantor_direksi_pttdm_{$bulan}_{$tahun}.xls");
    echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
    echo "<table border='1'>
        <tr><th colspan='14'>Rekap Data Karyawan Kantor Direksi PT.TDM Bulan $bulan Tahun $tahun</th></tr>
        <tr>
            <th>No</th><th>NIK</th><th>Nama</th><th>Jenis Kelamin</th><th>Tgl Lahir</th>
            <th>Alamat</th><th>Agama</th><th>Pendidikan</th><th>Jabatan</th><th>Unit Kerja</th>
            <th>Status Karyawan</th><th>Status Kerja</th><th>Tanggal Masuk</th><th>Masa Kontrak</th>
        </tr>";
    $no = 1;
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format tanggal masuk jika ada
            $tgl_masuk = !empty($row['tanggal_bergabung']) && $row['tanggal_bergabung'] != '0000-00-00'
                ? date('d/m/Y', strtotime($row['tanggal_bergabung']))
                : '';
            echo "<tr>
                <td>{$no}</td>
                <td style=\"mso-number-format:'\@';\">".htmlspecialchars($row['nik'])."</td>
                <td>".htmlspecialchars($row['nama'])."</td>
                <td>".htmlspecialchars($row['jenis_kelamin'])."</td>
                <td>".htmlspecialchars($row['tanggal_lahir'])."</td>
                <td>".htmlspecialchars($row['alamat'])."</td>
                <td>".htmlspecialchars($row['agama'])."</td>
                <td>".htmlspecialchars($row['pendidikan_terakhir'])."</td>
                <td>".htmlspecialchars($row['jabatan'])."</td>
                <td>".htmlspecialchars($row['unit_kerja'])."</td>
                <td>".htmlspecialchars($row['status_karyawan'])."</td>
                <td>".htmlspecialchars($row['status_kerja'])."</td>
                <td>{$tgl_masuk}</td>
                <td>".htmlspecialchars($row['masa_kontrak'])."</td>
            </tr>";
            $no++;
        }
    } else {
        echo "<tr><td colspan='14' class='text-center'>Data tidak ditemukan</td></tr>";
    }
    echo "</table>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Data Karyawan Bulanan Kantor Direksi PT.TDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (opsional untuk ikon tombol) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<div class="container py-4">
    <h3 class="mb-4">Rekap Data Karyawan Bulanan Kantor Direksi PT.TDM</h3>
    
    <form method="GET" class="row g-3 mb-3 no-print">
        <div class="col-md-3">
            <label class="form-label">Bulan</label>
            <select class="form-select" name="bulan">
                <?php
                $bulanOptions = [
                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                ];
                foreach ($bulanOptions as $num => $nama) {
                    $selected = ($bulan === $num) ? "selected" : "";
                    echo "<option value='$num' $selected>$nama</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tahun</label>
            <select class="form-select" name="tahun">
                <?php
                for ($th = 2025; $th <= 2060; $th++) {
                    $selected = ($tahun == $th) ? "selected" : "";
                    echo "<option value='$th' $selected>$th</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </div>
    </form>

    <div class="mb-3 no-print d-flex gap-2">
        <?php
        // Amanin parameter untuk URL
        $qs_bulan = urlencode($bulan);
        $qs_tahun = urlencode($tahun);
        ?>
        <a href="?bulan=<?= $qs_bulan ?>&tahun=<?= $qs_tahun ?>&export=excel" class="btn btn-success">Export Excel</a>
        <a href="data_karyawan_k_pttdm.php" class="btn btn-secondary">Kembali</a>
         <!-- Tombol Tambah Karyawan -->
        <a href="tambah_data_pttdm.php?bulan=<?= $qs_bulan ?>&tahun=<?= $qs_tahun ?>" class="btn btn-primary ms-auto">
            <i class="fas fa-plus me-1"></i> Tambah Karyawan
        </a>
    </div>

    <div class="table-responsive" style="max-height:600px; overflow:auto;">
    <table class="table table-bordered table-sm align-middle">
        <thead class="table-light text-center">
            <tr>
                <th>No</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Jenis Kelamin</th>
                <th>Tgl Lahir</th>
                <th>Alamat</th>
                <th>Agama</th>
                <th>Pendidikan</th>
                <th>Jabatan</th>
                <th>Unit Kerja</th>
                <th>Status Karyawan</th>
                <th>Status Kerja</th>
                <th>Tanggal Masuk</th>
                <th>Masa Kontrak</th>
                <th>Tanggal Input</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tgl_masuk = !empty($row['tanggal_bergabung']) && $row['tanggal_bergabung'] != '0000-00-00'
                    ? date('d/m/Y', strtotime($row['tanggal_bergabung'])) : '';
                $tgl_input = !empty($row['tanggal_input']) && $row['tanggal_input'] != '0000-00-00'
                    ? date('d/m/Y', strtotime($row['tanggal_input'])) : '';

                echo "<tr>
                        <td class='text-center'>{$no}</td>
                        <td>".htmlspecialchars($row['nik'])."</td>
                        <td>".htmlspecialchars($row['nama'])."</td>
                        <td>".htmlspecialchars($row['jenis_kelamin'])."</td>
                        <td>".htmlspecialchars($row['tanggal_lahir'])."</td>
                        <td>".htmlspecialchars($row['alamat'])."</td>
                        <td>".htmlspecialchars($row['agama'])."</td>
                        <td>".htmlspecialchars($row['pendidikan_terakhir'])."</td>
                        <td>".htmlspecialchars($row['jabatan'])."</td>
                        <td>".htmlspecialchars($row['unit_kerja'])."</td>
                        <td>".htmlspecialchars($row['status_karyawan'])."</td>
                        <td>".htmlspecialchars($row['status_kerja'])."</td>
                        <td class='text-center'>{$tgl_masuk}</td>
                        <td>".htmlspecialchars($row['masa_kontrak'])."</td>
                        <td class='text-center'>{$tgl_input}</td>
                        <td class='text-center'>
                            <a href='edit_data_pttdm.php?id={$row['id_pttdm']}' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i></a>
                            <a href='hapus_data_pttdm.php?id={$row['id_pttdm']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'><i class='fas fa-trash'></i></a>
                        </td>
                    </tr>";
                $no++;
            }
        } else {
            echo "<tr><td colspan='16' class='text-center'>Data tidak ditemukan</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<style>
    /* Sticky Header */
    .table-responsive thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8f9fa; /* warna sama seperti .table-light */
    }

    /* Agar kolom tidak gepeng */
    .table td, .table th {
        min-width: 120px;
        white-space: nowrap;
    }
</style>


    <div class="text-center text-muted mt-3">
        &copy; <?= date('Y') ?> PT Tembakau Deli Medica
    </div>
</div>
</body>
</html>