<?php 
date_default_timezone_set('Asia/Jakarta');
session_start();

// KONEKSI DATABASE
include 'config.php';


// PROSES UPDATE KETERANGAN
if (isset($_POST['update_keterangan'])) {
    $id     = $_POST['id'];
    $ket    = $_POST['keterangan'];

    // Update keterangan di absensi_rsts
    $stmt = $conn->prepare("UPDATE absensi_rsts SET keterangan=? WHERE id=?");
    $stmt->bind_param("si", $ket, $id);
    $stmt->execute();
    $stmt->close();

    // Update juga di rekapan_harian_rsts jika ada
    $q = $conn->query("SELECT nik, jabatan, tanggal, jenis_absen FROM absensi_rsts WHERE id='$id'");
    if ($q && $q->num_rows > 0) {
        $row = $q->fetch_assoc();
        $nik = $conn->real_escape_string($row['nik']);
        $jabatan = $conn->real_escape_string($row['jabatan']);
        $tanggal = $conn->real_escape_string($row['tanggal']);
        $jenis_absen = $conn->real_escape_string($row['jenis_absen']);
        $ket_esc = $conn->real_escape_string($ket);

        $conn->query("UPDATE rekapan_harian_rsts SET keterangan='$ket_esc' 
                      WHERE nik='$nik' AND jabatan='$jabatan' AND tanggal='$tanggal' AND jenis_absen='$jenis_absen'");
    }
}

// PROSES HAPUS DATA
if (isset($_POST['hapus_data'])) {
    $id = $_POST['id'];

    // Hapus dari absensi_rsts
    $stmt = $conn->prepare("DELETE FROM absensi_rsts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Tidak perlu hapus manual di rekapan_harian_rsts jika hanya untuk laporan,
    // tapi kalau ingin bersih bisa gunakan query tambahan sesuai kebutuhan.
}

// Tentukan tanggal hari ini atau ambil dari filter form
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Tentukan filter jenis absen
$jenis_absen = isset($_GET['jenis_absen']) ? $_GET['jenis_absen'] : 'Semua';

// Query rekapan absensi
$sql = "SELECT id, nik, nama, jabatan, jenis_absen, keterangan, jam, unit_kerja 
        FROM absensi_rsts
        WHERE tanggal = '$tanggal'";

if ($jenis_absen != "Semua") {
    $sql .= " AND jenis_absen = '$jenis_absen'";
}

$sql .= " ORDER BY jam ASC";

$result = $conn->query($sql);

// PROSES EXPORT EXCEL
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=rekapan_absensi_$tanggal.xls");

    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr><th colspan='8' style='text-align:center; font-size:16px; background:#f2f2f2;'>Rekapan Harian RSTS - $tanggal</th></tr>";

    // Jika filter "Semua" → gabung Masuk & Pulang jadi 1 kolom
    if ($jenis_absen == "Semua") {
        echo "<tr>
                <th>No</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Keterangan</th>
                <th>Unit Kerja</th>
              </tr>";

        // Ambil data & kelompokkan
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $nik = $row['nik'];
                if (!isset($data[$nik])) {
                    $data[$nik] = [
                        'nik' => $row['nik'],
                        'nama' => $row['nama'],
                        'jabatan' => $row['jabatan'],
                        'unit_kerja' => $row['unit_kerja'],
                        'keterangan' => '',
                        'masuk' => '',
                        'pulang' => ''
                    ];
                }
                if ($row['jenis_absen'] == "Masuk") {
                    $data[$nik]['masuk'] = $row['jam'];
                }
                if ($row['jenis_absen'] == "Pulang") {
                    $data[$nik]['pulang'] = $row['jam'];
                    $data[$nik]['keterangan'] = $row['keterangan'];
                }
            }
        }

        // Tampilkan hasil
        $no = 1;
        foreach ($data as $d) {
            echo "<tr>
                    <td>".$no++."</td>
                    <td style='mso-number-format:\"\\@\"'>".$d['nik']."</td>
                    <td>".htmlspecialchars($d['nama'])."</td>
                    <td>".htmlspecialchars($d['jabatan'])."</td>
                    <td>".htmlspecialchars($d['masuk'])."</td>
                    <td>".htmlspecialchars($d['pulang'])."</td>
                    <td>".htmlspecialchars($d['keterangan'])."</td>
                    <td>".htmlspecialchars($d['unit_kerja'])."</td>
                  </tr>";
        }

    } else {
        // Jika filter bukan "Semua" tampilkan normal
        echo "<tr>
                <th>No</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Jam</th>
                <th>Jenis Absen</th>
                <th>Keterangan</th>
                <th>Unit Kerja</th>
              </tr>";
        if ($result && $result->num_rows > 0) {
            $no = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>".$no++."</td>
                        <td style='mso-number-format:\"\\@\"'>".$row['nik']."</td>
                        <td>".htmlspecialchars($row['nama'])."</td>
                        <td>".htmlspecialchars($row['jabatan'])."</td>
                        <td>".htmlspecialchars($row['jam'])."</td>
                        <td>".htmlspecialchars($row['jenis_absen'])."</td>
                        <td>".htmlspecialchars($row['keterangan'])."</td>
                        <td>".htmlspecialchars($row['unit_kerja'])."</td>
                      </tr>";
            }
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
    <title>Rekapan Absensi Harian Rumah Sakit Tanjung Selamat (RSTS)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        h2 { text-align: center; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 8px; text-align: center; }
        th { background: #007BFF; color: white; }
        tr:nth-child(even) { background: #f2f2f2; }
        .filter { margin-bottom: 20px; text-align:center; }
        input[type=text], input[type=date], select { 
            padding: 5px; 
            border: 1px solid #ccc; 
            border-radius: 4px;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-primary {
            background: #007BFF;
            color: white;
        }
        .btn-primary:hover { background: #0069d9; }

        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover { background: #218838; }

        .btn-warning {
            background: #ffc107;
            color: black;
        }
        .btn-warning:hover { background: #e0a800; }

        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover { background: #c82333; }

        .nav-btn { 
            position: absolute;
            top: 20px; 
            left: 20px; 
            background:#28a745; 
            color:white; 
            padding:8px 15px; 
            text-decoration:none; 
            border-radius:5px;
            font-weight:bold;
            transition: 0.3s;
        }
        .nav-btn:hover { background:#218838; }
    </style>
</head>
<body>

<a href="absensi_rsts.php" class="nav-btn">⬅ Kembali</a>

<h2>Rekapan Absensi Harian Rumah Sakit Tanjung Selamat (RSTS)</h2>

<div class="filter">
    <form method="get" action="">
        Pilih Tanggal: 
        <input type="date" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">

        Pilih Jenis Absen: 
        <select name="jenis_absen">
            <option value="Semua" <?php if($jenis_absen=="Semua") echo "selected"; ?>>Semua</option>
            <option value="Masuk" <?php if($jenis_absen=="Masuk") echo "selected"; ?>>Masuk</option>
            <option value="Pulang" <?php if($jenis_absen=="Pulang") echo "selected"; ?>>Pulang</option>
        </select>

        <button type="submit" class="btn btn-primary">Tampilkan</button>
        <button type="submit" name="export" value="excel" class="btn btn-warning">Export Excel</button>
    </form>
</div>

<table>
    <tr>
        <th>No</th>
        <th>NIK</th>
        <th>Nama</th>
        <th>Jabatan</th>
        <th>Jam</th>
        <th>Jenis Absen</th>
        <th>Keterangan</th>
        <th>Unit Kerja</th>
        <th>Aksi</th>
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
                <td>".htmlspecialchars($row['jam'])."</td>
                <td>".htmlspecialchars($row['jenis_absen'])."</td>
                <td>
                    <form method='post' style='display:inline-block;'>
                        <input type='hidden' name='id' value='".htmlspecialchars($row['id'])."'>
                        <input type='text' name='keterangan' value='".htmlspecialchars($row['keterangan'])."'>
                </td>
                <td>".htmlspecialchars($row['unit_kerja'])."</td>
                <td>
                        <button type='submit' name='update_keterangan' class='btn btn-success'>Simpan</button>
                    </form>
                    <form method='post' style='display:inline-block;' onsubmit=\"return confirm('Yakin ingin menghapus data ini?');\">
                        <input type='hidden' name='id' value='".htmlspecialchars($row['id'])."'>
                        <button type='submit' name='hapus_data' class='btn btn-danger'>Hapus</button>
                    </form>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='9'>Belum ada data absensi pada tanggal ini.</td></tr>";
    }
    ?>
</table>

</body>
</html>