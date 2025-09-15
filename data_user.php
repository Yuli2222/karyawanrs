<?php
date_default_timezone_set('Asia/Jakarta');
session_start();

include 'config.php';


$error = $success = "";
$users = [];

// Handle form submission for Add or Edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nik = isset($_POST['nik']) ? trim($_POST['nik']) : null;
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'user';
    $unit_kerja = isset($_POST['unit_kerja']) ? trim($_POST['unit_kerja']) : '';

    if (empty($username)) {
        $error = "Username wajib diisi.";
    } else {
        if ($id > 0) {
            // Update existing user
            if ($password === '') {
                // Update without changing password
                $stmt = $conn->prepare("UPDATE users SET nik=?, username=?, status=?, unit_kerja=? WHERE id_user=?");
                $stmt->bind_param("ssssi", $nik, $username, $status, $unit_kerja, $id);
            } else {
                // Update with password
                $stmt = $conn->prepare("UPDATE users SET nik=?, username=?, password=?, status=?, unit_kerja=? WHERE id_user=?");
                $stmt->bind_param("sssssi", $nik, $username, $password, $status, $unit_kerja, $id);
            }
            if ($stmt->execute()) {
                $success = "Data user telah berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui data user: " . $conn->error;
            }
            $stmt->close();
        } else {
            // Insert new user
            if (empty($password)) {
                $error = "Password wajib diisi untuk user baru.";
            } else {
                $stmt = $conn->prepare("INSERT INTO users (nik, username, password, status, unit_kerja, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sssss", $nik, $username, $password, $status, $unit_kerja);
                if ($stmt->execute()) {
                    $success = "User baru telah berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambahkan user baru: " . $conn->error;
                }
                $stmt->close();
            }
        }
    }
}

// Handle delete user request
if (isset($_GET['delete']) && intval($_GET['delete']) > 0) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        $success = "User telah berhasil dihapus!";
    } else {
        $error = "Gagal menghapus user: " . $conn->error;
    }
    $stmt->close();
}

// Fetch users data
$query = "SELECT id_user, nik, username, password, status, unit_kerja, created_at FROM users ORDER BY id_user DESC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
$conn->close();

// Ambil pesan dari query string jika ada (opsional)
$updateSuccess = isset($_GET['success']) && !empty($_GET['success']) ? urldecode($_GET['success']) : '';
$deleteSuccess = isset($_GET['deleteSuccess']) && !empty($_GET['deleteSuccess']) ? urldecode($_GET['deleteSuccess']) : '';
$error = isset($_GET['error']) && !empty($_GET['error']) ? urldecode($_GET['error']) : $error;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Akun Absensi Karyawan - Sistem Karyawan PT Tembakau Deli Medica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
       
    :root {
        --primary-color: #1e3c72;
        --secondary-color: #2a5298;
        --green-color: #28a745;
        --red-soft: #d9534f;
    }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #e6ecef, #d3e0ea);
        color: #333;
        margin: 0;
        padding: 0;
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
        position: relative;
    }

    /* =========================
       RESPONSIVE SETTINGS
    ========================== */
    @media (max-width: 992px) { /* Tablet */
        .main-content {
            margin-left: 200px;
            width: calc(100% - 200px);
            padding: 20px;
        }
        .header-content { padding: 15px 20px; }
        .table { font-size: 13px; }
    }

    @media (max-width: 768px) { /* HP besar */
        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 15px;
        }
        .header-content { padding: 15px; text-align: center; }
        .top-controls {
            flex-direction: column;
            align-items: stretch;
        }
        .search-box { width: 100%; }
        .btn-tambah-user { width: 100%; text-align: center; }
        .table-responsive { overflow-x: auto; }
        .table th, .table td { font-size: 12px; padding: 6px; }
    }

    @media (max-width: 480px) { /* HP kecil */
        .header-content h4 { font-size: 16px; }
        .header-content span { font-size: 12px; display:block; margin-top:5px; }
        .btn-tambah-user { font-size: 14px; padding: 8px 12px; }
        .modal-dialog { max-width: 95%; margin: auto; }
        .table th, .table td { font-size: 11px; padding: 5px; }
    }
</style>

