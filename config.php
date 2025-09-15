<?php
$host = "localhost";
$user = "root"; // contoh username default XAMPP
$pass = ""; // contoh password default XAMPP
$db   = "karyawanrs";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
