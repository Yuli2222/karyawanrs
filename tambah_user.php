<?php
date_default_timezone_set('Asia/Jakarta');
session_start();

// Koneksi Database
require_once 'config.php';

/**
 * KERASIN MODE ERROR & SET CHARSET (hosting sering strict)
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception("Koneksi database tidak ditemukan. Pastikan config.php mendefinisikan \$conn (mysqli).");
    }
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    http_response_code(500);
    echo "<pre style='color:#b91c1c;background:#fee2e2;padding:10px;border-radius:6px'>Kesalahan koneksi: " . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
}

// FLASH MESSAGE via session (untuk PRG)
$flash_success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

// Variabel pesan + sticky value
$error = $success = "";
$old_nik = $old_username = $old_status = "";

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Proses simpan data user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Token tidak valid. Muat ulang halaman.";
    } else {
        // Ambil data dari form (sticky)
        $nik        = isset($_POST['nik']) ? trim($_POST['nik']) : "";
        $username   = isset($_POST['username']) ? trim($_POST['username']) : "";
        $password   = isset($_POST['password']) ? (string)$_POST['password'] : "";
        $status     = isset($_POST['status']) ? trim($_POST['status']) : "";
        $created_at = date("Y-m-d H:i:s");

        $old_nik = $nik;
        $old_username = $username;
        $old_status = $status;

        // ----------- VALIDASI SERVER-SIDE -----------
        if ($nik === "" || $username === "" || $password === "" || $status === "") {
            $error = "⚠️ Semua field wajib diisi!";
        }
        // NIK (boleh angka saja, hosting sering pakai format numerik)
        if (!$error) {
            if (!ctype_digit($nik) || strlen($nik) < 6 || strlen($nik) > 20) {
                $error = "⚠️ NIK harus angka 6–20 digit.";
            }
        }
        // Username (alphanum, titik, strip, underscore)
        if (!$error) {
            if (!preg_match('/^[A-Za-z0-9._-]{3,30}$/', $username)) {
                $error = "⚠️ Username hanya huruf/angka/._- (3–30 karakter).";
            }
        }
        // Password (minimal 6)
        if (!$error) {
            if (strlen($password) < 6) {
                $error = "⚠️ Password minimal 6 karakter.";
            }
        }
        // Status whitelist
        if (!$error) {
            $allowed = ['admin', 'user'];
            if (!in_array($status, $allowed, true)) {
                $error = "⚠️ Status tidak valid.";
            }
        }

        if (!$error) {
            // Hash password
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);

            try {
                // Cek duplikat username / nik (pakai backtick supaya aman)
                $cek = $conn->prepare("SELECT `id`, `username`, `nik` FROM `users` WHERE `username` = ? OR `nik` = ? LIMIT 1");
                $cek->bind_param("ss", $username, $nik);
                $cek->execute();
                $res = $cek->get_result();
                if ($row = $res->fetch_assoc()) {
                    if (strcasecmp($row['username'], $username) === 0) {
                        $error = "⚠️ Username sudah digunakan.";
                    } elseif ($row['nik'] === $nik) {
                        $error = "⚠️ NIK sudah terdaftar.";
                    } else {
                        $error = "⚠️ Data sudah ada.";
                    }
                }
                $cek->close();

                if (!$error) {
                    // Simpan user
                    $stmt = $conn->prepare("INSERT INTO `users` (`nik`, `username`, `password`, `status`, `created_at`) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $nik, $username, $hashedPass, $status, $created_at);
                    $stmt->execute();
                    $stmt->close();

                    // PRG: hindari submit ulang saat refresh
                    $_SESSION['flash_success'] = "✅ User berhasil ditambahkan!";
                    // Reset sticky
                    unset($old_nik, $old_username, $old_status);
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }
            } catch (Throwable $th) {
                // Tangkap error mysqli strict
                $error = "❌ Gagal menambahkan user: " . $th->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Akun Karyawan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- tetap Bootstrap sesuai bentukmu -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Form Tambah Akun Karyawan</h3>
        </div>
        <div class="card-body">

            <!-- Pesan flash sukses dari PRG -->
            <?php if (!empty($flash_success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
            <?php endif; ?>

            <!-- Pesan error / sukses runtime (tanpa ubah bentuk) -->
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Form Tambah User (bentuk tetap) -->
            <form method="post" id="form-tambah-user" autocomplete="off">
                <!-- CSRF (hidden, tidak mengubah tampilan) -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="mb-3">
                    <label for="nik" class="form-label">NIK</label>
                    <input type="text" class="form-control" name="nik" id="nik" required
                           value="<?= htmlspecialchars($old_nik) ?>">
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" id="username" required
                           value="<?= htmlspecialchars($old_username) ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password" required minlength="6">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" name="status" id="status" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="admin" <?= ($old_status === 'admin' ? 'selected' : '') ?>>Admin</option>
                        <option value="user"  <?= ($old_status === 'user'  ? 'selected' : '') ?>>User</option>
                    </select>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary" id="btn-submit">
                        Tambah Akun Karyawan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- Proteksi double submit (tanpa ubah bentuk tampilan) -->
<script>
(function() {
    const form = document.getElementById('form-tambah-user');
    const btn  = document.getElementById('btn-submit');
    if (form && btn) {
        form.addEventListener('submit', function() {
            btn.disabled = true;
            btn.innerText = 'Memproses...';
        });
    }
})();
</script>

</body>
</html>
