<?php
// KONEKSI DATABASE
include 'config.php';



// Fungsi cek data duplikat
function isDataExists($conn, $nik, $nama, $jenis_kelamin, $tanggal_lahir, $alamat, $agama, $pendidikan_terakhir) {
    $nik = $conn->real_escape_string($nik);
    $nama = $conn->real_escape_string($nama);
    $jenis_kelamin = $conn->real_escape_string($jenis_kelamin);
    $tanggal_lahir = $conn->real_escape_string($tanggal_lahir);
    $alamat = $conn->real_escape_string($alamat);
    $agama = $conn->real_escape_string($agama);
    $pendidikan_terakhir = $conn->real_escape_string($pendidikan_terakhir);

    $query = "SELECT 1 FROM karyawan_klinik_rdb
              WHERE nik = '$nik' 
                AND nama = '$nama' 
                AND jenis_kelamin = '$jenis_kelamin' 
                AND tanggal_lahir = '$tanggal_lahir' 
                AND alamat = '$alamat' 
                AND agama = '$agama' 
                AND pendidikan_terakhir = '$pendidikan_terakhir'
              LIMIT 1";
    $result = $conn->query($query);
    return $result && $result->num_rows > 0;
}

// Proses tambah data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_rdb'])) {
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
    $masa_kontrak = $conn->real_escape_string($_POST['masa_kontrak']);
    $tanggal_input = date('Y-m-d');

    if (isDataExists($conn, $nik, $nama, $jenis_kelamin, $tanggal_lahir, $alamat, $agama, $pendidikan_terakhir)) {
        echo "<div class='alert alert-warning mt-4'>Data karyawan dengan NIK " . htmlspecialchars($nik) . " sudah ada.</div>";
    } else {
        $query = "INSERT INTO karyawan_klinik_rdb
                 (nik, nama, jenis_kelamin, tanggal_lahir, alamat, agama, pendidikan_terakhir, jabatan, unit_kerja, status_karyawan, status_kerja, tanggal_bergabung, masa_kontrak, tanggal_input)
                 VALUES 
                 ('$nik', '$nama', '$jenis_kelamin', '$tanggal_lahir', '$alamat', '$agama', '$pendidikan_terakhir', '$jabatan', '$unit_kerja', '$status_karyawan', '$status_kerja', '$tanggal_bergabung', '$masa_kontrak', '$tanggal_input')";
        $conn->query($query);
    }
}

// Ambil parameter GET
$search_nama = isset($_GET['search_nama']) ? trim($_GET['search_nama']) : '';
$filter      = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$bulan       = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tahun       = isset($_GET['tahun']) ? $_GET['tahun'] : '';

// Build filter status
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

// Build filter nama
$name_sql = "";
if ($search_nama !== '') {
    $safe = $conn->real_escape_string($search_nama);
    $name_sql .= " AND k.nama LIKE '%$safe%'";
}

// Build filter bulan/tahun
$bulan_tahun_sql = "";
if ($bulan !== '' && $tahun !== '') {
    $bulan_tahun_sql .= " AND MONTH(k.tanggal_input) = '$bulan' AND YEAR(k.tanggal_input) = '$tahun'";
} elseif ($bulan !== '') {
    $bulan_tahun_sql .= " AND MONTH(k.tanggal_input) = '$bulan'";
} elseif ($tahun !== '') {
    $bulan_tahun_sql .= " AND YEAR(k.tanggal_input) = '$tahun'";
}

// Query utama
if ($bulan !== '' || $tahun !== '') {
    // jika ada filter bulan/tahun → ambil semua data
    $query = "
        SELECT k.*
        FROM karyawan_klinik_rdb k
        WHERE 1=1
        $filter_sql
        $name_sql
        $bulan_tahun_sql
        ORDER BY k.nama ASC
    ";
} else {
    // jika tidak ada filter bulan/tahun → ambil data terbaru per NIK
    $query = "
        SELECT k.*
        FROM karyawan_klinik_rdb k
        INNER JOIN (
            SELECT nik, MAX(id_rdb) AS keep_id
            FROM karyawan_klinik_rdb
            GROUP BY nik
        ) t ON k.nik = t.nik AND k.id_rdb = t.keep_id
        WHERE 1=1
        $filter_sql
        $name_sql
        ORDER BY k.nama ASC
    ";
}

