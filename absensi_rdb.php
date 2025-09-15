<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// Koneksi ke database
include 'config.php';


// ================== PROSES SIMPAN ABSENSI ==================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if it's a single save request for Masuk or Pulang
    if (isset($_POST['single_save_masuk']) || isset($_POST['single_save_pulang'])) {
        if (isset($_POST['single_save_masuk'])) {
            $key = $_POST['single_save_masuk'];
            $jenis_to_save = 'Masuk';
        } else {
            $key = $_POST['single_save_pulang'];
            $jenis_to_save = 'Pulang';
        }

        list($nik, $jabatan) = explode("|", $key);

        // Get keterangan for the specific jenis absen only
        $ket = $_POST['keterangan'][$key][$jenis_to_save] ?? '';

        $nik_esc = $conn->real_escape_string($nik);
        $jabatan_esc = $conn->real_escape_string($jabatan);
        $jenis_esc = $conn->real_escape_string($jenis_to_save);
        $ket_esc = $conn->real_escape_string($ket);

        // Ambil tanggal manual dari input form, jika kosong gunakan tanggal sekarang
        $tanggal_input = $_POST['tanggal_absen'][$key][$jenis_to_save] ?? '';
        if ($tanggal_input && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_input)) {
            $tanggal = $tanggal_input;
        } else {
            $tanggal = date('Y-m-d');
        }

        // Ambil jam dari kolom karyawan jika tersedia
        $q_karyawan = $conn->query("SELECT * FROM karyawan_klinik_rdb WHERE nik='$nik_esc' AND jabatan='$jabatan_esc' LIMIT 1");
        $dk = $q_karyawan ? $q_karyawan->fetch_assoc() : null;
        $jam_manual = null;
        if ($dk && isset($dk['jam_absen_'.$jenis_to_save])) {
            $jam_manual = $dk['jam_absen_'.$jenis_to_save]; // kolom jam_absen_Masuk atau jam_absen_Pulang
        }

        // Jika jam manual ada, gunakan sebagai jam absensi
        $jam = $jam_manual ?? date('H:i:s');

        // cek apakah absensi dengan jenis_absen yang sama sudah ada
        $qcek = $conn->query("SELECT id FROM absensi_rdb
                              WHERE nik='$nik_esc' 
                              AND jabatan='$jabatan_esc' 
                              AND tanggal='$tanggal' 
                              AND jenis_absen='$jenis_esc' LIMIT 1");
        if ($qcek && $qcek->num_rows > 0) {
            // update absensi jenis yang sama
            $row = $qcek->fetch_assoc();
            $id  = $row['id'];
            $sql_update = "UPDATE absensi_rdb SET keterangan=?, jam=? WHERE id=?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssi", $ket, $jam, $id);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // ambil data karyawan sesuai nik & jabatan
            $qk = $conn->query("SELECT * FROM karyawan_klinik_rdb
                                WHERE nik='$nik_esc' 
                                AND jabatan='$jabatan_esc' 
                                ORDER BY id_rdb DESC LIMIT 1");
            $dk = $qk ? $qk->fetch_assoc() : null;
            if ($dk) {
                $nama = $dk['nama'];
                $jabatan = $dk['jabatan'];
                $unit = $dk['unit_kerja'];
                $foto = $dk['foto'] ?? null;
            } else {
                $nama = '';
                $unit = '';
                $foto = null;
            }

            // insert absensi manual
            $sql_insert = "INSERT INTO absensi_rdb(nik,nama,jabatan,foto,tanggal,jam,jenis_absen,keterangan,unit_kerja) 
                           VALUES(?,?,?,?,?,?,?,?,?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sssssssss", $nik, $nama, $jabatan, $foto, $tanggal, $jam, $jenis_to_save, $ket, $unit);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        // ====== sinkronisasi ke rekapan_harian_rdb ======
        $res_cek = $conn->query("SELECT id FROM rekapan_harian_rdb
                                 WHERE nik='$nik_esc' 
                                 AND jabatan='$jabatan_esc' 
                                 AND tanggal='$tanggal' 
                                 AND jenis_absen='$jenis_esc'");
        if ($res_cek && $res_cek->num_rows > 0) {
            $conn->query("UPDATE rekapan_harian_rdb
                          SET keterangan='$ket_esc', 
                              nama='".$conn->real_escape_string($nama)."', 
                              unit_kerja='".$conn->real_escape_string($unit)."' 
                          WHERE nik='$nik_esc' 
                          AND jabatan='$jabatan_esc' 
                          AND tanggal='$tanggal' 
                          AND jenis_absen='$jenis_esc'");
        } else {
            $conn->query("INSERT INTO rekapan_harian_rdb(nik,nama,jabatan,tanggal,jenis_absen,keterangan,unit_kerja) 
                          VALUES('$nik_esc',
                                 '".$conn->real_escape_string($nama)."',
                                 '$jabatan_esc',
                                 '$tanggal',
                                 '$jenis_esc',
                                 '$ket_esc',
                                 '".$conn->real_escape_string($unit)."')");
        }

        // Redirect kembali
        $redir_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
        $redir_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
        $redir_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

        header("Location: absensi_rdb.php?bulan=".$redir_bulan."&tahun=".$redir_tahun."&tanggal=".$redir_tanggal);
        exit;
    }
    else {
        // If you want to keep the "Simpan Semua Absensi" button functionality, you can implement it here
        // For now, no action on bulk save
    }
}

// ================== FILTER ==================
$bulan  = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun  = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// ambil semua data karyawan (termasuk yg punya lebih dari 1 jabatan)
$sql_karyawan = "
    SELECT * FROM karyawan_klinik_rdb
    WHERE MONTH(tanggal_input) = '". $conn->real_escape_string($bulan) ."'
    AND YEAR(tanggal_input) = '". $conn->real_escape_string($tahun) ."'
    ORDER BY tanggal_input ASC
";
$result_karyawan = $conn->query($sql_karyawan);

// ambil data absensi (semua jenis absen per tanggal)
$sql_absensi = "
    SELECT * FROM absensi_rdb
    WHERE YEAR(tanggal)='". $conn->real_escape_string($tahun) ."'
    AND MONTH(tanggal)='". $conn->real_escape_string($bulan) ."'
";
if (!empty($tanggal)) {
    $sql_absensi .= " AND tanggal = '". $conn->real_escape_string($tanggal)."'";
}
$sql_absensi .= " ORDER BY tanggal ASC, jam ASC";
$result_absensi = $conn->query($sql_absensi);

// gabungkan data absensi per nik|jabatan, jenis_absen
$data_gabungan = [];
if ($result_karyawan && $result_karyawan->num_rows > 0) {
    while ($row_karyawan = $result_karyawan->fetch_assoc()) {
        $key_base = $row_karyawan['nik']."|".$row_karyawan['jabatan'];
        // Siapkan struktur untuk Masuk dan Pulang
        $data_gabungan[$key_base]['nik'] = $row_karyawan['nik'];
        $data_gabungan[$key_base]['nama'] = $row_karyawan['nama'];
        $data_gabungan[$key_base]['jabatan'] = $row_karyawan['jabatan'];
        $data_gabungan[$key_base]['unit_kerja'] = $row_karyawan['unit_kerja'];
        $data_gabungan[$key_base]['foto'] = $row_karyawan['foto'] ?? null;
        // Inisialisasi data per jenis absen
        if (!isset($data_gabungan[$key_base]['absen'])) {
            $data_gabungan[$key_base]['absen'] = [
                'Masuk' => [
                    'tanggal' => null,
                    'jam' => null,
                    'jenis_absen' => 'Masuk',
                    'keterangan' => null,
                    'status' => 'Belum Absen',
                    'foto' => null
                ],
                'Pulang' => [
                    'tanggal' => null,
                    'jam' => null,
                    'jenis_absen' => 'Pulang',
                    'keterangan' => null,
                    'status' => 'Belum Absen',
                    'foto' => null
                ]
            ];
        }
    }
}
if ($result_absensi && $result_absensi->num_rows > 0) {
    while ($row_absensi = $result_absensi->fetch_assoc()) {
        $key = $row_absensi['nik']."|".$row_absensi['jabatan'];
        $jenis = $row_absensi['jenis_absen'];
        if (isset($data_gabungan[$key]['absen'][$jenis])) {
            // Merge absensi data without overwriting other jenis_absen data
            $data_gabungan[$key]['absen'][$jenis]['tanggal'] = $row_absensi['tanggal'];
            $data_gabungan[$key]['absen'][$jenis]['jam'] = $row_absensi['jam'];
            $data_gabungan[$key]['absen'][$jenis]['keterangan'] = $row_absensi['keterangan'];
            $data_gabungan[$key]['absen'][$jenis]['status'] = 'Sudah Absen';
            $data_gabungan[$key]['absen'][$jenis]['foto'] = $row_absensi['foto'];

            // Update main photo only if jenis is Masuk and photo exists
            if (!empty($row_absensi['foto']) && $jenis == 'Masuk') {
                $data_gabungan[$key]['foto'] = $row_absensi['foto'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<title>ABSENSI KLINIK RAMBUTAN DELI BINJAI (RDB)</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
    body { background:#f1f3f6; font-size:14px; }
    .container { margin-top:20px; }
    h3 { font-weight:600; }
    .btn-custom { border-radius:8px; }
    .table-container { background:#fff; padding:15px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
    .table th, .table td { padding:14px 12px; vertical-align:middle; }
    .table thead th { text-align:center; font-size:14px; }
    .table tbody td { text-align:center; }
    .table img { width:48px; height:48px; object-fit:cover; border-radius:50%; border:2px solid #ddd; cursor:pointer; }
    .form-select, .form-control { font-size:15px; padding:10px 14px; min-width:160px; border-radius:8px; }
    .btn-sm { padding:4px 10px; font-size:13px; }
    form.d-flex .form-select, form.d-flex .form-control { min-width:170px; }
    .modal-img { display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.8); justify-content:center; align-items:center; }
    .modal-img img { max-width:80%; max-height:80%; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.5); }
    .single-save-btn { margin-top: 5px; }
    .btn-group-save {
        display: flex;
        gap: 5px;
        justify-content: center;
    }
    input[type="date"].input-tanggal-absen {
        min-width: 140px;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 14px;
    }
</style>
</head>
<body>
<div class="container">
<h3 class="text-center mb-4">ABSENSI KLINIK RAMBUTAN DELI BINJAI (RDB)</h3>
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="get" class="d-flex gap-2">
        <select name="bulan" class="form-select">
            <option value="">-- Semua Bulan --</option>
            <?php for ($m=1;$m<=12;$m++) {
                $sel = ($m==$bulan) ? "selected" : "";
                echo "<option value='$m' $sel>".date('F', mktime(0,0,0,$m,1))."</option>";
            } ?>
        </select>
       <select name="tahun" class="form-select">
    <?php 
    for ($y=2025; $y<=2060; $y++) {
        $sel = ($y==$tahun) ? "selected" : "";
        echo "<option value='$y' $sel>$y</option>";
    } 
    ?>
</select>

        <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" class="form-control" />
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>
    <div><strong><?= date('d F Y') ?></strong></div>
</div>

<div class="mb-3">
    <a href="rekapan_harian_rdb.php" class="btn btn-outline-primary btn-custom">Rekapan Harian</a>
    <a href="rekapan_bulanan_rdb.php" class="btn btn-outline-success btn-custom">Rekapan Bulanan</a>
</div>

<form method="post" action="">
<div class="table-container">
<div class="table-responsive">
<table class="table table-bordered table-striped table-hover align-middle text-center">
<thead class="table-dark">
<tr>
    <th rowspan="2">No</th>
    <th rowspan="2">NIK</th>
    <th rowspan="2">Nama</th>
    <th rowspan="2">Jabatan</th>
    <th rowspan="2">Foto</th>
    <th colspan="4">Absensi Masuk</th>
    <th colspan="5">Absensi Pulang</th>
    <th rowspan="2">Unit Kerja</th>
    <th rowspan="2">Status</th>
    <th rowspan="2">Aksi</th>
</tr>
<tr>
    <th>Tanggal</th>
    <th>Jam</th>
    <th>Keterangan</th>
    <th>Tanggal Input</th>
    <th>Foto</th>
    <th>Tanggal</th>
    <th>Jam</th>
    <th>Keterangan</th>
    <th>Tanggal Input</th>
</tr>
</thead>
<tbody>
<?php
if (!empty($data_gabungan)) {
    $no=1;
    foreach ($data_gabungan as $key => $data) {
        $nik = $data['nik'];
        $jabatan = $data['jabatan'];
        $absen_masuk = $data['absen']['Masuk'];
        $absen_pulang = $data['absen']['Pulang'];
        $status = ($absen_masuk['status']=='Sudah Absen' || $absen_pulang['status']=='Sudah Absen') ? 'Sudah Absen' : 'Belum Absen';

        echo "<tr>
            <td>".$no++."</td>
            <td>".htmlspecialchars($nik)."</td>
            <td>".htmlspecialchars($data['nama'])."</td>
            <td>".htmlspecialchars($jabatan)."</td>
            <td>";
        // Use the photo from combined data to display
        $photoPath = !empty($data['foto']) ? $data['foto'] : 'no_image.png';
        echo "<img src='".htmlspecialchars($photoPath)."' onclick=\"showImg(this.src)\">";
        echo "</td>";

        // Absensi Masuk
        echo "<td>".htmlspecialchars($absen_masuk['tanggal'] ?? '-')."</td>
              <td>".htmlspecialchars($absen_masuk['jam'] ?? '-')."</td>
              <td>
                <select name='keterangan[".$nik."|".$jabatan."][Masuk]' class='form-select'>
                    <option value='' ".($absen_masuk['keterangan']==''?'selected':'').">-</option>
                    <option value='Masuk' ".($absen_masuk['keterangan']=='Masuk'?'selected':'').">Masuk</option>
                    <option value='Sakit' ".($absen_masuk['keterangan']=='Sakit'?'selected':'').">Sakit</option>
                    <option value='Izin' ".($absen_masuk['keterangan']=='Izin'?'selected':'').">Izin</option>
                    <option value='Libur' ".($absen_masuk['keterangan']=='Libur'?'selected':'').">Libur</option>
                    <option value='Cuti' ".($absen_masuk['keterangan']=='Cuti'?'selected':'').">Cuti</option>
                    <option value='p-3' ".($absen_masuk['keterangan']=='p-3'?'selected':'').">p-3</option>
                    <option value='Dinas Luar' ".($absen_masuk['keterangan']=='Dinas Luar'?'selected':'').">Dinas Luar</option>
                </select>
              </td>";

        // Input tanggal manual untuk absensi Masuk
        $tanggal_masuk_input = htmlspecialchars($absen_masuk['tanggal'] ?? '');
        echo "<td><input type='date' name='tanggal_absen[".$nik."|".$jabatan."][Masuk]' value='".$tanggal_masuk_input."' class='input-tanggal-absen'></td>";

        // Absensi Pulang with Foto column
        echo "<td>";
        $photoPulang = !empty($absen_pulang['foto']) ? $absen_pulang['foto'] : 'no_image.png';
        echo "<img src='".htmlspecialchars($photoPulang)."' onclick=\"showImg(this.src)\">";
        echo "</td>";

        echo "<td>".htmlspecialchars($absen_pulang['tanggal'] ?? '-')."</td>
              <td>".htmlspecialchars($absen_pulang['jam'] ?? '-')."</td>
              <td>
                <select name='keterangan[".$nik."|".$jabatan."][Pulang]' class='form-select'>
                    <option value='' ".($absen_pulang['keterangan']==''?'selected':'').">-</option>
                    <option value='Masuk' ".($absen_pulang['keterangan']=='Masuk'?'selected':'').">Masuk</option>
                    <option value='Sakit' ".($absen_pulang['keterangan']=='Sakit'?'selected':'').">Sakit</option>
                    <option value='Izin' ".($absen_pulang['keterangan']=='Izin'?'selected':'').">Izin</option>
                    <option value='Libur' ".($absen_pulang['keterangan']=='Libur'?'selected':'').">Libur</option>
                    <option value='Cuti' ".($absen_pulang['keterangan']=='Cuti'?'selected':'').">Cuti</option>
                    <option value='p-3' ".($absen_pulang['keterangan']=='p-3'?'selected':'').">p-3</option>
                    <option value='Dinas Luar' ".($absen_pulang['keterangan']=='Dinas Luar'?'selected':'').">Dinas Luar</option>
                </select>
              </td>";

        // Input tanggal manual untuk absensi Pulang
        $tanggal_pulang_input = htmlspecialchars($absen_pulang['tanggal'] ?? '');
        echo "<td><input type='date' name='tanggal_absen[".$nik."|".$jabatan."][Pulang]' value='".$tanggal_pulang_input."' class='input-tanggal-absen'></td>";

        echo "<td>".htmlspecialchars($data['unit_kerja'])."</td>
            <td>";
        if ($status=='Sudah Absen') {
            echo "<span class='badge bg-success'>Sudah Absen</span>";
        } else {
            echo "<span class='badge bg-danger'>Belum Absen</span>";
        }
        echo "</td>
            <td>
                <div class='btn-group-save'>
                    <button type='submit' name='single_save_masuk' value='".$nik."|".$jabatan."' class='btn btn-success btn-sm single-save-btn'>Simpan Masuk</button>
                    <button type='submit' name='single_save_pulang' value='".$nik."|".$jabatan."' class='btn btn-primary btn-sm single-save-btn'>Simpan Pulang</button>
                </div>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='15'>Data tidak ada</td></tr>";
}
?>
</tbody>
</table>
</div>
</div>

<!-- Input jenis_absen untuk Masuk dan Pulang -->
<?php
foreach ($data_gabungan as $key => $data) {
    $nik = $data['nik'];
    $jabatan = $data['jabatan'];
    echo '<input type="hidden" name="jenis_absen['.$nik.'|'.$jabatan.'][Masuk]" value="Masuk">';
    echo '<input type="hidden" name="jenis_absen['.$nik.'|'.$jabatan.'][Pulang]" value="Pulang">';
}
?>
<!-- Removed the "Simpan Semua Absensi" button as per your request for separate buttons -->
</form>
<a href="absen.php" class="btn btn-danger mt-3">Keluar</a>
</div>

<div class="modal-img" id="modalImg" onclick="closeImg()">
    <img id="imgFull" src="">
</div>
<script>
function showImg(src){
    document.getElementById("modalImg").style.display="flex";
    document.getElementById("imgFull").src=src;
}
function closeImg(){
    document.getElementById("modalImg").style.display="none";
}
</script>
</body>
</html>
<?php $conn->close(); ?>