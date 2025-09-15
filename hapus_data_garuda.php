<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM karyawan_klinik_grd WHERE id_klinik_garuda=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: data_karyawan_k_Garuda.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}
?>