</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="header-content d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-users me-2"></i>Akun Absensi Karyawan</h4>
            <span class="me-3"><i class="fas fa-calendar me-1"></i> <?php echo date('d F Y'); ?></span>
        </div>
        
        <div class="card">
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="top-controls">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Cari NIK atau Username..." class="form-control" />
                    </div>
                    
                    <div class="filter-controls d-flex gap-2">
                        <!-- Dropdown Filter Unit Kerja -->
                        <select id="filterUnitKerja" class="form-control">
                            <option value="">Semua Unit Kerja</option>
                            <option value="Kantor Direksi PT.TDM">Kantor Direksi PT.TDM</option>
                            <option value="Rumah Sakit Tanjung Selamat (RSTS)">Rumah Sakit Tanjung Selamat (RSTS)</option>
                            <option value="Klinik Pratama Garuda">Klinik Pratama Garuda</option>
                            <option value="Klinik Pratama Batang Serangan (BTS)">Klinik Pratama Batang Serangan (BTS)</option>
                            <option value="Klinik Rambutan Deli Binjai (RDB)">Klinik Rambutan Deli Binjai (RDB)</option>
                            <option value="Karyawan Pimpinan Kantor Direksi PT. TDM">Karyawan Pimpinan Kantor Direksi PT. TDM</option>
                            <option value="Karyawan Pelaksana Kantor Direksi PT. TDM">Karyawan Pelaksana Kantor Direksi PT. TDM</option>
                        </select>
                        
                        <!-- Button Tambah User -->
                        <button type="button" class="btn-tambah-user" id="btnAddUser">
                            <i class="fas fa-user-plus me-1"></i>Tambah Akun Karyawan
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIK</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Status</th>
                                <th>Unit Kerja</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="userTable">
                            <?php $no = 1; foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($user['nik']); ?></td>
                                    <td><?= htmlspecialchars($user['username']); ?></td>
                                    <td><?= htmlspecialchars($user['password']); ?></td>
                                    <td><?= htmlspecialchars($user['status']); ?></td>
                                    <td><?= htmlspecialchars($user['unit_kerja']); ?></td>
                                    <td><?= date('d F Y H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-action btn-edit" 
                                            data-id="<?= $user['id_user']; ?>"
                                            data-nik="<?= htmlspecialchars($user['nik']); ?>"
                                            data-username="<?= htmlspecialchars($user['username']); ?>"
                                            data-status="<?= htmlspecialchars($user['status']); ?>"
                                            data-unit_kerja="<?= htmlspecialchars($user['unit_kerja']); ?>"
                                            ><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-action btn-delete" data-id="<?= $user['id_user']; ?>"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Modal Add/Edit User -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="userForm" method="post" action="">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Tambah/Edit Akun Karyawan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="id_user" name="id" value="">
                            <div class="mb-3">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="unit_kerja" class="form-label">Unit Kerja</label>
                                <select class="form-control" id="unit_kerja" name="unit_kerja">
                                    <option value="Kantor Direksi PT.TDM">Kantor Direksi PT.TDM</option>
                                    <option value="Rumah Sakit Tanjung Selamat (RSTS)">Rumah Sakit Tanjung Selamat (RSTS)</option>
                                    <option value="Klinik Pratama Garuda">Klinik Pratama Garuda</option>
                                    <option value="Klinik Pratama Batang Serangan (BTS)">Klinik Pratama Batang Serangan (BTS)</option>
                                    <option value="Klinik Rambutan Deli Binjai (RDB)">Klinik Rambutan Deli Binjai (RDB)</option>
                                    <option value="Karyawan Pimpinan Kantor Direksi PT. TDM">Karyawan Pimpinan Kantor Direksi PT. TDM</option>
                                    <option value="Karyawan Pelaksana Kantor Direksi PT. TDM">Karyawan Pelaksana Kantor Direksi PT. TDM</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Modal Konfirmasi Hapus -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Hapus Akun Karyawan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Apakah Anda Yakin Ingin Menghapus Akun Ini?
                    </div>
                    <div class="modal-footer btn-container">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteButton">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Notifikasi Sukses -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="successModalLabel">Sukses</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Operasi berhasil dilakukan!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Notifikasi Hapus Sukses -->
        <div class="modal fade" id="deleteSuccessModal" tabindex="-1" aria-labelledby="deleteSuccessModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteSuccessModalLabel">Sukses</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Akun telah berhasil dihapus!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Loading Overlay -->
        <div class="loading-overlay">
            <div class="spinner"></div>
        </div>
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Toggle password visibility
            $(document).ready(function() {
                $('#togglePassword').click(function() {
                    const passwordInput = $('#password');
                    const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
                    passwordInput.attr('type', type);
                    $(this).find('i').toggleClass('fa-eye fa-eye-slash');
                });

                // Handle Add User button click
                $('#btnAddUser').click(function() {
                    $('#id_user').val(''); // Clear ID for new user
                    $('#nik').val('');
                    $('#username').val('');
                    $('#password').val('');
                    $('#status').val('user');
                    $('#unit_kerja').val('');
                    $('#editModalLabel').text('Tambah User');
                    var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                });

                // Handle Edit button click
                $(document).on('click', '.btn-edit', function() {
                    const id = $(this).data('id');
                    const nik = $(this).data('nik');
                    const username = $(this).data('username');
                    const status = $(this).data('status');
                    const unit_kerja = $(this).data('unit_kerja');

                    $('#id_user').val(id);
                    $('#nik').val(nik);
                    $('#username').val(username);
                    $('#status').val(status);
                    $('#unit_kerja').val(unit_kerja);
                    $('#editModalLabel').text('Edit User');
                    var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                });

                // Handle Delete button click
                $(document).on('click', '.btn-delete', function() {
                    const id = $(this).data('id');
                    $('#confirmDeleteButton').data('id', id);
                    var confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                    confirmModal.show();
                });

                // Handle Confirm Delete button click
                $('#confirmDeleteButton').click(function() {
                    const id = $(this).data('id');
                    // Show loading overlay briefly
                    $('.loading-overlay').addClass('active');
                    // redirect to delete endpoint
                    window.location.href = 'data_user.php?delete=' + id;
                });

                // Handle search input
                $('#searchInput').on('input', function() {
                    const searchText = $(this).val().toLowerCase();
                    $('#userTable tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(searchText) > -1);
                    });
                });

                // Handle filter unit kerja
                $('#filterUnitKerja').change(function() {
                    const filterValue = $(this).val().toLowerCase();
                    $('#userTable tr').filter(function() {
                        $(this).toggle($(this).find('td:eq(5)').text().toLowerCase().indexOf(filterValue) > -1);
                    });
                });

                // Show success modal if success message exists
                <?php if ($success): ?>
                    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                <?php endif; ?>

                // Show delete success modal if delete success message exists
                <?php if ($deleteSuccess): ?>
                    var deleteSuccessModal = new bootstrap.Modal(document.getElementById('deleteSuccessModal'));
                    deleteSuccessModal.show();
                <?php endif; ?>
            });
        </script>
    </div>
</body>
</html>