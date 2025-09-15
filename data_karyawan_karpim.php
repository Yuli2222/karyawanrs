<?php
// KONEKSI DATABASE
include 'config.php';


// -----------------------------
// Fungsi cek data duplikat berdasarkan nama + bulan & tahun tanggal_input
// -----------------------------
function isDataExists($conn, $nama, $jenis_kelamin, $tanggal_lahir, $alamat, $agama, $pendidikan_terakhir, $tanggal_input) {
    if (empty($tanggal_input)) {
        return false; // jika tanggal_input kosong, jangan anggap duplikat
    }

    // Escape input untuk aman
    $nama = $conn->real_escape_string($nama);
    $jenis_kelamin = $conn->real_escape_string($jenis_kelamin);
    $tanggal_lahir = $conn->real_escape_string($tanggal_lahir);
    $alamat = $conn->real_escape_string($alamat);
    $agama = $conn->real_escape_string($agama);
    $pendidikan_terakhir = $conn->real_escape_string($pendidikan_terakhir);

    $ts = strtotime($tanggal_input);
    if ($ts === false) return false;

    $bulan = (int)date('n', $ts); // 1-12
    $tahun = (int)date('Y', $ts);

    $query = "SELECT 1 FROM karyawan_karpim_pttdm
              WHERE nama = '$nama'
                AND jenis_kelamin = '$jenis_kelamin'
                AND tanggal_lahir = '$tanggal_lahir'
                AND alamat = '$alamat'
                AND agama = '$agama'
                AND pendidikan_terakhir = '$pendidikan_terakhir'
                AND MONTH(tanggal_input) = $bulan
                AND YEAR(tanggal_input) = $tahun
              LIMIT 1";
    $result = $conn->query($query);
    return $result && $result->num_rows > 0;
}

// -----------------------------
// PROSES TAMBAH DATA (simpan_karpim)
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_karpim'])) {
    $nik = $conn->real_escape_string($_POST['nik']);
    $nama = $conn->real_escape_string($_POST['nama']);
    $jenis_kelamin = $conn->real_escape_string($_POST['jenis_kelamin']);
    $tanggal_lahir = $conn->real_escape_string($_POST['tanggal_lahir']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $agama = $conn->real_escape_string($_POST['agama']);
    $pendidikan_terakhir = $conn->real_escape_string($_POST['pendidikan_terakhir']);
    $jabatan = $conn->real_escape_string($_POST['jabatan']);
    $unit_kerja = $conn->real_escape_string($_POST['unit_kerja']);
    $status_karyawan = $conn->real_escape_string($_POST['status_karyawan']);
    $status_kerja = $conn->real_escape_string($_POST['status_kerja']);
    $tanggal_bergabung = $conn->real_escape_string($_POST['tanggal_bergabung']);
    $no_kpts = $conn->real_escape_string($_POST['no_kpts']);
    $golongan = $conn->real_escape_string($_POST['golongan']);
    $tanggal_input = $conn->real_escape_string($_POST['tanggal_input']);

    // Cek duplikat berdasarkan nama + bulan/tahun tanggal_input
    if (isDataExists($conn, $nama, $jenis_kelamin, $tanggal_lahir, $alamat, $agama, $pendidikan_terakhir, $tanggal_input)) {
        echo "<div class='alert alert-warning mt-4'>Data karyawan dengan nama <b>" . htmlspecialchars($nama) . "</b> sudah ada pada bulan <b>" . date('F Y', strtotime($tanggal_input)) . "</b>.</div>";
    } else {
        $query = "INSERT INTO karyawan_karpim_pttdm
                 (nik, nama, jenis_kelamin, tanggal_lahir, alamat, agama, pendidikan_terakhir, jabatan, unit_kerja, status_karyawan, status_kerja, tanggal_bergabung, no_kpts, tanggal_input, golongan)
                 VALUES 
                 ('$nik', '$nama', '$jenis_kelamin', '$tanggal_lahir', '$alamat', '$agama', '$pendidikan_terakhir', '$jabatan', '$unit_kerja', '$status_karyawan', '$status_kerja', '$tanggal_bergabung', '$no_kpts', '$tanggal_input', '$golongan')";
        
        if ($conn->query($query) === TRUE) {
            echo "<script>alert('Data berhasil disimpan'); window.location.href=window.location.href;</script>";
        } else {
            echo "<div class='alert alert-danger mt-4'>Error: " . htmlspecialchars($conn->error) . "</div>";
        }
    }
}

