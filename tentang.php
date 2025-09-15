<?php
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - PT Tembakau Deli Medica</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- responsive viewport -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url("asset/img/p3.jpeg") no-repeat center center/cover;
            min-height: 100vh;
            margin: 0;
            color: #111;
            font-size: 16px;
        }

        nav {
            background: rgba(44, 62, 80, 0.95);
            padding: 15px;
            text-align: center;
            position: relative;
        }
        nav h3 {
            color: white;
            margin: 0 0 15px 0;
            font-size: 20px;
            font-weight: bold;
        }

        .logout-btn {
            position: absolute;
            right: 20px;
            top: 20px;
            padding: 8px 16px;
            background: #e74c3c;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .logout-btn:hover {
            background: #c0392b;
        }

        /* Tabs */
        .tabs {
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .tabs button {
            padding: 10px 15px;
            background: #b44c4c;
            border: none;
            cursor: pointer;
            color: white;
            border-radius: 5px;
            transition: background 0.3s;
            font-size: 15px;
            font-weight: 500;
        }
        .tabs button:hover {
            background: #943838;
        }
        .tabs button.active {
            background: #3498db;
            font-weight: bold;
        }

        /* Section Tentang */
        #tentang {
            margin: 0;
            width: 100%;
            min-height: calc(100vh - 90px);
            background: rgba(255, 255, 255, 0.7);
            border-radius: 0;
            padding: 30px 40px;
            box-shadow: none;
        }
        #tentang h2 {
            margin-bottom: 15px;
            color: #2c3e50;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        .tab-content {
            display: none;
            line-height: 1.8;
            color: #111;
            text-align: justify;
            font-size: 16px;
        }
        .tab-content.active {
            display: block;
        }
        .tab-content h3 {
            font-size: 18px;
            font-weight: bold;
            color: #222;
        }
        .tab-content p,
        .tab-content li {
            font-size: 16px;
            color: #111;
        }

        /* ============================= */
        /* RESPONSIVE DESIGN */
        /* ============================= */
        @media (max-width: 1024px) {
            nav h3 {
                font-size: 18px;
            }
            .tabs button {
                font-size: 14px;
                padding: 8px 12px;
            }
        }

        @media (max-width: 768px) {
            nav {
                padding: 10px;
            }
            nav h3 {
                font-size: 16px;
                margin-bottom: 10px;
            }
            .logout-btn {
                position: static;
                display: block;
                margin: 10px auto;
                font-size: 13px;
                padding: 6px 12px;
            }
            .tabs {
                flex-direction: column;
                gap: 6px;
            }
            #tentang {
                padding: 20px;
            }
            #tentang h2 {
                font-size: 20px;
            }
        }

        @media (max-width: 480px) {
            nav h3 {
                font-size: 14px;
            }
            .tabs button {
                font-size: 13px;
                width: 100%;
            }
            #tentang {
                padding: 15px;
            }
            #tentang h2 {
                font-size: 18px;
            }
            .tab-content h3 {
                font-size: 16px;
            }
            .tab-content p,
            .tab-content li {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav>
        <h3>APLIKASI PENGELOLAAN DATA KARYAWAN PT TEMBAKAU DELI MEDICA</h3>
        <div class="tabs">
            <button class="tablink active" onclick="openTab(event, 'latar')">Latar Belakang</button>
            <button class="tablink" onclick="openTab(event, 'visi')">Visi & Misi</button>
            <button class="tablink" onclick="openTab(event, 'tujuan')">Tujuan</button>
            <button class="tablink" onclick="openTab(event, 'kegiatan')">Kegiatan & Bidang Usaha</button>
            <button class="tablink" onclick="openTab(event, 'fasilitas')">Fasilitas Perusahaan</button>
        </div>
        <button class="logout-btn" onclick="window.location.href='dashboard.php'">Keluar</button>
    </nav>

    <!-- Tentang Perusahaan -->
    <section id="tentang">
        <h2>Tentang Perusahaan</h2>

        <div id="latar" class="tab-content active">
            <h1>Latar Belakang</h1>
            <p>Didirikan pada 30 Juni 2016 sesuai Akta Notaris M. Arif Fadilah No. 06, Merupakan badan hukum di bidang perumahsakitan dan merupakan Anak Perusahaan dari PT Perkebunan Nusantara II. Komposisi saham milik PT Perkebunan Nusantara II sebesar 98% dan milik Koperasi Karpeda PTPN II sebesar 2%.</p>
        </div>

        <div id="visi" class="tab-content">
            <h3>Visi & Misi</h3>
            <p><b>Visi:</b> Menjadi rumah sakit yang mandiri, unggul dan berdaya saing.</p>
            <p><b>Misi:</b></p>
            <ul>
                <li>Menyelenggarakan pelayanan kesehatan yang berbasis pada patient safety</li>
                <li>Melaksanakan manajemen rumah sakit secara professional</li>
                <li>Membangun kepercayaan pelanggan melalui sumber daya manusia yang professional, berkualitas dan berbudaya kerja prima</li>
                <li>Memberikan kontribusi yang optimal bagi perusahaan maupun masyarakat sekitar</li>
                <li>Menjaga dan memelihara kelestarian lingkungan serta menciptakan nilai tambah.</li>
            </ul>
        </div>

        <div id="tujuan" class="tab-content">
            <h3>Tujuan</h3>
            <p><b>Umum:</b> Menjadikan Rumah Sakit PT TDM sebagai RUJUKAN UTAMA pelayanan kesehatan tingkat lanjutan Kelas C bagi masyarakat disekitarnya.</p>
            <p><b>Khusus:</b></p>
            <ul>
                <li>Mengubah pandangan masyarakat bahwa RS bukan hanya tempat orang sakit tetapi juga tempat keluarga melakukan peningkatan dan pemeliharaan kesehatannya.</li>
                <li>Menjadikan rumah sakit pendidikan tenaga kesehatan yang profesional dan pendidikan kesehatan yang berorientasi kepada kepuasan pasien.</li>
            </ul>
        </div>

        <div id="kegiatan" class="tab-content">
            <h3>Kegiatan & Bidang Usaha</h3>
            <p>PT Tembakau Deli Medica bergerak dibidang pelayanan Kesehatan yang memiliki 3 unit rumah sakit :</p>
            <ul>
                <li>RSU Tanjung Selamat</li>
            </ul>
            <p>2 faskes:</p>
            <ul>
                <li>Klinik Pratama Garuda</li>
                <li>Klinik Pratama Batang Serangan</li>
                <li>Klinik Rambutan Deli Binjai</li>
            </ul>
        </div>

        <div id="fasilitas" class="tab-content">
            <h3>Fasilitas Perusahaan</h3>
            <ul>
                <li>Poliklinik perusahaan (layanan kesehatan dasar).</li>
                <li>Ruang pelatihan dan workshop.</li>
                <li>Kantin karyawan.</li>
                <li>Ruang ibadah.</li>
                <li>Area olahraga dan ruang istirahat karyawan.</li>
                <li>Sistem informasi kepegawaian berbasis teknologi.</li>
            </ul>
        </div>
    </section>

    <script>
        function openTab(evt, tabId) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("tablink");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabId).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>

</body>
</html>
