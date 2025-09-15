<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Cek session admin dengan fallback
if (!isset($_SESSION['admin_session']) || $_SESSION['admin_session'] !== true) {
    echo ''; // Kasih output kosong biar nggak crash layout
    exit();
}
// Ambil data admin dari sesi dengan penyesuaian case
$admin_data = ['id_admin' => '', 'username' => 'admin', 'status' => 'Aktif']; // Default ke "admin" dan status Aktif
if (isset($_SESSION['admin_username'])) {
    $admin_data['username'] = strtolower($_SESSION['admin_username']); // Pastikan huruf kecil
    $admin_data['status'] = isset($_SESSION['status']) ? $_SESSION['status'] : 'Aktif'; // Default ke Aktif
    // Tambah fallback role kalau hilang
    if (!isset($_SESSION['role'])) {
        $_SESSION['role'] = 'admin';
    }
}
?>
<style>
    :root {
        --primary-color: #1e3c72;
        --secondary-color: #2a5298;
        --accent-color: #ffd700;
    }
    .sidebar {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 20px 0;
        position: fixed;
        width: 250px;
        transition: all 0.3s ease;
        overflow-y: auto;
        box-sizing: border-box;
        height: 100%;
        padding-bottom: 20px;
        z-index: 1000;
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
    .submenu {
        display: none;
        padding-left: 40px;
    }
    .sidebar a.has-submenu {
        cursor: pointer;
        position: relative;
    }
    .sidebar a.has-submenu::after {
        content: '\f0d7';
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        right: 20px;
        transition: transform 0.3s ease;
    }
    .sidebar a.has-submenu.active::after {
        transform: rotate(180deg);
    }
    .submenu.active {
        display: block;
    }
    .submenu a {
        padding: 8px 20px;
        margin: 2px 0;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
    }
    .submenu a i {
        margin-right: 10px;
    }
    /* Modal Logout Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        justify-content: center;
        align-items: center;
    }
    .modal-content {
        background: linear-gradient(135deg, #2a2a2a, var(--accent-color));
        color: white;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        width: 90%;
        max-width: 350px;
        box-shadow: 0 8px 20px rgba(255, 215, 0, 0.4);
        border: 2px solid #fff;
        animation: popIn 0.3s ease-out;
    }
    @keyframes popIn {
        from { opacity: 0; transform: scale(0.8); }
        to { opacity: 1; transform: scale(1); }
    }
    .modal-content h3 {
        margin-bottom: 10px;
        font-size: 1.3rem;
        color: #fff;
        text-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
    }
    .modal-content p {
        margin-bottom: 15px;
        font-size: 1rem;
        color: #e0e0e0;
    }
    .modal-content .btn-container {
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }
    .modal-content .btn {
        padding: 8px 18px;
        margin: 0;
        font-weight: 700;
        border-radius: 6px;
        transition: all 0.3s ease;
        flex: 1;
    }
    .btn-yes {
        background-color: #dc3545;
        border: 1px solid #c82333;
    }
    .btn-yes:hover {
        background-color: #c82333;
        transform: scale(1.1);
        box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
    }
    .btn-no {
        background-color: #343a40;
        border: 1px solid #292d32;
    }
    .btn-no:hover {
        background-color: #292d32;
        transform: scale(1.1);
        box-shadow: 0 0 10px rgba(52, 58, 64, 0.5);
    }
    .close {
        position: absolute;
        top: 8px;
        right: 12px;
        font-size: 20px;
        color: #fff;
        cursor: pointer;
        transition: color 0.3s ease;
    }
    .close:hover {
        color: var(--accent-color);
        text-shadow: 0 0 5px #fff;
    }
    @media (max-width: 768px) {
        .sidebar { width: 200px; }
        .submenu { padding-left: 30px; }
        .modal-content { margin: 15px; }
    }
    @media (max-width: 576px) {
        .sidebar { width: 100%; height: auto; position: relative; }
        .user-profile img { width: 40px; height: 40px; }
        .user-profile .fw-bold { font-size: 1rem; }
        .submenu { padding-left: 20px; }
        .modal-content { width: 85%; margin: 20px; }
    }
</style>
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-hospital me-2"></i>PT TEMBAKAU DELI MEDICA
    </div>
    <div class="user-profile">
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin_data['username']); ?>&background=random" alt="User">
        <div>
            <div class="fw-bold"><?php echo $admin_data['username']; ?></div>
            <small><?php echo $admin_data['status']; ?></small>
        </div>
    </div>
    <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a>
    <a href="data_karyawan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'data_karyawan.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Data Karyawan</a>
    <a href="absen.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'absen.php' ? 'active' : ''; ?>"><i class="fas fa-calendar-check"></i> Absensi Karyawan</a>
    <a href="data_user.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'data_user.php' ? 'active' : ''; ?>"><i class="fas fa-users-cog"></i> Akun Absensi Karyawan</a>
    <a href="kontrak.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kontrak.php' ? 'active' : ''; ?>"><i class="fas fa-file-signature"></i> Kontrak Kerja</a>
    <a href="profil.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>"><i class="fa fa-user"></i> Profil</a>
    <a href="pengaturan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pengaturan.php' ? 'active' : ''; ?>"><i class="fa fa-cog"></i> Pengaturan</a>
    <a href="#" onclick="toggleLogoutModal(event)"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
<!-- Modal Logout -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeLogoutModal()">&times;</span>
        <h3>Konfirmasi Keluar</h3>
        <p>Apakah Anda yakin ingin keluar dari akun ini?</p>
        <div class="btn-container">
            <button class="btn btn-yes" onclick="logout()">Ya</button>
            <button class="btn btn-no" onclick="closeLogoutModal()">Tidak</button>
        </div>
    </div>
</div>
<script>
    function toggleSubmenu(event, submenuId) {
        event.preventDefault();
        const submenu = document.getElementById(submenuId);
        const parentLink = event.target.closest('.has-submenu');
        submenu.classList.toggle('active');
        parentLink.classList.toggle('active');
    }
    // Modal Logout
    function toggleLogoutModal(event) {
        event.preventDefault();
        document.getElementById('logoutModal').style.display = 'flex';
    }
    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }
    function logout() {
        window.location.href = 'logout.php'; // Ganti dengan file logout Anda
    }
    // Tutup modal jika klik di luar
    window.onclick = function(event) {
        const modal = document.getElementById('logoutModal');
        if (event.target == modal) {
            closeLogoutModal();
        }
    }
</script>