// -----------------------------
// PROSES EDIT DATA (update_karpim)
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_karpim'])) {
    $id_karpim = (int)$_POST['id_karpim'];
    $nik = $conn->real_escape_string($_POST['nik']);
    $nama = $conn->real_escape_string($_POST['nama']);
    $jenis_kelamin = $conn->real_escape_string($_POST['jenis_kelamin']);
    $tanggal_lahir = $conn->real_escape_string($_POST['tanggal_lahir']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $agama = $conn->real_escape_string($_POST['agama']);
    $pendidikan_terakhir = $conn->real_escape_string($_POST['pendidikan_terakhir']);
    $jabatan = $conn->real_escape_string($_POST['jabatan']);
    $unit_kerja = $conn->real_escape_string($_POST['unit_kerja']);
    $status_karyawan = $conn->real_escape_string($_POST['status_karyawan']);
    $status_kerja = $conn->real_escape_string($_POST['status_kerja']);
    $tanggal_bergabung = $conn->real_escape_string($_POST['tanggal_bergabung']);
    $no_kpts = $conn->real_escape_string($_POST['no_kpts']);
    $golongan = $conn->real_escape_string($_POST['golongan']);
    $tanggal_input = $conn->real_escape_string($_POST['tanggal_input']);

    $query = "UPDATE karyawan_karpim_pttdm SET 
                nik='$nik',
                nama='$nama',
                jenis_kelamin='$jenis_kelamin',
                tanggal_lahir='$tanggal_lahir',
                alamat='$alamat',
                agama='$agama',
                pendidikan_terakhir='$pendidikan_terakhir',
                jabatan='$jabatan',
                unit_kerja='$unit_kerja',
                status_karyawan='$status_karyawan',
                status_kerja='$status_kerja',
                tanggal_bergabung='$tanggal_bergabung',
                no_kpts='$no_kpts',
                golongan='$golongan',
                tanggal_input='$tanggal_input'
              WHERE id_karpim='$id_karpim'";
    if ($conn->query($query) === TRUE) {
        echo "<script>alert('Data berhasil diperbarui'); window.location.href=window.location.href;</script>";
    } else {
        echo "<div class='alert alert-danger mt-4'>Error: " . htmlspecialchars($conn->error) . "</div>";
    }
}

// -----------------------------
// PROSES HAPUS DATA
// -----------------------------
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($conn->query("DELETE FROM karyawan_karpim_pttdm WHERE id_karpim='$id'") === TRUE) {
        echo "<script>alert('Data berhasil dihapus'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
    } else {
        echo "<div class='alert alert-danger mt-4'>Error: " . htmlspecialchars($conn->error) . "</div>";
    }
}

// --- FILTERS ---
$search_nama = isset($_GET['search_nama']) ? trim($_GET['search_nama']) : '';
$filter      = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$bulan       = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tahun       = isset($_GET['tahun']) ? $_GET['tahun'] : '';

$filter_sql = "";
if ($filter === 'active') {
    $filter_sql .= " AND LOWER(TRIM(k.status_kerja)) = 'aktif'";
} elseif ($filter === 'nonactive') {
    $filter_sql .= " AND (LOWER(TRIM(k.status_kerja)) IN ('tidak aktif','non aktif','nonaktif','resign','keluar') 
                    OR LOWER(TRIM(k.status_kerja)) LIKE '%resign%' 
                    OR LOWER(TRIM(k.status_kerja)) LIKE '%nonaktif%')";
} elseif ($filter === 'cuti') {
    $filter_sql .= " AND LOWER(TRIM(k.status_kerja)) LIKE '%cuti%'";
}

$name_sql = "";
if ($search_nama !== '') {
    $safe = $conn->real_escape_string($search_nama);
    $name_sql .= " AND k.nama LIKE '%$safe%'";
}

$bulan_raw = isset($_GET['bulan']) ? $conn->real_escape_string($_GET['bulan']) : '';
$tahun_raw = isset($_GET['tahun']) ? $conn->real_escape_string($_GET['tahun']) : '';

$bulan_int = ($bulan_raw !== '') ? (int)$bulan_raw : null;
$tahun_int = ($tahun_raw !== '') ? (int)$tahun_raw : null;

$bulan_tahun_sql = '';
if (!is_null($bulan_int)) {
    $bulan_tahun_sql .= " AND MONTH(k.tanggal_input) = $bulan_int";
}
if (!is_null($tahun_int)) {
    $bulan_tahun_sql .= " AND YEAR(k.tanggal_input) = $tahun_int";
}

$use_latest_per_nik = (is_null($bulan_int) && is_null($tahun_int));

