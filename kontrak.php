<?php
// ==============================
// KONEKSI DATABASE
// ==============================
session_start();
include 'config.php';



// Ambil total karyawan
$sql_total = "SELECT COUNT(*) AS total FROM karyawan";
$result_total = $conn->query($sql_total);
$total_karyawan = 0;
if ($result_total && $row_total = $result_total->fetch_assoc()) {
    $total_karyawan = (int)$row_total['total'];
}

// Ambil data admin
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sistem Karyawan PT Tembakau Deli Medica</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
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

    /* Responsive */
    @media (max-width: 992px) {
      .sidebar { width: 220px; }
      .main-content { margin-left: 220px; padding: 20px; }
    }
    @media (max-width: 768px) {
      .sidebar { position: relative; width: 100%; height: auto; }
      .main-content { margin-left: 0; width: 100%; padding: 15px; }
    }
    @media (max-width: 576px) {
      .header-content h4 { font-size: 1.2rem; }
      .card h6 { font-size: 0.9rem; }
      .panel-title { font-size: 1rem; }
      .panel-sub { font-size: 0.8rem; }
    }

    /* Panel stat */
    .stat-top-card {
      border:none; border-radius:14px; color:#fff;
      box-shadow:0 10px 18px rgba(0,0,0,.08);
      transition:transform .2s ease, box-shadow .2s ease;
    }
    .stat-top-card:hover { transform:translateY(-4px); box-shadow:0 14px 24px rgba(0,0,0,.12); }

    /* Grid panel kontrak */
    .panel-link { text-decoration:none; }
    .panel-card {
      border:none; border-radius:18px; color:#fff; position:relative; overflow:hidden;
      box-shadow:0 10px 18px rgba(0,0,0,.08);
      transition:transform .2s ease, box-shadow .2s ease;
      min-height:120px;
    }
    .panel-card:hover { transform:translateY(-5px); box-shadow:0 16px 28px rgba(0,0,0,.14); }
    .panel-card .panel-body { padding:20px; }
    .panel-title { font-weight:700; font-size:1.25rem; margin:0; }
    .panel-sub { opacity:.9; font-size:.9rem; }
    .panel-icon {
      position:absolute; right:16px; bottom:12px; font-size:48px; opacity:.2;
    }
    .panel-spk {
      background:linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }
    .panel-spkwt {
      background:linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
  <!-- Sidebar -->
  

  <!-- Main -->
  <main class="main-content">
    <div class="container-fluid">
      <!-- Header -->
      <div class="header-content d-flex justify-content-between align-items-center flex-wrap">
        <h4 class="mb-2 mb-md-0"><i class="fas fa-file-contract me-2"></i>Kontrak Kerja</h4>
        <span class="text-muted"><i class="fas fa-calendar me-2"></i><?php echo date('d F Y'); ?></span>
      </div>

      <!-- Kartu ringkas -->
      <div class="row g-3 mb-3">
        <div class="col-sm-6 col-lg-4 col-xxl-3">
          <a href="total_kontrak_karyawan.php" class="text-decoration-none">
            <div class="card stat-top-card bg-info">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <p class="panel-title mb-1">Total Kontrak Karyawan</p>
                </div>
                <i class="fas fa-user-friends fa-2x opacity-50"></i>
              </div>
            </div>
          </a>
        </div>
      </div>

      <h5 class="mb-3">Kontrak Kerja</h5>

      <!-- Grid Panel Kontrak -->
      <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
          <a href="list_spk.php" class="panel-link">
            <div class="panel-card panel-spk">
              <div class="panel-body">
                <p class="panel-title mb-1">Daftar SPK</p>
                <p class="panel-sub mb-0">Lihat semua kontrak SPK</p>
                <i class="fa-solid fa-file-signature panel-icon"></i>
              </div>
            </div>
          </a>
        </div>

        <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
          <a href="list_spkwt.php" class="panel-link">
            <div class="panel-card panel-spkwt">
              <div class="panel-body">
                <p class="panel-title mb-1">Daftar SPKWT</p>
                <p class="panel-sub mb-0">Lihat semua kontrak SPKWT</p>
                <i class="fa-solid fa-file-pen panel-icon"></i>
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>
  </main>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
