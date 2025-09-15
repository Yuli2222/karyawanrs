<?php
$host = "sql100.infinityfree.com";   // hostname dari InfinityFree
$user = "if0_39947784";              // username MySQL kamu
$pass = "YuliAnda22025";             // password MySQL kamu
$db   = "if0_39947784_karyawan_rs";  // nama database

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
} else {
    echo "Koneksi berhasil!";
}
?>
