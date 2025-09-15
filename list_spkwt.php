<?php
include 'config.php';
$pesan = '';

// -----------------------------
// PROSES TAMBAH KONTRAK
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_spkwt'])) {
    $nik = isset($_POST['nik']) ? trim($_POST['nik']) : '';
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $no_kontrak = isset($_POST['no_kontrak']) ? trim($_POST['no_kontrak']) : '';
    $unit_kerja = isset($_POST['unit_kerja_input']) ? trim($_POST['unit_kerja_input']) : '';
    $jenis_kontrak = isset($_POST['jenis_kontrak']) ? trim($_POST['jenis_kontrak']) : '';
    $jabatan = isset($_POST['jabatan']) ? trim($_POST['jabatan']) : '';
    $tanggal_mulai = isset($_POST['tanggal_mulai']) ? trim($_POST['tanggal_mulai']) : '';
    $tanggal_selesai = isset($_POST['tanggal_selesai']) ? trim($_POST['tanggal_selesai']) : '';
    $tanggal_input = isset($_POST['tanggal_input']) && trim($_POST['tanggal_input']) !== '' ? trim($_POST['tanggal_input']) : date('Y-m-d');

    if ($nik === '' || $nama === '' || $no_kontrak === '' || $unit_kerja === '' || $jenis_kontrak === '' || $jabatan === '' || $tanggal_mulai === '') {
        $pesan = "<div class='alert alert-danger'>Isi semua field wajib (minimal NIK, Nama, No Kontrak, Unit, Jenis, Jabatan, Tanggal Mulai).</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO spkwt (nik, nama, no_kontrak, unit_kerja, jenis_kontrak, jabatan, tanggal_mulai, tanggal_selesai, tanggal_input) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssssss", $nik, $nama, $no_kontrak, $unit_kerja, $jenis_kontrak, $jabatan, $tanggal_mulai, $tanggal_selesai, $tanggal_input);
            if ($stmt->execute()) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?msg=success");
                exit;
            } else {
                $pesan = "<div class='alert alert-danger'>Gagal menyimpan data: " . htmlspecialchars($stmt->error) . "</div>";
            }
            $stmt->close();
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal mempersiapkan query: " . htmlspecialchars($conn->error) . "</div>";
        }
    }
}

// Pesan sukses tambah
if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
    $pesan = "<div class='alert alert-success'>Data kontrak berhasil ditambahkan.</div>";
}

// -----------------------------
// PROSES UPDATE KONTRAK
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_kontrak'])) {
    $id_edit = isset($_POST['edit_id']) ? (int) $_POST['edit_id'] : 0;
    $nik_edit = isset($_POST['nik_edit']) ? trim($_POST['nik_edit']) : '';
    $nama_edit = isset($_POST['nama_edit']) ? trim($_POST['nama_edit']) : '';
    $no_kontrak_edit = isset($_POST['no_kontrak_edit']) ? trim($_POST['no_kontrak_edit']) : '';
    $unit_kerja_edit = isset($_POST['unit_kerja_edit']) ? trim($_POST['unit_kerja_edit']) : '';
    $jenis_kontrak_edit = isset($_POST['jenis_kontrak_edit']) ? trim($_POST['jenis_kontrak_edit']) : '';
    $jabatan_edit = isset($_POST['jabatan_edit']) ? trim($_POST['jabatan_edit']) : '';
    $tanggal_mulai_edit = isset($_POST['tanggal_mulai_edit']) ? trim($_POST['tanggal_mulai_edit']) : '';
    $tanggal_selesai_edit = isset($_POST['tanggal_selesai_edit']) ? trim($_POST['tanggal_selesai_edit']) : '';
    $tanggal_input_edit = isset($_POST['tanggal_input_edit']) ? trim($_POST['tanggal_input_edit']) : '';

    if ($id_edit <= 0 || $nik_edit === '' || $nama_edit === '' || $no_kontrak_edit === '' || $unit_kerja_edit === '' || $jenis_kontrak_edit === '' || $jabatan_edit === '' || $tanggal_mulai_edit === '') {
        $pesan = "<div class='alert alert-danger'>Field wajib belum lengkap. Pastikan semua field penting terisi.</div>";
    } else {
        if ($tanggal_input_edit === '') {
            $q = $conn->prepare("SELECT tanggal_input FROM spkwt WHERE id = ?");
            if ($q) {
                $q->bind_param("i", $id_edit);
                $q->execute();
                $resq = $q->get_result();
                if ($resq && $resq->num_rows > 0) {
                    $rowq = $resq->fetch_assoc();
                    $tanggal_input_edit = $rowq['tanggal_input'];
                } else {
                    $tanggal_input_edit = date('Y-m-d');
                }
                $q->close();
            } else {
                $tanggal_input_edit = date('Y-m-d');
            }
        }

        $sql_up = "UPDATE spkwt SET nik = ?, nama = ?, no_kontrak = ?, unit_kerja = ?, jenis_kontrak = ?, jabatan = ?, tanggal_mulai = ?, tanggal_selesai = ?, tanggal_input = ? WHERE id = ?";
        $stmt_up = $conn->prepare($sql_up);
        if ($stmt_up) {
            $stmt_up->bind_param("sssssssssi", $nik_edit, $nama_edit, $no_kontrak_edit, $unit_kerja_edit, $jenis_kontrak_edit, $jabatan_edit, $tanggal_mulai_edit, $tanggal_selesai_edit, $tanggal_input_edit, $id_edit);
            if ($stmt_up->execute()) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?msg=updated");
                exit;
            } else {
                $pesan = "<div class='alert alert-danger'>Gagal update: " . htmlspecialchars($stmt_up->error) . "</div>";
            }
            $stmt_up->close();
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal mempersiapkan query update: " . htmlspecialchars($conn->error) . "</div>";
        }
    }
}

