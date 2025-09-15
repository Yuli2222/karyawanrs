<?php
$koneksi = new mysqli("localhost", "root", "", "karyawan_rs");
$query = $koneksi->query("SELECT * FROM pengguna WHERE username = 'admin'");
$data = $query->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h3>Edit Profil Admin</h3>
    <form action="update_profil.php" method="POST">
        <input type="hidden" name="id" value="<?= $data['id'] ?>">

        <div class="mb-3">
            <label>Nama Lengkap</label>
            <input type="text" class="form-control" name="nama_lengkap" value="<?= $data['nama_lengkap'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="email" value="<?= $data['email'] ?>" required>
        </div>
        <div class="mb-3">
            <label>No Telepon</label>
            <input type="text" class="form-control" name="telepon" value="<?= $data['telepon'] ?>">
        </div>
        <div class="mb-3">
            <label>Alamat</label>
            <textarea class="form-control" name="alamat" rows="3"><?= $data['alamat'] ?></textarea>
        </div>
        <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
        <a href="profil.php" class="btn btn-secondary">Batal</a>
    </form>
</body>
</html>
