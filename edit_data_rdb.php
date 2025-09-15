<?php
include 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Cek apakah koneksi berhasil
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Validasi ID
if ($id <= 0) {
    die("ID tidak valid.");
}

// Ambil data lama
$sql = "SELECT * FROM karyawan_klinik_rdb WHERE id_rdb = $id";
$result = mysqli_query($conn, $sql);

// Debug jika query gagal
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

// Cek apakah data ditemukan
if (mysqli_num_rows($result) == 0) {
    echo "Data tidak ditemukan!";
    exit;
}

$data = mysqli_fetch_assoc($result);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitasi input untuk mencegah SQL injection dasar
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $agama = mysqli_real_escape_string($conn, $_POST['agama']);
    $pendidikan_terakhir = mysqli_real_escape_string($conn, $_POST['pendidikan_terakhir']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $unit_kerja = mysqli_real_escape_string($conn, $_POST['unit_kerja']);
    $status_karyawan = mysqli_real_escape_string($conn, $_POST['status_karyawan']);
    $status_kerja = mysqli_real_escape_string($conn, $_POST['status_kerja']);
    $tanggal_bergabung = mysqli_real_escape_string($conn, $_POST['tanggal_bergabung']);
    $masa_kontrak = mysqli_real_escape_string($conn, $_POST['masa_kontrak']);
    $tanggal_input = mysqli_real_escape_string($conn, $_POST['tanggal_input']);

    // Perbaikan sintaks UPDATE: tambahkan koma sebelum tanggal_input, hapus koma sebelum WHERE
    $update = "UPDATE karyawan_klinik_rdb SET
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
        masa_kontrak='$masa_kontrak',
        tanggal_input='$tanggal_input'
        WHERE id_rdb = $id";

    if (mysqli_query($conn, $update)) {
        header("Location: data_karyawan_k_rdb.php");
        exit();
    } else {
        echo "Error saat update: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Data Karyawan Klinik Rambutan Deli Binjai (RDB)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 700px;
            margin: 20px auto;
            padding: 25px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 8px 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .submit-btn {
            background: #007BFF;
            color: white;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Data Karyawan Klinik Rambutan Deli Binjai (RDB)</h1>
        <form method="post">
            <?php
            function selected($value, $option) {
                return $value == $option ? 'selected' : '';
            }
            ?>
            <div class="form-group"><label>NIK:</label><input type="text" name="nik" value="<?= htmlspecialchars($data['nik']) ?>" required></div>
            <div class="form-group"><label>Nama lengkap:</label><input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required></div>
            <div class="form-group">
                <label>Jenis Kelamin:</label>
                <select name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <option value="Laki-laki" <?= selected($data['jenis_kelamin'], 'Laki-laki') ?>>Laki-laki</option>
                    <option value="Perempuan" <?= selected($data['jenis_kelamin'], 'Perempuan') ?>>Perempuan</option>
                </select>
            </div>
            <div class="form-group"><label>Tanggal Lahir:</label><input type="text" name="tanggal_lahir" value="<?= htmlspecialchars($data['tanggal_lahir']) ?>" required></div>
            <div class="form-group"><label>Alamat:</label><input type="text" name="alamat" value="<?= htmlspecialchars($data['alamat']) ?>" required></div>
            <div class="form-group">
                <label>Agama:</label>
                <select name="agama" required>
                    <option value="">-- Pilih --</option>
                    <option value="Islam" <?= selected($data['agama'], 'Islam') ?>>Islam</option>
                    <option value="Kristen" <?= selected($data['agama'], 'Kristen') ?>>Kristen</option>
                    <option value="Katolik" <?= selected($data['agama'], 'Katolik') ?>>Katolik</option>
                    <option value="Hindu" <?= selected($data['agama'], 'Hindu') ?>>Hindu</option>
                    <option value="Buddha" <?= selected($data['agama'], 'Buddha') ?>>Buddha</option>
                    <option value="Konghucu" <?= selected($data['agama'], 'Konghucu') ?>>Konghucu</option>
                </select>
            </div>
            <div class="form-group"><label>Pendidikan Terakhir:</label><input type="text" name="pendidikan_terakhir" value="<?= htmlspecialchars($data['pendidikan_terakhir']) ?>" required></div>
            <div class="form-group"><label>Jabatan:</label><input type="text" name="jabatan" value="<?= htmlspecialchars($data['jabatan']) ?>" required></div>
            <div class="form-group">
                <label>Unit Kerja:</label>
                <select name="unit_kerja" required>
                    <option value="">-- Pilih --</option>
                    <option value="Kantor Direksi PT.TDM" <?= selected($data['unit_kerja'], 'Kantor Direksi PT.TDM') ?>>Kantor Direksi PT.TDM</option>
                    <option value="Rs Tanjung Selamat" <?= selected($data['unit_kerja'], 'Rs Tanjung Selamat') ?>>Rs Tanjung Selamat (RSTS)</option>
                    <option value="Klinik Pratama Garuda" <?= selected($data['unit_kerja'], 'Klinik Pratama Garuda') ?>>Klinik Pratama Garuda</option>
                    <option value="Klinik Pratama Batang Serangan" <?= selected($data['unit_kerja'], 'Klinik Pratama Batang Serangan') ?>>Klinik Pratama Batang Serangan (BTS)</option>
                    <option value="Klinik Rambutan Deli Binjai" <?= selected($data['unit_kerja'], 'Klinik Rambutan Deli Binjai') ?>>Klinik Rambutan Deli Binjai (RDB)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status Karyawan:</label>
                <select name="status_karyawan" required>
                    <option value="">-- Pilih --</option>
                    <option value="Pkwt" <?= selected($data['status_karyawan'], 'Pkwt') ?>>Pkwt</option>
                    <option value="Honor" <?= selected($data['status_karyawan'], 'Honor') ?>>Honor</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status Kerja:</label>
                <select name="status_kerja" required>
                    <option value="">-- Pilih --</option>
                    <option value="Aktif" <?= selected($data['status_kerja'], 'Aktif') ?>>Aktif</option>
                    <option value="Cuti" <?= selected($data['status_kerja'], 'Cuti') ?>>Cuti</option>
                    <option value="Resign" <?= selected($data['status_kerja'], 'Resign') ?>>Resign</option>
                </select>
            </div>
            <div class="form-group"><label>Tanggal Masuk:</label><input type="date" name="tanggal_bergabung" value="<?= htmlspecialchars($data['tanggal_bergabung']) ?>" required></div>
            <div class="form-group"><label>Masa Kontrak:</label><input type="text" name="masa_kontrak" value="<?= htmlspecialchars($data['masa_kontrak']) ?>" required></div>
            <div class="form-group"><label>Tanggal Input:</label><input type="date" name="tanggal_input" value="<?= htmlspecialchars($data['tanggal_input']) ?>" required></div>
            <button type="submit" class="submit-btn">Update Data</button>
        </form>
        <a href="data_karyawan_k_rdb.php" class="back-link">← Kembali ke Daftar Karyawan</a>
    </div>
</body>
</html>