// Pesan setelah update
if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    $pesan = "<div class='alert alert-success'>Data kontrak berhasil diperbarui.</div>";
}

// -----------------------------
// PROSES HAPUS KONTRAK
// -----------------------------
if (isset($_GET['hapus_id'])) {
    $hapus_id = (int) $_GET['hapus_id'];
    if ($hapus_id > 0) {
        $check = $conn->prepare("SELECT id FROM spkwt WHERE id = ?");
        if ($check) {
            $check->bind_param("i", $hapus_id);
            $check->execute();
            $res_check = $check->get_result();
            if ($res_check && $res_check->num_rows > 0) {
                $del = $conn->prepare("DELETE FROM spkwt WHERE id = ?");
                if ($del) {
                    $del->bind_param("i", $hapus_id);
                    if ($del->execute()) {
                        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=deleted");
                        exit;
                    } else {
                        $pesan = "<div class='alert alert-danger'>Gagal menghapus data: " . htmlspecialchars($del->error) . "</div>";
                    }
                    $del->close();
                } else {
                    $pesan = "<div class='alert alert-danger'>Gagal mempersiapkan query hapus: " . htmlspecialchars($conn->error) . "</div>";
                }
            } else {
                $pesan = "<div class='alert alert-warning'>Data tidak ditemukan atau sudah dihapus.</div>";
            }
            $check->close();
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal memeriksa data: " . htmlspecialchars($conn->error) . "</div>";
        }
    } else {
        $pesan = "<div class='alert alert-danger'>ID yang diberikan tidak valid.</div>";
    }
}

// Pesan setelah delete
if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $pesan = "<div class='alert alert-success'>Data kontrak berhasil dihapus.</div>";
}

// -----------------------------
// FILTER (unit_kerja, bulan berdasarkan tanggal_input, tahun)
// -----------------------------
$unitFilter = isset($_GET['unit_kerja']) ? $conn->real_escape_string($_GET['unit_kerja']) : '';
$bulan = isset($_GET['bulan']) ? $conn->real_escape_string($_GET['bulan']) : date('F');
$tahun = isset($_GET['tahun']) ? $conn->real_escape_string($_GET['tahun']) : date('Y');

$bulanAngka = date('m', strtotime("$bulan 1, $tahun"));

// Build SQL with filters (filter berdasarkan tanggal_input seperti sebelumnya)
$sql = "SELECT * FROM spkwt WHERE MONTH(tanggal_input) = '$bulanAngka' AND YEAR(tanggal_input) = '$tahun'";
if ($unitFilter != '') {
    $sql .= " AND unit_kerja = '".$conn->real_escape_string($unitFilter)."'";
}
$sql .= " ORDER BY nama ASC";

