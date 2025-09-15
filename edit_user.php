<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "karyawan_rs";
$conn = new mysqli($host, $user, $pass, $db);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_user  = $_POST['id_user'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role     = $_POST['role'];

    $query = "UPDATE users SET username=?, password=?, role=? WHERE id_user=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $username, $password, $role, $id_user);
    $stmt->execute();

    header("Location: data_user.php");
    exit();
}
?>
