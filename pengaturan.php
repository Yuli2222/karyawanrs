<?php
session_start();
// Validasi session

// Koneksi ke database
include 'config.php';

// Ambil data admin dari database berdasarkan sesi
$admin_data = ['id_admin' => '', 'username' => 'Admin', 'password' => '', 'status' => 'admin'];
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $query = $conn->prepare("SELECT id_admin, username, password, status FROM admin WHERE username = ?");
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
$error = $success = "";
if (isset($_POST['simpan'])) {
    $id_admin = $admin_data['id_admin'];
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    // Validasi input
    if (empty($username)) {
        $error = "Username tidak boleh kosong!";
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Prepared statement untuk keamanan
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin SET username = ?, password = ? WHERE id_admin = ?");
            $stmt->bind_param("ssi", $username, $hashed_password, $id_admin);
        } else {
            $stmt = $conn->prepare("UPDATE admin SET username = ? WHERE id_admin = ?");
            $stmt->bind_param("si", $username, $id_admin);
        }
        if ($stmt === false) {
            $error = "Error preparing query: " . $conn->error;
        } elseif ($stmt->execute()) {
            $success = "Data berhasil diperbarui!";
            // Update sesi
            $admin_data['username'] = $username;
            $_SESSION['username'] = $username;
        } else {
            $error = "Gagal menyimpan: " . $stmt->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - Sistem Karyawan PT Tembakau Deli Medica</title>
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
            --red-dark: #c82333;
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
            background: rgba(255, 255, 255, 0.95);
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
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
        .form-group {
            margin-bottom: 20px;
            text-align: left;
            max-width: 500px;
        }
        .form-group label {
            font-weight: 500;
            display: block;
            margin-bottom: 6px;
            color: #333;
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            color: white;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-simpan {
            background-color: var(--primary-color);
        }
        .btn-kembali {
            background-color: #6c757d;
        }
        .alert {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-left: 4px solid #28a745;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 14px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-left: 4px solid #dc3545;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 14px;
        }
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .modal-content .btn {
            margin: 10px 5px;
            padding: 8px 15px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: var(--primary-color);
            text-decoration: none;
        }
        @media (max-width: 768px) {
            .sidebar { width: 200px; }
            .main-content { margin-left: 200px; width: calc(100% - 200px); }
            .card { margin-bottom: 15px; }
            .modal-content { margin: 20% auto; }
        }
        @media (max-width: 576px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; width: 100%; padding: 15px; }
            .header-content { padding: 15px; }
            .user-profile img { width: 40px; height: 40px; }
            .user-profile .fw-bold { font-size: 1rem; }
            .card { margin-bottom: 10px; }
            .modal-content { width: 90%; margin: 25% auto; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <!-- Main Content -->
    <div class="main-content">
        <div class="header-content d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fa fa-cog me-2"></i>Pengaturan Akun</h4>
            <div>
                <span class="me-3"><i class="fas fa-calendar me-1"></i> <?php echo date('d F Y'); ?></span>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="form-group">
                        <label><i class="fa fa-user"></i> Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($admin_data['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa fa-lock"></i> Password Baru (Kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" placeholder="Masukkan password baru (min 6 karakter)">
                    </div>
                    <button type="submit" name="simpan" class="btn btn-simpan"><i class="fa fa-save"></i> Simpan Perubahan</button>
                    <a href="dashboard.php" class="btn btn-kembali"><i class="fa fa-arrow-left"></i> Kembali</a>
                </form>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-4 text-center text-muted">
            <p class="mb-0">
                &copy; 2025 PT TEMBAKAU DELI MEDICA |
                <span id="datetime"></span>
            </p>
        </footer>
    </div>

    <!-- Modal Notifikasi -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <i class="fas fa-check-circle fa-3x mb-3" style="color: var(--green-color);"></i>
            <h4>Sukses!</h4>
            <p>Data Anda telah berhasil diperbarui.</p>
            <button class="btn btn-simpan" onclick="closeModal()">Oke</button>
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

        // Modal Notifikasi
        function showModal() {
            document.getElementById('successModal').style.display = 'block';
        }
        function closeModal() {
            document.getElementById('successModal').style.display = 'none';
            window.location.href = 'pengaturan.php';
        }
        // Trigger modal jika ada success
        <?php if ($success): ?>
            showModal();
        <?php endif; ?>

        // Tutup modal jika klik di luar
        window.onclick = function(event) {
            const modal = document.getElementById('successModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>