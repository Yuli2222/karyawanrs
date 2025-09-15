<?php
include 'config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi Pengelolaan Data Karyawan PT Tembakau Deli Medica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- penting untuk responsive -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        * {margin:0; padding:0; box-sizing:border-box;}
        body {
            font-family: 'Poppins', sans-serif;
            background: url('asset/img/p3.jpeg') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        nav {
            width: 100%;
            background: #2c3e50;
            padding: 15px 20px;
            color: #fff;
            font-weight: 600;
            display: flex;
            justify-content: center; 
            align-items: center;
            position: relative;
            text-align: center;
        }
        nav h2 { 
            font-size: 18px; 
            margin: 0; 
        }

        .show-login-btn {
            position: absolute;
            right: 20px;
            padding: 10px 18px;
            background: #2c3e50;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
        }
        .show-login-btn:hover {
            background: #dbdfe4;
            color: #000;
        }

        /* Hero Section */
        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        /* Kotak Login */
        .login-box {
            width: 600px;
            max-width: 95%;
            background: linear-gradient(135deg, #2c3e50, #2c3e50);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            color: #fff;
            display: none;
        }
        .login-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .login-header img {
            width: 40px;
            height: 40px;
        }
        .login-header h3 {
            margin: 0;
            font-size: 20px;
            color: #fff;
        }
        .login-box p {
            font-size: 14px;
            margin-bottom: 20px;
            color: #f0f0f0;
        }
        .user-box { margin-bottom: 15px; }

        /* Tombol link styled */
        .btn-link {
            display: inline-block;
            width: 100%;
            text-align: center;
            padding: 12px;
            background: #db3482ff;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            transition: 0.3s;
        }
        .btn-link:hover {
            background: #b71c1c;
        }

        /* Footer */
        .footer {
            padding: 15px;
            text-align: center;
            background: rgba(0,0,0,0.7);
            color: #fff;
            font-size: 13px;
        }

        /* ================== RESPONSIVE DESIGN ================== */
        @media (max-width: 992px) { /* tablet */
            nav h2 { font-size: 16px; padding: 0 40px; }
            .show-login-btn { font-size: 13px; padding: 8px 14px; }
            .login-box { width: 80%; }
        }
        @media (max-width: 768px) { /* hp landscape */
            nav h2 { font-size: 15px; }
            .login-header h3 { font-size: 18px; }
            .btn-link { font-size: 14px; padding: 10px; }
        }
        @media (max-width: 480px) { /* hp kecil */
            nav { flex-direction: column; padding: 10px; }
            nav h2 { font-size: 14px; text-align: center; margin-bottom: 8px; }
            .show-login-btn { position: relative; right: 0; margin-top: 5px; }
            .login-header img { width: 35px; height: 35px; }
            .login-header h3 { font-size: 16px; }
            .btn-link { font-size: 13px; padding: 8px; }
        }
    </style>
</head>
<body>
   <!-- Navbar -->
   <nav>
        <h2>APLIKASI PENGELOLAAN DATA KARYAWAN PT TEMBAKAU DELI MEDICA</h2>
        <button class="show-login-btn" id="showBtn" onclick="showLogin()">LOGIN</button>
   </nav>

    <!-- Hero Section -->
    <div class="hero">
        <div class="login-box" id="loginBox">
            <div class="login-header">
                <img src="asset/img/LOGO.jpeg" alt="Logo PT Tembakau Deli Medica">
                <h3>Login</h3>
            </div>
            <p>Silakan Login Untuk Mengakses Aplikasi Pengelolaan Data Karyawan PT.Tembakau Deli Medica</p>
            <form method="POST">
                <div class="user-box">
                    <a href="login.php" class="btn-link">Login sebagai Admin</a>
                </div>
                <div class="user-box">
                    <a href="login_user.php" class="btn-link">Login sebagai Karyawan</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showLogin() {
            document.getElementById('showBtn').style.display = 'none';
            document.getElementById('loginBox').style.display = 'block';
        }
    </script>
</body>
</html>