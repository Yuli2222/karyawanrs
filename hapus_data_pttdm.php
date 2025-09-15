<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM karyawan_kantor_pttdm WHERE id_pttdm=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: data_karyawan_k_pttdm.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}
?>