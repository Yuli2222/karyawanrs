<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';
if ($conn->connect_error) {
    die("conn gagal: " . $conn->connect_error);
}

$error = "";
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    if ($query === false) {
        die("Error preparing query: " . $conn->error);
    }
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($password === $user['password']) { // Ganti ke password_verify() kalau udah di-hash
            // Simpan session admin dengan tanda unik
            session_regenerate_id(true); // Reset session ID biar aman
            $_SESSION['admin_session'] = true; // Tanda admin
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['role'] = 'admin';
            $_SESSION['status'] = $user['status'] ?? 'Aktif';
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Username atau Password salah!";
        }
    } else {
        $error = "Username atau Password salah!";
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
    <title>Login Admin - Aplikasi Karyawan RS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1s ease-in;
            text-align: center;
            position: relative;
        }
        .login-container h2 {
            margin: 0 0 15px;
            color: #1e3c72;
            font-size: 1.8em;
            font-weight: 600;
            text-shadow: 1px 1px 3px rgba(255, 215, 0, 0.3);
        }
        .user-box {
            position: relative;
            margin-bottom: 20px;
        }
        .user-box input {
            width: 100%;
            padding: 12px 40px 12px 12px;
            border: 1px solid #1e3c72;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            background: rgba(255, 255, 255, 0.9);
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        .user-box input:focus {
            border-color: #ffd700;
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
            color: #ffd700;
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #1e3c72;
            transition: all 0.3s ease;
            border-radius: 50%;
            padding: 2px;
        }
        .password-toggle:hover {
            color: #ffd700;
            transform: translateY(-50%) scale(1.2);
            box-shadow: 0 0 8px rgba(255, 215, 0, 0.5);
        }
        .password-toggle:active {
            transform: translateY(-50%) scale(0.95);
            box-shadow: none;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #1e3c72, #ffd700);
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
            background: linear-gradient(45deg, #ffd700, #1e3c72);
        }
        .back-link {
            display: block;
            margin-top: 15px;
            color: #ffd700;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #1e3c72;
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
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeOut {
            to { opacity: 0; }
        }
        @media (max-width: 768px) {
            .login-container { padding: 20px; }
            .login-container h2 { font-size: 1.5em; }
            .user-box input { font-size: 12px; padding: 10px 35px 10px 10px; }
            .password-toggle { font-size: 16px; right: 10px; }
            .btn-login { font-size: 12px; padding: 10px; }
            .back-link { font-size: 11px; }
            .footer { font-size: 9px; }
            .toast-error { font-size: 10px; padding: 8px 12px; }
        }
        @media (max-width: 480px) {
            .login-container { padding: 15px; }
            .login-container h2 { font-size: 1.2em; }
            .user-box input { font-size: 10px; padding: 8px 30px 8px 8px; }
            .password-toggle { font-size: 14px; right: 8px; }
            .btn-login { font-size: 10px; padding: 8px; }
            .back-link { font-size: 10px; }
            .footer { font-size: 8px; }
            .toast-error { font-size: 9px; padding: 6px 10px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login sebagai Admin</h2>
        <?php if (isset($error) && !empty($error)): ?>
            <div id="errorToast" class="toast-error" role="alert"><?php echo $error; ?></div>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const errorToast = document.getElementById("errorToast");
                    errorToast.classList.add("show");
                    setTimeout(() => errorToast.classList.remove("show"), 3000);
                });
            </script>
        <?php endif; ?>
        <form method="POST">
            <div class="user-box">
                <input type="text" name="username" required>
                <label>Username</label>
            </div>
            <div class="user-box">
                <input type="password" name="password" id="password" required>
                <label>Password</label>
                <span class="password-toggle" onclick="togglePassword()">👁</span>
            </div>
            <button type="submit" name="login" class="btn-login">Masuk</button>
        </form>
        <a href="welcome.php" class="back-link">Kembali ke Halaman Utama</a>
        <div class="footer">© 2025 Aplikasi Pengelolaan Data Karyawan PT.Tembakau Deli Medica </div>
    </div>
    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const toggleIcon = document.querySelector(".password-toggle");
            if (password.type === "password") {
                password.type = "text";
                toggleIcon.textContent = "👁‍🗨";
            } else {
                password.type = "password";
                toggleIcon.textContent = "👁";
            }
        }
    </script>
</body>
</html>