if ($use_latest_per_nik) {
    $from_sql = "
        karyawan_karpim_pttdm k
        INNER JOIN (
            SELECT nik, MAX(id_karpim) AS keep_id
            FROM karyawan_karpim_pttdm
            GROUP BY nik
        ) t ON k.nik = t.nik AND k.id_karpim = t.keep_id
    ";
} else {
    $from_sql = "karyawan_karpim_pttdm k";
}

$query = "
    SELECT k.*
    FROM $from_sql
    WHERE 1=1
    $filter_sql
    $name_sql
    $bulan_tahun_sql
    ORDER BY k.nama ASC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Data Karyawan Pimpinan Kantor Direksi PT.TDM (KARPIM) - PT. Tembakau Deli Medica</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root {
      --primary-color: #1e3c72;
      --secondary-color: #2a5298;
      --green-color: #28a745;
      --red-color: #dc3545;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #e6ecef, #d3e0ea);
      color: #333;
      margin: 0;
      padding: 0;
    }

    /* Header Content */
    .header-content {
      background: white;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 20px;
      border-left: 5px solid var(--primary-color);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .header-content h4 {
      margin: 0;
      font-weight: 600;
      color: var(--primary-color);
    }

    /* Button Tambah */
    .btn-tambah-user {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      font-weight: 600;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }
    .btn-tambah-user:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      color: white;
    }

    /* Table */
    .table-responsive {
      overflow-x: auto;
      white-space: nowrap;
    }
    .table {
      background: white;
      border-radius: 10px;
      font-size: 14px;
    }
    .table th {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      text-align: center;
      font-weight: 600;
      padding: 10px;
    }
    .table td {
      vertical-align: middle;
      text-align: center;
      padding: 8px;
    }
    .table-hover tbody tr:hover {
      background-color: #f5f7fa;
    }

    /* Action Buttons */
    .btn-action {
      padding: 5px 10px;
      margin: 0 3px;
      border-radius: 5px;
      font-size: 13px;
    }
    .btn-edit {
      background-color: #17a2b8;
      border: 1px solid #138496;
      color: white;
    }
    .btn-edit:hover {
      background-color: #138496;
    }
    .btn-delete {
      background-color: var(--red-color);
      border: 1px solid #c82333;
      color: white;
    }
    .btn-delete:hover {
      background-color: #c82333;
    }

    /* Filter Controls */
    .filter-controls {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 15px;
    }
    .filter-controls select,
    .filter-controls input {
      border-radius: 6px;
      padding: 6px 10px;
      border: 1px solid #ced4da;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .header-content { padding: 15px; }
      .table { font-size: 13px; }
      .btn-action { font-size: 12px; padding: 4px 8px; }
    }
    @media (max-width: 480px) {
      .header-content h4 { font-size: 16px; }
      .table th, .table td { font-size: 11px; }
      .btn-action { font-size: 11px; padding: 3px 6px; }
    }
  </style>
</head>
<body>
<div class="container mt-4">
  <h2>Data Karyawan Pimpinan Kantor Direksi PT.TDM (KARPIM)</h2>

  <!-- FILTER FORM -->
  <form method="get" class="row g-2 mb-3">
    <div class="col-md-3">
      <input type="text" name="search_nama" class="form-control" placeholder="Cari Nama" value="<?= htmlspecialchars($search_nama) ?>">
    </div>
    <div class="col-md-2">
      <select name="filter" class="form-select">
        <option value="all" <?= $filter=='all'?'selected':'' ?>>Semua</option>
        <option value="active" <?= $filter=='active'?'selected':'' ?>>Aktif</option>
        <option value="nonactive" <?= $filter=='nonactive'?'selected':'' ?>>Non Aktif</option>
        <option value="cuti" <?= $filter=='cuti'?'selected':'' ?>>Cuti</option>
      </select>
    </div>
    <div class="col-md-2">
      <select name="bulan" class="form-select">
        <option value="">Bulan</option>
        <?php for ($m=1;$m<=12;$m++): ?>
          <option value="<?= $m ?>" <?= ($bulan==$m)?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,10)) ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="tahun" class="form-select">
        <option value="">Tahun</option>
        <?php for ($y=date('Y');$y>=2000;$y--): ?>
          <option value="<?= $y ?>" <?= ($tahun==$y)?'selected':'' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-3">
      <a href="rekap_bln_karpim_pttdm.php" class="btn btn-info"><i class="fas fa-chart-bar"></i> Rekap Bulanan</a>
    </div>
    <div class="col-md-3 mt-2">
      <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
      <a href="?reset=1" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Reset</a>
    </div>
  </form>

  <!-- Tombol Tambah Data -->
  <div class="mb-3">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahModal">
      <i class="fas fa-plus"></i> Tambah Data
    </button>
  </div>

  <!-- Modal Tambah Data -->
