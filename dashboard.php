<?php 
// =============================================
// KONEKSI DATABASE & CEK SESSION ADMIN
// =============================================
session_start();
include 'config.php';


// Cek kalau bukan admin, redirect ke login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil data admin dari database berdasarkan sesi
$admin_data = ['id_admin' => '', 'username' => 'Admin', 'status' => 'Tidak Aktif'];
if (isset($_SESSION['admin_username'])) { 
    $username = $_SESSION['admin_username'];
    $query = $conn->prepare("SELECT id_admin, username, status FROM admin WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $admin_data = $result->fetch_assoc();
    } else {
        $admin_data['username'] = $username;
    }
    $query->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Karyawan PT Tembakau Deli Medica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --primary-color: #1e3c72;
        --secondary-color: #2a5298;
    }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: url("asset/img/p3.jpeg") no-repeat center center fixed;
        background-size: cover;
        color: #333;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    .main-content {
        margin-left: 250px;
        padding: 25px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .card {
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border-radius: 50px;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.95);
        max-width: 700px;
        margin: 0 auto;
        padding: 40px 30px;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }
    .btn-about {
        background: var(--primary-color);
        color: white;
        font-weight: bold;
        padding: 12px 25px;
        border-radius: 30px;
        transition: 0.3s;
        text-decoration: none;
    }
    .btn-about:hover {
        background: var(--secondary-color);
        color: white;
    }

    /* =========================
       RESPONSIVE SETTINGS
    ========================== */
    @media (max-width: 992px) { /* Tablet */
        .main-content {
            margin-left: 200px; /* sidebar lebih kecil */
            padding: 20px;
        }
        .card {
            max-width: 90%;
            padding: 30px 20px;
        }
    }

    @media (max-width: 768px) { /* HP besar */
        body {
            flex-direction: column;
        }
        .main-content {
            margin-left: 0; /* sidebar jadi atas */
            padding: 15px;
        }
        .card {
            max-width: 100%;
            border-radius: 25px;
            padding: 25px 15px;
        }
    }

    @media (max-width: 480px) { /* HP kecil */
        .card {
            padding: 20px 10px;
            border-radius: 20px;
        }
        .btn-about {
            padding: 10px 15px;
            font-size: 14px;
        }
        h5.card-title {
            font-size: 18px;
        }
        p.card-text {
            font-size: 14px;
        }
    }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Tentang Perusahaan -->
        <div class="card text-center">
            <i class="fas fa-building fa-3x mb-3 text-primary"></i>
            <h5 class="card-title">Tentang Perusahaan</h5>
            <p class="card-text">
                PT Tembakau Deli Medica merupakan perusahaan yang bergerak di bidang kesehatan 
                dengan unit kerja berupa rumah sakit dan klinik di berbagai wilayah. 
                Sistem informasi ini dibuat untuk mempermudah pengelolaan data karyawan.
            </p>
            <a href="tentang.php" class="btn-about">
                <i class="fas fa-info-circle"></i> Pelajari Lebih Lanjut
            </a>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>