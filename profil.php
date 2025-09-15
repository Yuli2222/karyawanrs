<?php
session_start();
// KONEKSI DATABASE
include 'config.php';

// Ambil data admin dari database berdasarkan sesi
$admin_data = ['id_admin' => '', 'username' => 'Admin', 'status' => 'Tidak Aktif'];
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $query = $conn->prepare("SELECT id_admin, username, status FROM admin WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $admin_data = $result->fetch_assoc();
    } else {
        // Debug kalau data nggak ketemu
        $admin_data['username'] = $username; // Paksa pake sesi kalau nggak ketemu di db
    }
    $query->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Sistem Karyawan PT Tembakau Deli Medica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #ffd700;
            --green-color: #28a745;
            --green-dark: #218838;
            --red-soft: #d9534f;
            --red-soft-dark: #c9302c;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e6ecef, #d3e0ea);
            color: #333;
            overflow-x: hidden;
        }
        .sidebar {
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 0;
            position: fixed;
            width: 250px;
            transition: all 0.3s ease;
        }
        .sidebar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
            color: var(--accent-color);
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
        }
        .user-profile {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .user-profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid var(--accent-color);
            margin-right: 15px;
            transition: all 0.3s ease;
        }
        .user-profile img:hover {
            transform: scale(1.1);
        }
        .user-profile .fw-bold {
            color: var(--accent-color);
            font-size: 1.1rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
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
            transform: translateX(5px);
        }
        .sidebar i {
            width: 25px;
            text-align: center;
            margin-right: 12px;
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
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 20px rgba(0, 180, 216, 0.3);
            transition: all 0.3s ease;
        }
        .profile-img:hover {
            box-shadow: 0 5px 25px rgba(0, 180, 216, 0.5);
            transform: scale(1.05);
        }
        .profile-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--accent-color);
            color: white;
            border-radius: 50px;
            padding: 5px 10px;
            font-size: 0.8rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .info-card {
            border-left: 4px solid var(--accent-color);
        }
        .form-control:read-only {
            background-color: #f8f9fa;
            border-color: #e9ecef;
        }
        .btn-edit {
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            border: none;
            transition: all 0.3s ease;
        }
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 180, 216, 0.3);
        }
        .social-links {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .social-links a {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f1f1f1;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        .social-links a:hover {
            background: var(--accent-color);
            color: white;
            transform: translateY(-3px);
        }
        footer {
            background: rgba(255, 255, 255, 0.9);
            padding: 10px 0;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        @media (max-width: 768px) {
            .sidebar { width: 200px; }
            .main-content { margin-left: 200px; width: calc(100% - 200px); }
            .card { margin-bottom: 15px; }
        }
        @media (max-width: 576px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; width: 100%; padding: 15px; }
            .header-content { padding: 15px; }
            .user-profile img { width: 40px; height: 40px; }
            .user-profile .fw-bold { font-size: 1rem; }
            .card { margin-bottom: 10px; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <!-- Main Content -->
    <div class="main-content">
        <div class="header-content d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-user me-2"></i>Profile</h4>
            <div>
                <span class="me-3"><i class="fas fa-calendar me-1"></i> <?php echo date('d F Y'); ?></span>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center position-relative">
                        <span class="profile-badge"><i class="fas fa-star me-1"></i>Admin</span>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin_data['username']); ?>&size=150&background=0077b6&color=fff" class="profile-img mb-3" alt="Profile">
                        <h4><?php echo $admin_data['username']; ?></h4>
                        <p class="text-muted mb-4">Administrator Sistem</p>
                        
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">Profile completeness: 100%</small>
                        
                        <div class="social-links">
                            <a href="https://www.instagram.com/pttembakaudelimedica/"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="card info-card">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-info-circle me-2 text-accent"></i>Quick Info</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="fas fa-calendar-alt me-2 text-muted"></i> Bergabung</span>
                                <span class="badge bg-light text-dark">01 Jan 2020</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="fas fa-clock me-2 text-muted"></i> Terakhir Login</span>
                                <span class="badge bg-light text-dark"><?php echo date('d M H:i'); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="fas fa-shield-alt me-2 text-muted"></i> Status</span>
                                <span class="badge <?php echo $admin_data['status'] == 'Aktif' ? 'bg-success' : 'bg-danger'; ?>"><?php echo $admin_data['status']; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-accent"></i>Informasi Profile</h5>
                        <small class="text-muted">Terakhir update: <?php echo date('d M Y'); ?></small>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Pengguna <span class="text-muted">(required)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" value="<?php echo $admin_data['username']; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                                        <input type="text" class="form-control" value="<?php echo $admin_data['username']; ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" value="admin@rsdeliserdang.com" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. Telepon</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="text" class="form-control" value="081234567890" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <textarea class="form-control" rows="3" readonly>JL Lokasi Tanjung Morawa, No. 39, Bangun Sari, Tanjung Morawa, Deli Serdang, Medan</textarea>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Bergabung</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                        <input type="text" class="form-control" value="01 Jan 2020" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Role</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                        <input type="text" class="form-control" value="Admin" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-circle"></i></span>
                                        <input type="text" class="form-control" value="<?php echo $admin_data['status']; ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                
        
        
    </div>
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update tanggal dan waktu
        function updateDateTime() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            document.getElementById('datetime').innerText = now.toLocaleDateString('id-ID', options);
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>
</body>
</html>
<?php $conn->close(); ?>