<div class="modal fade" id="tambahModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Data Karyawan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-2">
          <div class="col-md-6">
            <label>NIK</label>
            <input type="text" name="nik" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label>Jenis Kelamin</label>
            <select name="jenis_kelamin" class="form-select">
              <option value="">-- Pilih Jenis Kelamin --</option>
              <option value="Laki-laki">Laki-laki</option>
              <option value="Perempuan">Perempuan</option>
            </select>
          </div>
          <div class="col-md-6">
            <label>Tanggal Lahir</label>
            <input type="text" name="tanggal_lahir" class="form-control" required>
          </div>

          <div class="col-md-12">
            <label>Alamat</label>
            <input type="text" name="alamat" class="form-control">
          </div>
          <div class="col-md-4">
            <label>Agama</label>
            <select name="agama" class="form-select">
              <option value="">-- Pilih Agama --</option>
              <option value="Islam">Islam</option>
              <option value="Kristen">Kristen</option>
              <option value="Hindu">Hindu</option>
              <option value="Buddha">Buddha</option>
              <option value="Konghucu">Konghucu</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>
          <div class="col-md-4">
            <label>Pendidikan Terakhir</label>
            <input type="text" name="pendidikan_terakhir" class="form-control">
          </div>
          <div class="col-md-4">
            <label>Jabatan</label>
            <input type="text" name="jabatan" class="form-control">
          </div>
          <div class="col-md-6">
            <label>Unit Kerja</label>
            <select name="unit_kerja" class="form-select">
              <option value="">-- Pilih Unit Kerja --</option>
              <option value="Kantor Direksi PT.TDM">Kantor Direksi PT.TDM</option>
           </select>
          </div>
          <div class="col-md-6">
            <label>Status Karyawan</label>
            <select name="status_karyawan" class="form-select" required>
              <option value="">-- Pilih Status Karyawan --</option>
              <option value="Karpim">Karpim</option>
              <option value="Karpel">Karpel</option>
           </select>
          </div>
          <div class="col-md-6">
            <label>Status Kerja</label>
            <select name="status_kerja" class="form-select" required>
              <option value="">-- Pilih Status Kerja --</option>
              <option value="Aktif">Aktif</option>
              <option value="Non Aktif">Non Aktif</option>
              <option value="Cuti">Cuti</option>
            </select>
          </div>

          <div class="col-md-6">
            <label>Tanggal Masuk</label>
            <input type="date" name="tanggal_bergabung" class="form-control">
          </div>
          <div class="col-md-6">
            <label>No KPTS</label>
            <input type="text" name="no_kpts" class="form-control">
          </div>
          <div class="col-md-6">
            <label>Golongan</label>
            <input type="text" name="golongan" class="form-control">
          </div>
          <div class="col-md-6">
            <label>Tanggal Input</label>
            <input type="date" name="tanggal_input" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="simpan_karpim" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

  <!-- Table -->
  <div class="card">
    <div class="card-body p-2">
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm mb-0">
          <thead class="table-light text-center small">
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
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
<?php
if ($result && $result->num_rows > 0) {
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        ?>
        <tr>
            <td><?= $no ?></td>
            <td><?= htmlspecialchars($row['nik']) ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['jenis_kelamin']) ?></td>
            <td><?= htmlspecialchars($row['tanggal_lahir']) ?></td>
            <td><?= htmlspecialchars($row['alamat']) ?></td>
            <td><?= htmlspecialchars($row['agama']) ?></td>
            <td><?= htmlspecialchars($row['pendidikan_terakhir']) ?></td>
            <td><?= htmlspecialchars($row['jabatan']) ?></td>
            <td><?= htmlspecialchars($row['unit_kerja']) ?></td>
            <td><?= htmlspecialchars($row['status_karyawan']) ?></td>
            <td><?= htmlspecialchars($row['status_kerja']) ?></td>
            <td><?= htmlspecialchars($row['tanggal_bergabung']) ?></td>
            <td><?= htmlspecialchars($row['no_kpts']) ?></td>
            <td><?= htmlspecialchars($row['golongan']) ?></td>
            <td>
                <button class="btn btn-warning btn-sm" 
                        data-bs-toggle="modal" 
                        data-bs-target="#editModal<?= $row['id_karpim'] ?>">
                    <i class="fas fa-edit"></i>
                </button>
                <a href="?hapus=<?= $row['id_karpim'] ?>" 
                   class="btn btn-danger btn-sm" 
                   onclick="return confirm('Yakin ingin menghapus data ini?')">
                    <i class="fas fa-trash"></i>
                </a>
            </td>
        </tr>

        <!-- Modal Edit untuk baris ini (hanya 1x per row) -->
        <div class="modal fade" id="editModal<?= $row['id_karpim'] ?>" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <form method="POST">
                <div class="modal-header">
                  <h5 class="modal-title">Edit Data Karyawan</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-2">
                  <input type="hidden" name="id_karpim" value="<?= $row['id_karpim'] ?>">

                  <div class="col-md-6">
                    <label>NIK</label>
                    <input type="text" name="nik" class="form-control" value="<?= htmlspecialchars($row['nik']) ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label>Nama</label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($row['nama']) ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select">
                      <option value="Laki-laki" <?= $row['jenis_kelamin']=='Laki-laki'?'selected':'' ?>>Laki-laki</option>
                      <option value="Perempuan" <?= $row['jenis_kelamin']=='Perempuan'?'selected':'' ?>>Perempuan</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label>Tanggal Lahir</label>
                    <input type="text" name="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($row['tanggal_lahir']) ?>" required>
                  </div>
                  <div class="col-md-12">
                    <label>Alamat</label>
                    <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($row['alamat']) ?>">
                  </div>
                  <div class="col-md-4">
                    <label>Agama</label>
                    <select name="agama" class="form-select">
                      <?php
                      $agama_list = ["Islam","Kristen","Hindu","Buddha","Konghucu","Lainnya"];
                      foreach($agama_list as $ag){
                          $sel = ($row['agama']==$ag)?'selected':'';
                          echo "<option value='".htmlspecialchars($ag)."' $sel>".htmlspecialchars($ag)."</option>";
                      }
                      ?>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label>Pendidikan Terakhir</label>
                    <input type="text" name="pendidikan_terakhir" class="form-control" value="<?= htmlspecialchars($row['pendidikan_terakhir']) ?>">
                  </div>
                  <div class="col-md-4">
                    <label>Jabatan</label>
                    <input type="text" name="jabatan" class="form-control" value="<?= htmlspecialchars($row['jabatan']) ?>">
                  </div>
                  <div class="col-md-6">
                    <label>Unit Kerja</label>
                    <select name="unit_kerja" class="form-select" required>
                      <option value="Kantor Direksi PT.TDM" <?= $row['unit_kerja']=='Kantor Direksi PT.TDM'?'selected':'' ?>>Kantor Direksi PT.TDM</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label>Status Karyawan</label>
                    <select name="status_karyawan" class="form-select" required>
                      <option value="Karpim" <?= $row['status_karyawan']=='Karpim'?'selected':'' ?>>Karpim</option>
                      <option value="Karpel" <?= $row['status_karyawan']=='Karpel'?'selected':'' ?>>Karpel</option>
                    </select>  
                  </div>
                  <div class="col-md-6">
                    <label>Status Kerja</label>
                    <select name="status_kerja" class="form-select" required>
                      <option value="Aktif" <?= $row['status_kerja']=='Aktif'?'selected':'' ?>>Aktif</option>
                      <option value="Non Aktif" <?= $row['status_kerja']=='Non Aktif'?'selected':'' ?>>Non Aktif</option>
                      <option value="Cuti" <?= $row['status_kerja']=='Cuti'?'selected':'' ?>>Cuti</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label>Tanggal Masuk</label>
                    <input type="date" name="tanggal_bergabung" class="form-control" value="<?= htmlspecialchars($row['tanggal_bergabung']) ?>">
                  </div>
                  <div class="col-md-6">
                    <label>No KPTS</label>
                    <input type="text" name="no_kpts" class="form-control" value="<?= htmlspecialchars($row['no_kpts']) ?>">
                  </div>
                  <div class="col-md-6">
                    <label>Golongan</label>
                    <input type="text" name="golongan" class="form-control" value="<?= htmlspecialchars($row['golongan']) ?>">
                  </div>
                  <div class="col-md-6">
                    <label>Tanggal Input</label>
                    <input type="date" name="tanggal_input" class="form-control" value="<?= htmlspecialchars($row['tanggal_input']) ?>">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="update_karpim" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php
        $no++;
    }
} else {
    echo "<tr><td colspan='16' class='text-center'>Data tidak ditemukan</td></tr>";
}
?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<a href="data_karyawan.php" class="btn btn-danger m-3">Keluar</a>
</body>
</html>