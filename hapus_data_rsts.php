<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM karyawan_rsts WHERE id_rsts=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: data_karyawan_rsts.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}
?>
