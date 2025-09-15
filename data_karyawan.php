<?php
// KONEKSI DATABASE
session_start();
include 'config.php';



// AMBIL TOTAL KARYAWAN
$sql_total = "SELECT COUNT(*) AS total FROM karyawan";
$result_total = $conn->query($sql_total);
$total_karyawan = 0;
if ($result_total && $row_total = $result_total->fetch_assoc()) {
    $total_karyawan = $row_total['total'];
}

// Ambil data admin dari database
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
    <title>Sistem Karyawan PT Tembakau Deli Medica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #ffd700;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e6ecef, #d3e0ea);
            color: #333;
            overflow-x: hidden;
        }
        /* Sidebar */
        .sidebar {
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 0;
            position: fixed;
            width: 250px;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        .sidebar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
            color: var(--accent-color);
        }
        .sidebar a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 20px;
            margin: 5px 0;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }
        .sidebar a:hover, .sidebar a.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            border-left: 4px solid var(--accent-color);
        }
        .sidebar i {
            width: 25px;
            text-align: center;
            margin-right: 12px;
        }
        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 25px;
            transition: all 0.3s ease;
        }
        .header-content {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }

        /* Warna Coklat */
.bg-brown {
    background-color: #b26832ff !important; /* SaddleBrown */
    color: white !important;
}

/* Warna Abu-Abu */
.bg-gray {
    background-color: #708090 !important; /* SlateGray */
    color: white !important;
}



        /* Responsive Design */
        @media (max-width: 992px) { /* Tablet */
            .sidebar {
                width: 220px;
            }
            .main-content {
                margin-left: 220px;
                padding: 20px;
            }
        }
        @media (max-width: 768px) { /* iPad & Mobile */
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
        @media (max-width: 576px) { /* Smartphone kecil */
            .header-content h4 {
                font-size: 1.2rem;
            }
            .card h6 {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
     <?php include 'sidebar.php'; ?>
   

    <!-- Main Content -->
    <div class="main-content">
        <div class="header-content d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-users me-2"></i> Data Karyawan</h4>
            <div>
                <span class="me-3"><i class="fas fa-calendar me-1"></i> <?php echo date('d F Y'); ?></span>
            </div>
        </div>

        <!-- Panel Total Karyawan -->
        <div class="row mb-4">
            <div class="col-md-4 col-sm-6 mb-3">
                <a href="total_karyawan.php" style="text-decoration:none;">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <h6 class="mb-1">Total Karyawan</h6>
                            <i class="fas fa-users fa-2x opacity-50"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <h2 class="mb-4">Data Karyawan Per Unit</h2>
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <a href="data_karyawan_k_pttdm.php" style="text-decoration: none;">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Kantor Direksi PT.TDM</h6>
                            <i class="fas fa-briefcase fa-2x opacity-50"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <a href="data_karyawan_rsts.php" style="text-decoration: none;">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Rumah Sakit Tanjung Selamat (RSTS)</h6>
                            <i class="fas fa-hospital fa-2x opacity-50"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <a href="data_karyawan_k_Garuda.php" style="text-decoration: none;">
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Klinik Pratama Garuda</h6>
                            <i class="fas fa-clinic-medical fa-2x opacity-50"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <a href="data_karyawan_k_bts.php" style="text-decoration: none;">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Klinik Pratama Batang Serangan (BTS)</h6>
                            <i class="fas fa-stethoscope fa-2x opacity-50"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <a href="data_karyawan_k_rdb.php" style="text-decoration: none;">
                    <div class="card stat-card bg-danger text-white">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Klinik Rambutan Deli Binjai (RDB)</h6>
                            <i class="fas fa-user-md fa-2x opacity-50"></i>
                        </div>
                    </div>
                </a>
            </div>
             <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <a href="data_karyawan_karpim.php" style="text-decoration: none;">
                    <div class="card stat-card bg-brown text-white">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Karyawan Pimpinan Kantor Direksi PT.TDM (Karpim)</h6>
                            <i class="fas fa-user-tie fa-2x opacity-50"></i>
                        </div>
                    </div>
                </a>
            </div>
             <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <a href="data_karyawan_karpel.php" style="text-decoration: none;">
                    <div class="card stat-card bg-gray text-white">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Karyawan Pelaksana Kantor Direksi PT.TDM (Karpel)</h6>
                            <i class="fas fa-users fa-2x opacity-50"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
