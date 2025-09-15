<?php
include 'config.php';



// Ambil filter bulan & tahun
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT); // format 2 digit
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : intval(date('Y'));
$export = isset($_GET['export']) ? $_GET['export'] : '';

// Query data berdasarkan bulan & tahun
$bulanInt = intval($bulan);
$query = "SELECT * FROM karyawan_karpim_pttdm
          WHERE MONTH(tanggal_input) = $bulanInt 
          AND YEAR(tanggal_input) = $tahun
          ORDER BY nama ASC";
$result = $conn->query($query);

// Export ke Excel
if ($export === 'excel') {
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Rekap_Karyawan_Pimpinan_PT_TDM_{$bulan}_{$tahun}.xls");
    echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
    echo "<table border='1'>
        <tr><th colspan='15'>Rekap Karyawan Pimpinan PT.TDM - Bulan $bulan Tahun $tahun</th></tr>
        <tr>
          <th>No</th>
          <th>NIK</th>
          <th>Nama</th>
          <th>Jenis Kelamin</th>
          <th>Tanggal Lahir</th>
          <th>Alamat</th>
          <th>Agama</th>
          <th>Pendidikan Terakhir</th>
          <th>Jabatan</th>
          <th>Unit Kerja</th>
          <th>Status Karyawan</th>
          <th>Status Kerja</th>
          <th>Tanggal Masuk</th>
          <th>No KPTS</th>
          <th>Golongan</th>
        </tr>";
    $no=1;
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>".$no++."</td>
                <td style='mso-number-format:\"\\@\"'>".$row['nik']."</td>
                <td>".$row['nama']."</td>
                <td>".$row['jenis_kelamin']."</td>
                <td>".$row['tanggal_lahir']."</td>
                <td>".$row['alamat']."</td>
                <td>".$row['agama']."</td>
                <td>".$row['pendidikan_terakhir']."</td>
                <td>".$row['jabatan']."</td>
                <td>".$row['unit_kerja']."</td>
                <td>".$row['status_karyawan']."</td>
                <td>".$row['status_kerja']."</td>
                <td>".$row['tanggal_bergabung']."</td>
                <td>".$row['no_kpts']."</td>
                <td>".$row['golongan']."</td>
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
  <title>Rekap Bulanan Karyawan Pimpinan PT.TDM</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  /* Tabel custom */
.table-custom {
  width: 100%;
  font-size: 14px;
  border-collapse: collapse;
}

/* Header sticky */
.table-custom thead th {
  position: sticky;
  top: 0;
  z-index: 10;
  background-color: #f8f9fa;
  text-align: center;
  vertical-align: middle;
  padding: 8px;
  border: 1px solid #dee2e6;
}

/* Kolom pertama sticky */
.table-custom th:first-child,
.table-custom td:first-child {
  position: sticky;
  left: 0;
  z-index: 9;
  background-color: #ffffff;
  text-align: center;
}

/* Supaya header + kolom pertama saat overlap tetap rapi */
.table-custom thead th:first-child {
  z-index: 11;
}

/* Sel */
.table-custom td {
  text-align: left;
  vertical-align: middle;
  padding: 8px;
  border: 1px solid #dee2e6;
  white-space: nowrap;   /* Biar isi tetap ke samping */
}

/* Zebra striping */
.table-custom tbody tr:nth-child(even) {
  background-color: #f9f9f9;
}

/* Lebar kolom proporsional */
.col-no { width: 50px; }
.col-nik { width: 160px; }
.col-nama { width: 180px; font-weight: 500; text-transform: capitalize; }
.col-jk { width: 100px; text-align:center; }
.col-tgllahir { width: 140px; }
.col-alamat { width: 220px; }
.col-agama { width: 120px; text-align:center; }
.col-pendidikan { width: 140px; }
.col-jabatan { width: 140px; }
.col-unit { width: 180px; }
.col-stskaryawan { width: 140px; text-align:center; }
.col-stskerja { width: 120px; text-align:center; }
.col-tglmasuk { width: 130px; text-align:center; }
.col-nokpts { width: 140px; }
.col-gol { width: 100px; text-align:center; }
.col-tglinput { width: 140px; text-align:center; }

/* Wrapper scroll */
.table-wrapper {
  max-height: 500px;   /* tinggi area scroll */
  overflow: auto;
  border: 1px solid #dee2e6;
}

  </style>
</head>
<body class="container mt-4">
  <h2>Rekap Bulanan Karyawan Pimpinan PT.TDM</h2>

  <!-- Filter -->
  <form method="get" class="row g-2 mb-3">
    <div class="col-md-3">
      <select name="bulan" class="form-select">
        <?php for ($m=1;$m<=12;$m++): 
          $val = str_pad($m,2,'0',STR_PAD_LEFT); ?>
          <option value="<?= $val ?>" <?= ($bulan==$val)?'selected':'' ?>>
            <?= date('F', mktime(0,0,0,$m,10)) ?>
          </option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select name="tahun" class="form-select">
        <?php for ($y=date('Y');$y>=2000;$y--): ?>
          <option value="<?= $y ?>" <?= ($tahun==$y)?'selected':'' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-primary">Tampilkan</button>
      <a href="?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&export=excel" class="btn btn-success">Export Excel</a>
    </div>
  </form>
  <!-- Tombol kembali -->
  <a href="data_karyawan_karpim.php" class="btn btn-secondary mb-3">
    &larr; Kembali
  </a>
   <!-- Tabel Rekap -->
<div class="card">
  <div class="card-body">
    <div class="table-wrapper">
      <table class="table-custom table table-bordered table-hover">
      <thead>
        <tr>
          <th class="col-no">No</th>
          <th class="col-nik">NIK</th>
          <th class="col-nama">Nama</th>
          <th class="col-jk">Jenis Kelamin</th>
          <th class="col-tgllahir">Tanggal Lahir</th>
          <th class="col-alamat">Alamat</th>
          <th class="col-agama">Agama</th>
          <th class="col-pendidikan">Pendidikan Terakhir</th>
          <th class="col-jabatan">Jabatan</th>
          <th class="col-unit">Unit Kerja</th>
          <th class="col-stskaryawan">Status Karyawan</th>
          <th class="col-stskerja">Status Kerja</th>
          <th class="col-tglmasuk">Tanggal Masuk</th>
          <th class="col-nokpts">No KPTS</th>
          <th class="col-gol">Golongan</th>
          <th class="col-tglinput">Tanggal Input</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            $no=1;
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td class='text-center'>".$no++."</td>
                    <td>".$row['nik']."</td>
                    <td>".$row['nama']."</td>
                    <td class='text-center'>".$row['jenis_kelamin']."</td>
                    <td>".$row['tanggal_lahir']."</td>
                    <td>".$row['alamat']."</td>
                    <td class='text-center'>".$row['agama']."</td>
                    <td>".$row['pendidikan_terakhir']."</td>
                    <td>".$row['jabatan']."</td>
                    <td>".$row['unit_kerja']."</td>
                    <td class='text-center'>".$row['status_karyawan']."</td>
                    <td class='text-center'>".$row['status_kerja']."</td>
                    <td class='text-center'>".$row['tanggal_bergabung']."</td>
                    <td>".$row['no_kpts']."</td>
                    <td class='text-center'>".$row['golongan']."</td>
                    <td class='text-center'>".$row['tanggal_input']."</td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='16' class='text-center'>Data tidak ditemukan</td></tr>";
        }
        ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>