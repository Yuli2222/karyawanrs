*<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM karyawan_klinik_rdb WHERE id_rdb=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: data_karyawan_k_rdb.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}
?>*