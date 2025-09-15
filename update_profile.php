<?php
$koneksi = new mysqli("localhost", "root", "", "karyawan_rs");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    // Ambil data dari form
    $id            = $_POST['id'];
    $nama_lengkap  = $koneksi->real_escape_string($_POST['nama_lengkap']);
    $email         = $koneksi->real_escape_string($_POST['email']);
    $telepon       = $koneksi->real_escape_string($_POST['telepon']);
    $alamat        = $koneksi->real_escape_string($_POST['alamat']);

    // Update ke database
    $query = "UPDATE pengguna SET 
                nama_lengkap = '$nama_lengkap',
                email = '$email',
                telepon = '$telepon',
                alamat = '$alamat',
                updated_at = NOW()
              WHERE id = '$id'";

    if ($koneksi->query($query)) {
        echo "<script>
            alert('Profil berhasil diperbarui!');
            window.location.href = 'profil.php';
        </script>";
    } else {
        echo "<script>
            alert('Gagal memperbarui profil: " . $koneksi->error . "');
            window.history.back();
        </script>";
    }
} else {
    header("Location: profil.php");
    exit;
}
?>
