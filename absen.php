<?php
session_start(); // Tambah session_start() di awal
// KONEKSI DATABASE
include 'config.php';

// Ambil nama admin dari database berdasarkan sesi
$admin_name = "Admin";
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $query = $conn->prepare("SELECT username FROM admin WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $admin_name = $admin['username'] ?: $username;
    }
    $query->close();
}

// Buat statistik absensi per unit
$unit_stats = [
    'Kantor Direksi PT.TDM' => 0,
    'Rumah Sakit Tanjung Selamat (RSTS)' => 0,
    'Klinik Pratama Garuda' => 0,
    'Klinik Pratama Batang Serangan (BTS)' => 0,
    'Klinik Rambutan Deli Binjai (RDB)' => 0,
    'Karyawan Pimpinan Kantor Direksi PT. TDM' => 0,
    'Karyawan Pelaksana Kantor Direksi PT. TDM' => 0
];

$total_karyawan = 0;

$table_map = [
    'Kantor Direksi PT.TDM' => 'karyawan_kantor_pttdm',
    'Rumah Sakit Tanjung Selamat (RSTS)' => 'karyawan_rsts',
    'Klinik Pratama Garuda' => 'karyawan_klinik_grd',
    'Klinik Pratama Batang Serangan (BTS)' => 'karyawan_klinik_bts',
    'Klinik Rambutan Deli Binjai (RDB)' => 'karyawan_klinik_rdb',
    'Karyawan Pimpinan Kantor Direksi PT. TDM' => 'karyawan_karpim_pttdm',
    'Karyawan Pelaksana Kantor Direksi PT. TDM' => 'karyawan_karpel_pttdm'
];

foreach ($unit_stats as $unit => $val) {
    $table = $table_map[$unit];
    $query = "SELECT COUNT(*) as total FROM $table";
    $res = mysqli_query($conn, $query);
    if ($res) {
        $data = mysqli_fetch_assoc($res);
        $unit_stats[$unit] = (int) $data['total'];
        $total_karyawan += (int) $data['total'];
    }
}

function unitToFileName($unit) {
    $map = [
        'Kantor Direksi PT.TDM' => 'absensi_pttdm.php',
        'Rumah Sakit Tanjung Selamat (RSTS)' => 'absensi_rsts.php',
        'Klinik Pratama Garuda' => 'absensi_garuda.php',
        'Klinik Pratama Batang Serangan (BTS)' => 'absensi_bts.php',
        'Klinik Rambutan Deli Binjai (RDB)' => 'absensi_rdb.php',
        'Karyawan Pimpinan Kantor Direksi PT. TDM' => 'absensi_karpim.php',
        'Karyawan Pelaksana Kantor Direksi PT. TDM' => 'absensi_karpel.php'
    ];
    return $map[$unit] ?? '#';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Karyawan - PT Tembakau Deli Medica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
        --primary-color: #1e3c72;
        --secondary-color: #2a5298;
        --accent-color: #ffd700;
        --red-soft: #d9534f;
        --red-soft-dark: #c9302c;
        --green-color: #28a745;
        --green-dark: #218838;
        --red-dark: #dc3545;
        }
        body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #e6ecef, #d3e0ea);
        color: #333;
        overflow-x: hidden;
        }
        .main-content {
        margin-left: 250px;
        padding: 25px;
        width: calc(100% - 250px);
        transition: all 0.3s ease;
        }
        .header-content {
        background: white;
        padding: 20px 25px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
        border-left: 5px solid var(--primary-color);
        }
         .card {
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        }
        .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        .stat-card.total {
            background: linear-gradient(135deg, var(--red-soft), var(--red-soft-dark));
        }
        /* Warna khusus tiap unit */
    .stat-card.direksi { background: #007bff; color: white; }     /* Biru */
    .stat-card.rsts    { background: #28a745; color: white; }     /* Hijau */
    .stat-card.garuda  { background: #ffc107; color: white; }     /* Kuning */
    .stat-card.bts     { background: #00a9cf; color: white; }     /* Cyan */
    .stat-card.rdb     { background: #dc3545; color: white; }     /* Merah */
    .stat-card.karpim  { background: #8B4513; color: white; }     /* Coklat Gelap */
    .stat-card.karpel  { background: #6c757d; color: white; }     /* Abu-abu */

    .icon-lg {
        font-size: 2rem;
        opacity: 0.75;
        }
        @media (max-width: 768px) {
        .main-content { margin-left: 200px; width: calc(100% - 200px); }
        .card { margin-bottom: 15px; }
    }
    @media (max-width: 576px) {
        .main-content { margin-left: 0; width: 100%; padding: 15px; }
        .header-content { padding: 15px; }
        .card { margin-bottom: 10px; }
    }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <!-- Main Content -->
    <div class="main-content">
        <div class="header-content d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Absensi Karyawan</h4>
            <span><i class="fas fa-calendar me-1"></i> <?php echo date('d F Y'); ?></span>
        </div>
        
        <!-- Panel Per Unit -->
       <div class="row">
    <?php
    $icons = [
        'Kantor Direksi PT.TDM' => 'fa-briefcase',
        'Rumah Sakit Tanjung Selamat (RSTS)' => 'fa-hospital',
        'Klinik Pratama Garuda' => 'fa-clinic-medical',
        'Klinik Pratama Batang Serangan (BTS)' => 'fa-stethoscope',
        'Klinik Rambutan Deli Binjai (RDB)' => 'fa-user-md',
        'Karyawan Pimpinan Kantor Direksi PT. TDM' => 'fa-user-tie',
        'Karyawan Pelaksana Kantor Direksi PT. TDM' => 'fa-users'
    ];

    foreach ($unit_stats as $unit => $count):
        // mapping class sesuai unit
        if ($unit === 'Kantor Direksi PT.TDM') {
            $class = 'direksi';
        } elseif ($unit === 'Rumah Sakit Tanjung Selamat (RSTS)') {
            $class = 'rsts';
        } elseif ($unit === 'Klinik Pratama Garuda') {
            $class = 'garuda';
        } elseif ($unit === 'Klinik Pratama Batang Serangan (BTS)') {
            $class = 'bts';
        } elseif ($unit === 'Klinik Rambutan Deli Binjai (RDB)') {
            $class = 'rdb';
        } elseif ($unit === 'Karyawan Pimpinan Kantor Direksi PT. TDM') {
            $class = 'karpim';
        } elseif ($unit === 'Karyawan Pelaksana Kantor Direksi PT. TDM') {
            $class = 'karpel';
        } else {
            $class = '';
        }
    ?>
        <div class="col-md-4 mb-3">
            <a href="<?php echo unitToFileName($unit); ?>" style="text-decoration: none;">
                <div class="card stat-card <?php echo $class; ?>">
                    <div class="card-body">
                        <i class="fas <?php echo $icons[$unit]; ?> icon-lg"></i>
                        <h6 class="mt-2"><?php echo $unit; ?></h6>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

    </div>
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
