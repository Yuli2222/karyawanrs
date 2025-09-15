<?php
date_default_timezone_set('Asia/Jakarta');
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: welcome.php");
    exit();
}

include 'config.php';


$error = $success = "";
$id = $_POST['id'] ?? '';
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($id) && !empty($username) && !empty($password)) {
    // Validasi input
    if (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Cek apakah username sudah digunakan oleh user lain
        $check = $conn->prepare("SELECT id_user FROM users WHERE username = ? AND id_user != ?");
        $check->bind_param("si", $username, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Username sudah digunakan oleh user lain!";
        } else {
            // Update data user
            $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id_user = ?");
            $stmt->bind_param("ssi", $username, $password, $id);
            if ($stmt->execute()) {
                $success = "Data user berhasil diperbarui pada " . date('d F Y, H:i') . " WIB!";
            } else {
                $error = "Gagal memperbarui data user: " . $conn->error;
            }
            $stmt->close();
        }
        $check->close();
    }
} else {
    $error = "Data tidak lengkap!";
}

$conn->close();

// Kembali ke data_user.php dengan pesan
header("Location: data_user.php?success=" . urlencode($success) . "&error=" . urlencode($error));
exit();
?>