// -----------------------------
// EXPORT EXCEL (xls)
// -----------------------------
if (isset($_GET['export']) && $_GET['export'] == '1') {
    $resExport = $conn->query($sql);
    $fileName = "kontrak_spkwt_{$bulan}_{$tahun}.xls";
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=\"$fileName\";");
    echo "<table border='1'>
        <tr><th colspan='9'>Rekap Data Kontrak SPKWT Bulan $bulan Tahun $tahun</th></tr>
        <tr>
            <th>No</th>
            <th>NIK</th>
            <th>Nama</th>
            <th>Jabatan</th>
            <th>No Kontrak</th>
            <th>Mulai</th>
            <th>Selesai</th>
            <th>Unit Kerja</th>
            <th>Tanggal Input</th>
        </tr>";
    if ($resExport && $resExport->num_rows > 0) {
        $no = 1;
        while ($r = $resExport->fetch_assoc()) {
            $mulai = $r['tanggal_mulai'] ? date('d/m/Y', strtotime($r['tanggal_mulai'])) : '';
            $selesai = $r['tanggal_selesai'] ? date('d/m/Y', strtotime($r['tanggal_selesai'])) : '';
            $tgl_input = $r['tanggal_input'] ? date('d/m/Y', strtotime($r['tanggal_input'])) : '';
            echo "<tr>
                <td>$no</td>
                <td style=\"mso-number-format:'\@';\">{$r['nik']}</td>
                <td>{$r['nama']}</td>
                <td>{$r['jabatan']}</td>
                <td>{$r['no_kontrak']}</td>
                <td>$mulai</td>
                <td>$selesai</td>
                <td>{$r['unit_kerja']}</td>
                <td>$tgl_input</td>
            </tr>";
            $no++;
        }
    }
    echo "</table>";
    exit;
}

// Untuk tampilan web
$result = $conn->query($sql);

// Buat link export yang mempertahankan filter saat ini
$exportParams = [];
$exportParams['export'] = '1';
if ($unitFilter !== '') $exportParams['unit_kerja'] = $unitFilter;
if ($bulan !== '') $exportParams['bulan'] = $bulan;
if ($tahun !== '') $exportParams['tahun'] = $tahun;
$exportLink = $_SERVER['PHP_SELF'] . '?' . http_build_query($exportParams);

