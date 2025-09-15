<?php
date_default_timezone_set('Asia/Jakarta');
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: welcome.php");
    exit();
}

include 'config.php';


$error = $success = "";
$id = $_GET['id'] ?? '';

if (!empty($id)) {
    // Hapus data user
    $stmt = $conn->prepare("DELETE FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "User berhasil dihapus pada " . date('d F Y, H:i') . " WIB!";
    } else {
        $error = "Gagal menghapus user: " . $conn->error;
    }
    $stmt->close();
} else {
    $error = "ID user tidak ditemukan!";
}

$conn->close();

// Kembali ke data_user.php dengan pesan
header("Location: data_user.php?success=" . urlencode($success) . "&error=" . urlencode($error));
exit();
?>