$result = $conn->query($query);
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Karyawan Klinik Rambutan Deli Binjai (RDB)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-check-label { margin-left: .25rem; }

        /* Tabel & action-bar styling (sama seperti PT.TDM) */
        .table-responsive-custom { overflow:auto; max-height:70vh; border:1px solid #e9ecef; border-radius:.375rem; }
        table.table { min-width:1200px; table-layout: fixed; border-collapse:separate; border-spacing:0; }
        table.table thead th { position: sticky; top:0; z-index:5; background:#f8f9fa; }
        table.table th, table.table td { vertical-align: middle; padding: .55rem .6rem; border-top:1px solid #e9ecef; }
        .col-nik { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .col-name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .col-short { white-space: nowrap; }
        .col-wrap { white-space: normal; word-wrap: break-word; overflow-wrap: break-word; }
        .td-actions { width:90px; text-align:center; }
        .text-center-date { text-align:center; white-space: nowrap; }
        .td-no { width:50px; text-align:center; }
        .small-cell { font-size:.92rem; }
        @media (max-width: 768px) {
            table.table { min-width: 1000px; }
        }
        .action-bar { display:flex; gap:.5rem; align-items:center; margin-top:0.9rem; flex-wrap:wrap; }
        .action-bar .btn { white-space:nowrap; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2> Data Karyawan - Klinik Rambutan Deli Binjai (RDB)</h2>

        <!-- FORM: pencarian + filter
             Perubahan: form membungkus area pencarian, tombol filter dan action-bar
             agar tombol filter tetap submit GET dan tombol fitur berjajar -->
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="search_nama" class="form-label">Cari Nama Karyawan</label>
                <input type="text" class="form-control" id="search_nama" name="search_nama" value="<?php echo htmlspecialchars($search_nama); ?>" placeholder="Masukkan nama karyawan">
            </div>

            <div class="col-md-3">
    <label for="bulan" class="form-label">Filter Bulan</label>
    <select name="bulan" id="bulan" class="form-select">
        <option value="">-- Semua Bulan --</option>
        <?php
        for ($m=1; $m<=12; $m++) {
            $val = str_pad($m,2,"0",STR_PAD_LEFT);
            $selected = ($val == $bulan) ? "selected" : "";
            echo "<option value='$val' $selected>".date('F', mktime(0,0,0,$m,1))."</option>";
        }
        ?>
    </select>
</div>

<div class="col-md-3">
    <label for="tahun" class="form-label">Filter Tahun</label>
    <select name="tahun" id="tahun" class="form-select">
        <option value="">-- Semua Tahun --</option>
        <?php
// mulai dari 2025 sampai 2060
for ($y = 2025; $y <= 2060; $y++) {
    $selected = ($y == $tahun_raw) ? "selected" : "";
    echo "<option value='$y' $selected>$y</option>";
}
?>


        ?>
    </select>
</div>


            <!-- kolom kosong supaya layout rapi pada desktop -->
            <div class="col-md-4">
                <label class="form-label d-md-block d-none">Filter status kerja</label>
                <div class="d-none d-md-block"></div>
            </div>

            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-secondary">Cari</button>
            </div>

            <!-- Action bar: Rekapan Bulanan, Tambah Data, dan tombol Filter status kerja -->
            <div class="col-12">
                <div class="action-bar">
                    <a href="rekap_bln_karyawan_rdb.php" class="btn btn-outline-success">
                        <i class="fas fa-calendar-alt me-1"></i> Rekapan Bulanan
                    </a>

                    <a href="tambah_data_rdb.php" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Tambah Data
                    </a>

                    <!-- bagian kanan: tombol-tombol filter tetap berupa button submit (name="filter") -->
                    <div class="ms-auto d-flex gap-2 flex-wrap">
                        <button type="submit" name="filter" value="all" class="btn <?php echo ($filter==='all') ? 'btn-primary' : 'btn-outline-primary'; ?>">Tampilkan Semua</button>
                        <button type="submit" name="filter" value="active" class="btn <?php echo ($filter==='active') ? 'btn-success' : 'btn-outline-success'; ?>">Tampilkan yang Aktif</button>
                        <button type="submit" name="filter" value="nonactive" class="btn <?php echo ($filter==='nonactive') ? 'btn-danger' : 'btn-outline-danger'; ?>">Tampilkan Tidak Aktif / Resign</button>
                        <button type="submit" name="filter" value="cuti" class="btn <?php echo ($filter==='cuti') ? 'btn-warning' : 'btn-outline-warning'; ?>">Tampilkan yang Cuti</button>
                    </div>
                </div>
            </div>
        </form>
        <!-- End form -->

        <!-- Tabel Data -->
        <div class="card mt-3">
            <div class="card-body p-2">
                <div class="table-responsive-custom">
                    <table class="table table-bordered table-hover table-sm mb-0">
                        <colgroup>
                            <col style="width:50px">   <!-- No -->
                            <col style="width:150px">  <!-- NIK -->
                            <col style="width:220px">  <!-- Nama -->
                            <col style="width:120px">  <!-- Jenis Kelamin -->
                            <col style="width:110px">  <!-- Tgl Lahir -->
                            <col style="width:300px">  <!-- Alamat (wrap) -->
                            <col style="width:90px">   <!-- Agama -->
                            <col style="width:120px">  <!-- Pendidikan -->
                            <col style="width:200px">  <!-- Jabatan (wrap) -->
                            <col style="width:160px">  <!-- Unit Kerja -->
                            <col style="width:120px">  <!-- Status Karyawan -->
                            <col style="width:120px">  <!-- Status Kerja -->
                            <col style="width:110px">  <!-- Tanggal Masuk -->
                            <col style="width:160px">  <!-- Masa Kontrak -->
                            <col style="width:90px">   <!-- Aksi -->
                        </colgroup>
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
                                <th>Masa Kontrak</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            $no = 1;
                            // arrays untuk memastikan tidak ada duplikat
                            $printed_nik = array();
                            $printed_names = array();

                            while($row = $result->fetch_assoc()) {
                                // normalisasi nama: trim, collapse multiple spaces, lowercase
                                $nama_norm = preg_replace('/\s+/', ' ', trim($row['nama']));
                                $nama_key = mb_strtolower($nama_norm, 'UTF-8');

                                // skip jika NIK sudah dicetak
                                if (!empty($row['nik']) && isset($printed_nik[$row['nik']])) {
                                    continue;
                                }
                                // skip jika nama sudah dicetak (fallback untuk NIK kosong/duplikat)
                                if (isset($printed_names[$nama_key])) {
                                    continue;
                                }

                                // tandai sudah dicetak
                                if (!empty($row['nik'])) $printed_nik[$row['nik']] = true;
                                $printed_names[$nama_key] = true;

                                $tgl_bergabung = (!empty($row['tanggal_bergabung']) && $row['tanggal_bergabung'] != '0000-00-00') ? date('d/m/Y', strtotime($row['tanggal_bergabung'])) : '';
                                $tgl_input = (!empty($row['tanggal_input']) && $row['tanggal_input'] != '0000-00-00') ? date('d/m/Y', strtotime($row['tanggal_input'])) : '';

                                echo "<tr class='small-cell'>
                                    <td class='td-no'>".htmlspecialchars($no)."</td>
                                    <td class='col-nik' title='".htmlspecialchars($row['nik'])."'>".htmlspecialchars($row['nik'])."</td>
                                    <td class='col-name' title='".htmlspecialchars($nama_norm)."'>".htmlspecialchars($nama_norm)."</td>
                                    <td class='col-short text-center'>".htmlspecialchars($row['jenis_kelamin'])."</td>
                                    <td class='text-center'>".htmlspecialchars($row['tanggal_lahir'])."</td>
                                    <td class='col-wrap' title='".htmlspecialchars($row['alamat'])."'>".nl2br(htmlspecialchars($row['alamat']))."</td>
                                    <td class='text-center'>".htmlspecialchars($row['agama'])."</td>
                                    <td class='text-center'>".htmlspecialchars($row['pendidikan_terakhir'])."</td>
                                    <td class='col-wrap' title='".htmlspecialchars($row['jabatan'])."'>".htmlspecialchars($row['jabatan'])."</td>
                                    <td class='col-wrap' title='".htmlspecialchars($row['unit_kerja'])."'>".htmlspecialchars($row['unit_kerja'])."</td>
                                    <td class='text-center'>".htmlspecialchars($row['status_karyawan'])."</td>
                                    <td class='text-center'>".htmlspecialchars($row['status_kerja'])."</td>
                                    <td class='text-center-date'>".htmlspecialchars($tgl_bergabung)."</td>
                                    <td class='col-wrap'>".htmlspecialchars($row['masa_kontrak'])."</td>
                                    <td class='td-actions'>
                                        <a href='edit_data_rdb.php?id=".htmlspecialchars($row['id_rdb'])."' class='btn btn-warning btn-sm' title='Edit'><i class='fas fa-edit'></i></a>
                                        <a href='hapus_data_rdb.php?id=".htmlspecialchars($row['id_rdb'])."' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus data ini?\")' title='Hapus'><i class='fas fa-trash'></i></a>
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
                </div> <!-- .table-responsive-custom -->
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="data_karyawan.php" class="btn btn-danger">Keluar</a>
        </div>
    </div>
</body>
</html>