<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "karyawan_rs";
$conn = new mysqli($host, $user, $pass, $db);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_user = $_POST['id_user'];
    $query = "DELETE FROM users WHERE id_user=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();

    header("Location: data_user.php");
    exit();
}
?>
