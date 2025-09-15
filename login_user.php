<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

if ($conn->connect_error) {
    die("conn gagal: " . $conn->connect_error);
}

$error = "";
$showError = false;
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($password === $user['password']) {
            session_regenerate_id(true);
            $_SESSION['user_session'] = true;
            $_SESSION['user_username'] = $username;
            $_SESSION['user_nik'] = $user['nik']; // Simpan NIK dari data user
            header("Location: absensi.php");
            exit();
        } else {
            $error = "Username atau Password salah!";
            $showError = true;
        }
    } else {
        $error = "Username atau Password salah!";
        $showError = true;
    }
    $query->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Karyawan - Aplikasi Karyawan RS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #d4af37, #c0c0c0);
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
        }
        .login-box {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: slideIn 1s ease-out;
            text-align: center;
        }
        .login-box h2 {
            margin: 0 0 15px;
            color: #d4af37;
            font-size: 1.8em;
            font-weight: 600;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
        }
        .user-box {
            position: relative;
            margin-bottom: 20px;
        }
        .user-box input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d4af37;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            background: rgba(255, 255, 255, 0.9);
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        .user-box input:focus {
            border-color: #c0c0c0;
        }
        .user-box label {
            position: absolute;
            left: 12px;
            top: 12px;
            background: #fff;
            padding: 0 5px;
            color: #888;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        .user-box input:focus + label,
        .user-box input:valid + label {
            top: -8px;
            font-size: 10px;
            color: #d4af37;
        }
        .toggle-eye {
            position: absolute;
            right: 12px;
            top: 12px;
            cursor: pointer;
            font-size: 14px;
            user-select: none;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #d4af37, #c0c0c0);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn-login:hover {
            transform: scale(1.05);
            background: linear-gradient(45deg, #c0c0c0, #d4af37);
        }
        .back-link {
            display: block;
            margin-top: 15px;
            color: #d4af37;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #c0c0c0;
            text-decoration: underline;
        }
        .footer {
            margin-top: 20px;
            color: #888;
            font-size: 10px;
            text-align: center;
        }
        .toast-error {
            margin-top: 10px;
            max-width: 90%;
            background: linear-gradient(45deg, #dc3545, #a71d2a);
            color: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease;
        }
        .toast-error.show {
            opacity: 1;
            visibility: visible;
            animation: fadeIn 0.3s ease-out forwards, fadeOut 0.3s 3s forwards;
        }
        .toast-error::before {
            content: '❌';
            margin-right: 0.5rem;
        }
        @keyframes slideIn {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeOut {
            to { opacity: 0; }
        }
        @media (max-width: 768px) {
            .login-box { padding: 20px; }
            .login-box h2 { font-size: 1.5em; }
            .user-box input { font-size: 12px; padding: 10px; }
            .btn-login { font-size: 12px; padding: 10px; }
            .toast-error { font-size: 10px; padding: 8px 12px; }
        }
        @media (max-width: 576px) {
            .login-box { padding: 15px; }
            .login-box h2 { font-size: 1.2em; }
            .user-box input { font-size: 10px; padding: 8px; }
            .btn-login { font-size: 10px; padding: 8px; }
            .toast-error { font-size: 9px; padding: 6px 10px; }
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login Sebagai Karyawan</h2>
        <div id="errorToast" class="toast-error" role="alert">
            Login gagal! Periksa Username atau Password Anda.
        </div>
        <form method="POST">
            <div class="user-box">
                <input type="text" name="username" required>
                <label>Username</label>
            </div>
            <div class="user-box">
                <input type="password" name="password" id="password" required>
                <label>Password</label>
                <span id="togglePassword" class="toggle-eye">👁</span>
            </div>
            <button type="submit" name="login" class="btn-login">Masuk</button>
        </form>
        <a href="welcome.php" class="back-link">Kembali ke Halaman Utama</a>
        <div class="footer">© 2025 Aplikasi Karyawan RS</div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('password');
            const errorToast = document.getElementById('errorToast');
            togglePassword.addEventListener('click', function () {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                this.textContent = type === 'password' ? '👁' : '🙈';
            });
            <?php if ($showError): ?>
                errorToast.classList.add('show');
                setTimeout(() => errorToast.classList.remove('show'), 3000);
            <?php endif; ?>
        });
    </script>
</body>
</html>
