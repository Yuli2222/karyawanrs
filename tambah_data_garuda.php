<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nik = $_POST['nik'];
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $agama = $_POST['agama'];
    $pendidikan_terakhir = $_POST['pendidikan_terakhir'];
    $jabatan = $_POST['jabatan'];
    $unit_kerja = $_POST['unit_kerja'];
    $status_karyawan = $_POST['status_karyawan'];
    $status_kerja = $_POST['status_kerja'];
    $tanggal_bergabung = $_POST['tanggal_bergabung'];
    $masa_kontrak = $_POST['masa_kontrak'];
    $tanggal_input = $_POST['tanggal_input'];

    $sql = "INSERT INTO karyawan_klinik_grd
        (nik, nama, jenis_kelamin, tanggal_lahir, alamat, agama, pendidikan_terakhir, jabatan, unit_kerja, status_karyawan, status_kerja, tanggal_bergabung, masa_kontrak, tanggal_input)
        VALUES 
        ('$nik', '$nama', '$jenis_kelamin', '$tanggal_lahir', '$alamat', '$agama', '$pendidikan_terakhir', '$jabatan', '$unit_kerja', '$status_karyawan', '$status_kerja', '$tanggal_bergabung', '$masa_kontrak', '$tanggal_input')";

    if ($conn->query($sql) === TRUE) {
        header("Location: data_karyawan_k_Garuda.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Data Karyawan Klinik Pratama Garuda</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 700px; margin: 20px auto; padding: 25px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 5px; }
        .submit-btn { background: #4CAF50; color: white; padding: 10px; width: 100%; border: none; border-radius: 5px; cursor: pointer; }
        .submit-btn:hover { background: #45a049; }
        .back-link { display: block; margin-top: 20px; text-align: center; text-decoration: none; color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tambah Data Karyawan Klinik Pratama Garuda</h1>
        <form method="post">
            <div class="form-group">
                <label>NIK:</label>
                <input type="text" name="nik" required>
            </div>
            <div class="form-group">
                <label>Nama lengkap:</label>
                <input type="text" name="nama" required>
            </div>
            <div class="form-group">
                <label>Jenis Kelamin:</label>
                <select name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>
            <div class="form-group">
                <label> Tanggal Lahir:</label>
                <input type="text" name="tanggal_lahir" required>
            </div>
            <div class="form-group">
                <label>Alamat:</label>
                <input type="text" name="alamat" required>
            </div>
            <div class="form-group">
                <label>Agama:</label>
                <select name="agama" required>
                    <option value="">-- Pilih --</option>
                    <option value="Islam">Islam</option>
                    <option value="Kristen">Kristen</option>
                    <option value="Katolik">Katolik</option>
                    <option value="Hindu">Hindu</option>
                    <option value="Buddha">Buddha</option>
                    <option value="Konghucu">Konghucu</option>
                </select>
            </div>
            <div class="form-group">
                <label>Pendidikan Terakhir:</label>
                <input type="text" name="pendidikan_terakhir" required>
            </div>     
            <div class="form-group">
                <label>Jabatan:</label>
                <input type="text" name="jabatan" required>
            </div>
            <div class="form-group">
                <label>Unit kerja:</label>
                <select name="unit_kerja" required>
                    <option value="">-- Pilih --</option>
                    <option value="Kantor Direksi PT.TDM">Kantor Direksi PT.TDM</option>
                    <option value="Rs Tanjung Selamat">Rs Tanjung Selamat (RSTS)</option>
                    <option value="Klinik Pratama Garuda">Klinik Pratama Garuda</option>
                    <option value="Klinik Pratama Batang Serangan">Klinik Pratama Batang Serangan (BTS)</option>
                    <option value="Klinik Rambutan Deli Binjai">Klinik Rambutan Deli Binjai (RDB)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status Karyawan:</label>
                <select name="status_karyawan" required>
                    <option value="">-- Pilih --</option>
                    <option value="Pkwt">Pkwt</option>
                    <option value="Honor">Honor</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status Kerja:</label>
                <select name="status_kerja" required>
                    <option value="">-- Pilih --</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Cuti">Cuti</option>
                    <option value="Resign">Resign</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal Masuk:</label>
                <input type="date" name="tanggal_bergabung" required>
            </div>
            <div class="form-group">
                <label>Masa Kontrak:</label>
                <input type="text" name="masa_kontrak" required>
            </div>
           <div class="form-group">
                <label>Tanggal Input:</label>
                <input type="date" name="tanggal_input" required>
            </div>
            <button type="submit" class="submit-btn">Simpan Data</button>
        </form>
        <a href="data_karyawan_k_Garuda.php" class="back-link">← Kembali ke Daftar Karyawan</a>
    </div>
</body>
</html>