// ================================================
// HITUNG KONTRAK YANG AKAN HABIS (≤30 hari) BERDASARKAN FILTER
// ================================================
$expiring = [];
$res_for_exp = $conn->query($sql);
if ($res_for_exp && $res_for_exp->num_rows > 0) {
    $today = new DateTime();
    $today->setTime(0,0,0);
    while ($re = $res_for_exp->fetch_assoc()) {
        if (!empty($re['tanggal_selesai'])) {
            $end = DateTime::createFromFormat('Y-m-d', $re['tanggal_selesai']);
            if ($end) {
                $end->setTime(0,0,0);
                $diffDays = (int)$today->diff($end)->format('%r%a');
                if ($diffDays >= 0 && $diffDays <= 30) {
                    $expiring[$re['id']] = [
                        'nik' => $re['nik'],
                        'nama' => $re['nama'],
                        'sisa' => $diffDays,
                        'tgl_selesai' => $end->format('d/m/Y'),
                    ];
                }
            }
        }
    }
    $res_for_exp->free();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Kontrak SPKWT</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { padding: 20px; background-color: #f2f2f2; }
        .table th { background-color: #448fdaff; color: white; }
        .container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; margin-bottom: 20px; }
        .btn-kembali { margin-bottom: 15px; }
        .badge-sm { font-size: .85rem; padding: .35rem .5rem; }

        /* Supaya isi tabel memanjang ke samping (tidak membungkus ke baris baru) */
        .table td, .table th {
            white-space: nowrap;
        }
    </style>
</head>
<body>



<div class="container">
    <h2>Daftar Kontrak SPKWT</h2>

    <!-- Tombol kembali, Tambah & Export -->
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="kontrak.php" class="btn btn-secondary btn-kembali"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $exportLink ?>" class="btn btn-outline-success"><i class="fas fa-file-export"></i> Export Excel</a>
            <button class="btn btn-success" data-toggle="modal" data-target="#modalTambah"><i class="fas fa-plus"></i> Tambah Kontrak</button>
        </div>
    </div>

    <!-- Pesan -->
    <?php if ($pesan !== ''): ?>
        <?= $pesan ?>
    <?php endif; ?>

    <!-- Form Filter -->
    <form method="GET" class="row g-3 align-items-end mb-3">
        <div class="col-md-4">
            <label for="unit_kerja" class="form-label">Unit Kerja</label>
            <select name="unit_kerja" class="form-control">
                <option value="">-- Semua Unit --</option>
                <option value="Kantor Direksi PT.TDM" <?= $unitFilter == "Kantor Direksi PT.TDM" ? "selected" : "" ?>>Kantor Direksi PT.TDM</option>
                <option value="Rumah Sakit Tanjung Selamat" <?= $unitFilter == "Rumah Sakit Tanjung Selamat" ? "selected" : "" ?>>Rumah Sakit Tanjung Selamat</option>
                <option value="Klinik Pratama Garuda" <?= $unitFilter == "Klinik Pratama Garuda" ? "selected" : "" ?>>Klinik Pratama Garuda</option>
                <option value="Klinik Pratama Batang Serangan" <?= $unitFilter == "Klinik Pratama Batang Serangan" ? "selected" : "" ?>>Klinik Pratama Batang Serangan</option>
                <option value="Klinik Rambutan Deli Binjai" <?= $unitFilter == "Klinik Rambutan Deli Binjai" ? "selected" : "" ?>>Klinik Rambutan Deli Binjai</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="bulan" class="form-label">Bulan</label>
            <select id="bulan" name="bulan" class="form-select form-control">
                <?php
                $bulanList = [
                    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
                    'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
                    'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
                    'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
                ];
                foreach ($bulanList as $en => $id) {
                    echo "<option value='$en' ".($bulan == $en ? 'selected' : '').">$id</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-3">
            <label for="tahun" class="form-label">Tahun</label>
            <select id="tahun" name="tahun" class="form-select form-control">
                <?php
                for ($i = 2005; $i <= 2050; $i++) {
                    echo "<option value='$i' " . ($tahun == $i ? 'selected' : '') . ">$i</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Tampilkan</button>
        </div>
    </form>

    <!-- Ringkasan NIK - Nama sesuai filter (berdasarkan tanggal_input) -->
    <?php
    $sql_names = "SELECT nik, nama FROM spkwt WHERE MONTH(tanggal_input) = '$bulanAngka' AND YEAR(tanggal_input) = '$tahun'";
    if ($unitFilter != '') {
        $sql_names .= " AND unit_kerja = '" . $conn->real_escape_string($unitFilter) . "'";
    }
    $sql_names .= " ORDER BY nama ASC";
    $res_names = $conn->query($sql_names);
    $names_count = ($res_names) ? $res_names->num_rows : 0;
    ?>
    <div class="mb-3">
        <h5 class="mb-2">Hasil Filter: <small class="text-muted"><?= $names_count ?> karyawan</small></h5>
        <?php if ($names_count > 0): ?>
            <div>
                <?php while ($n = $res_names->fetch_assoc()): ?>
                    <span class="badge badge-primary mr-1 mb-1 badge-sm">
                        <?= htmlspecialchars($n['nik']) ?> - <?= htmlspecialchars($n['nama']) ?>
                    </span>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-muted">Tidak ada kontrak yang sesuai dengan filter bulan/tahun/unit kerja ini.</div>
        <?php endif; ?>
    </div>

    <!-- Notifikasi kontrak akan habis (ditampilkan jika ada) -->
    <?php if (!empty($expiring)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Ada <b><?= count($expiring) ?></b> kontrak karyawan yang akan habis dalam 30 hari:
            <ul class="mb-0">
                <?php foreach ($expiring as $e): ?>
                    <li>
                        <b><?= htmlspecialchars($e['nik']) ?> - <?= htmlspecialchars($e['nama']) ?></b>
                        (Habis: <?= $e['tgl_selesai'] ?>, Sisa <?= $e['sisa'] ?> hari)
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Tabel Data -->
    <div style="overflow-x:auto;">
       <table class="table table-bordered table-striped table-hover table-sm">
            <thead class="text-center">
                <tr>
                    <th>No</th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>No Kontrak</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Unit Kerja</th>
                    <th>Input</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nik']) ?></td>
                            <td<?= isset($expiring[$row['id']]) ? ' style="color:orange;font-weight:bold;"' : '' ?>>
                                <?= htmlspecialchars($row['nama']) ?>
                            </td>
                            <td><?= htmlspecialchars($row['jabatan']) ?></td>
                            <td><?= htmlspecialchars($row['no_kontrak']) ?></td>
                            <td><?= ($row['tanggal_mulai'] ? date('d/m/Y', strtotime($row['tanggal_mulai'])) : '') ?></td>
                            <td>
                                <?php
                                if ($row['tanggal_selesai']) {
                                    $sisaHari = (int)((strtotime($row['tanggal_selesai']) - strtotime(date('Y-m-d'))) / (60*60*24));
                                    $tgl = date('d/m/Y', strtotime($row['tanggal_selesai']));
                                    if ($sisaHari >= 0 && $sisaHari <= 30) {
                                        echo "<span class='badge badge-danger'>$tgl (sisa $sisaHari hari)</span>";
                                    } else {
                                        echo $tgl;
                                    }
                                } else {
                                    echo "";
                                }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row['unit_kerja']) ?></td>
                            <td class="text-center"><?= ($row['tanggal_input'] ? date('d/m/Y', strtotime($row['tanggal_input'])) : '') ?></td>
                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-warning btn-sm btn-edit"
                                        data-id="<?= $row['id'] ?>"
                                        data-nik="<?= htmlspecialchars($row['nik'], ENT_QUOTES) ?>"
                                        data-nama="<?= htmlspecialchars($row['nama'], ENT_QUOTES) ?>"
                                        data-no_kontrak="<?= htmlspecialchars($row['no_kontrak'], ENT_QUOTES) ?>"
                                        data-unit_kerja="<?= htmlspecialchars($row['unit_kerja'], ENT_QUOTES) ?>"
                                        data-jenis_kontrak="<?= htmlspecialchars($row['jenis_kontrak'], ENT_QUOTES) ?>"
                                        data-jabatan="<?= htmlspecialchars($row['jabatan'], ENT_QUOTES) ?>"
                                        data-tanggal_mulai="<?= ($row['tanggal_mulai'] ? date('Y-m-d', strtotime($row['tanggal_mulai'])) : '') ?>"
                                        data-tanggal_selesai="<?= ($row['tanggal_selesai'] ? date('Y-m-d', strtotime($row['tanggal_selesai'])) : '') ?>"
                                        data-tanggal_input="<?= ($row['tanggal_input'] ? date('Y-m-d', strtotime($row['tanggal_input'])) : '') ?>"
                                        data-toggle="modal" data-target="#modalEdit">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <a href="<?= $_SERVER['PHP_SELF'] ?>?hapus_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="10" class="text-center">Tidak ada data</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form method="POST" class="modal-content">
      <input type="hidden" name="tambah_spkwt" value="1">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahLabel">Tambah Kontrak SPKWT</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="nik">NIK *</label>
                <input type="text" class="form-control" id="nik" name="nik" required>
            </div>
            <div class="form-group col-md-8">
                <label for="nama">Nama *</label>
                <input type="text" class="form-control" id="nama" name="nama" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="no_kontrak">No Kontrak *</label>
                <input type="text" class="form-control" id="no_kontrak" name="no_kontrak" required>
            </div>
            <div class="form-group col-md-6">
                <label for="unit_kerja_input">Unit Kerja *</label>
                <select name="unit_kerja_input" id="unit_kerja_input" class="form-control" required>
                    <option value="">-- Pilih Unit --</option>
                    <option value="Kantor Direksi PT.TDM">Kantor Direksi PT.TDM</option>
                    <option value="Rumah Sakit Tanjung Selamat">Rumah Sakit Tanjung Selamat</option>
                    <option value="Klinik Pratama Garuda">Klinik Pratama Garuda</option>
                    <option value="Klinik Pratama Batang Serangan">Klinik Pratama Batang Serangan</option>
                    <option value="Klinik Rambutan Deli Binjai">Klinik Rambutan Deli Binjai</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="jenis_kontrak">Jenis Kontrak *</label>
                <select name="jenis_kontrak" id="jenis_kontrak" class="form-control" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="SPK">SPK</option>
                    <option value="spkwt">spkwt</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="jabatan">Jabatan *</label>
                <input type="text" class="form-control" id="jabatan" name="jabatan" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="tanggal_mulai">Tanggal Mulai *</label>
                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
            </div>
            <div class="form-group col-md-4">
                <label for="tanggal_selesai">Tanggal Selesai</label>
                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai">
            </div>
            <div class="form-group col-md-4">
                <label for="tanggal_input">Tanggal Input</label>
                <input type="date" class="form-control" id="tanggal_input" name="tanggal_input" value="<?= date('Y-m-d') ?>">
                <small class="form-text text-muted">Jika kosong, sistem akan memakai tanggal hari ini.</small>
            </div>
        </div>

        <small class="text-muted">Field bertanda * wajib diisi.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Kontrak</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form method="POST" class="modal-content">
      <input type="hidden" name="update_kontrak" value="1">
      <input type="hidden" name="edit_id" id="edit_id" value="">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditLabel">Edit Kontrak SPKWT</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="nik_edit">NIK *</label>
                <input type="text" class="form-control" id="nik_edit" name="nik_edit" required>
            </div>
            <div class="form-group col-md-8">
                <label for="nama_edit">Nama *</label>
                <input type="text" class="form-control" id="nama_edit" name="nama_edit" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="no_kontrak_edit">No Kontrak *</label>
                <input type="text" class="form-control" id="no_kontrak_edit" name="no_kontrak_edit" required>
            </div>
            <div class="form-group col-md-6">
                <label for="unit_kerja_edit">Unit Kerja *</label>
                <select name="unit_kerja_edit" id="unit_kerja_edit" class="form-control" required>
                    <option value="">-- Pilih Unit --</option>
                    <option value="Kantor Direksi PT.TDM">Kantor Direksi PT.TDM</option>
                    <option value="Rumah Sakit Tanjung Selamat">Rumah Sakit Tanjung Selamat</option>
                    <option value="Klinik Pratama Garuda">Klinik Pratama Garuda</option>
                    <option value="Klinik Pratama Batang Serangan">Klinik Pratama Batang Serangan</option>
                    <option value="Klinik Rambutan Deli Binjai">Klinik Rambutan Deli Binjai</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="jenis_kontrak_edit">Jenis Kontrak *</label>
                <select name="jenis_kontrak_edit" id="jenis_kontrak_edit" class="form-control" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="SPK">SPK</option>
                    <option value="spkwt">spkwt</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="jabatan_edit">Jabatan *</label>
                <input type="text" class="form-control" id="jabatan_edit" name="jabatan_edit" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="tanggal_mulai_edit">Tanggal Mulai *</label>
                <input type="date" class="form-control" id="tanggal_mulai_edit" name="tanggal_mulai_edit" required>
            </div>
            <div class="form-group col-md-4">
                <label for="tanggal_selesai_edit">Tanggal Selesai</label>
                <input type="date" class="form-control" id="tanggal_selesai_edit" name="tanggal_selesai_edit">
            </div>
            <div class="form-group col-md-4">
                <label for="tanggal_input_edit">Tanggal Input</label>
                <input type="date" class="form-control" id="tanggal_input_edit" name="tanggal_input_edit">
                <small class="form-text text-muted">Biarkan kosong untuk menjaga nilai lama.</small>
            </div>
        </div>

        <small class="text-muted">Field bertanda * wajib diisi.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<!-- JS: jQuery + Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // isi modal edit dari data-*
    $('.btn-edit').on('click', function() {
        var button = $(this);
        $('#edit_id').val(button.data('id'));
        $('#nik_edit').val(button.data('nik'));
        $('#nama_edit').val(button.data('nama'));
        $('#no_kontrak_edit').val(button.data('no_kontrak'));
        $('#unit_kerja_edit').val(button.data('unit_kerja'));
        $('#jenis_kontrak_edit').val(button.data('jenis_kontrak'));
        $('#jabatan_edit').val(button.data('jabatan'));
        $('#tanggal_mulai_edit').val(button.data('tanggal_mulai'));
        $('#tanggal_selesai_edit').val(button.data('tanggal_selesai'));
        $('#tanggal_input_edit').val(button.data('tanggal_input') ? button.data('tanggal_input') : '');
        $('#modalEdit').modal('show');
    });

    // bersihkan modal tambah saat ditutup, set tanggal input hari ini
    $('#modalTambah').on('hidden.bs.modal', function () {
        $(this).find('input,select').val('');
        var today = new Date().toISOString().substr(0,10);
        $('#tanggal_input').val(today);
    });
});
</script>

</body>
</html>