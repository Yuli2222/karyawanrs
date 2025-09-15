<?php
session_start();
if (!isset($_SESSION['user_session'])) {
    header("Location: login_user.php");
    exit();
}

include 'config.php';
date_default_timezone_set('Asia/Jakarta');
// Endpoint AJAX untuk load data karyawan berdasarkan unit kerja dan NIK session
if (isset($_GET['action']) && $_GET['action'] === 'get_karyawan') {
    $unit_kerja = $_GET['unit_kerja'] ?? '';
    $nik = $_SESSION['user_nik'] ?? '';
    $bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m'); // Ambil bulan dari parameter atau gunakan bulan saat ini
    $table_map = [
        'Kantor Direksi PT. TDM' => 'karyawan_kantor_pttdm',
        'Rumah Sakit Tanjung Selamat' => 'karyawan_rsts',
        'Klinik Pratama Garuda' => 'karyawan_klinik_grd',
        'Klinik Pratama Batang Serangan' => 'karyawan_klinik_bts',
        'Klinik Rambutan Deli Binjai' => 'karyawan_klinik_rdb',
        'Karyawan Pelaksana Kantor Direksi PT. TDM' => 'karyawan_karpel_pttdm',
        'Karyawan Pimpinan Kantor Direksi PT. TDM' => 'karyawan_karpim_pttdm',
    ];
    $table = $table_map[$unit_kerja] ?? '';
    if ($table && $nik) {
        // Ambil data karyawan berdasarkan bulan yang dipilih
        $stmt = $conn->prepare("SELECT nama, jabatan FROM $table WHERE nik = ? AND MONTH(tanggal_input) = ?");
        $stmt->bind_param("ss", $nik, $bulan);
        $stmt->execute();
        $res = $stmt->get_result();

        $nama = null;
        $jabatan_arr = [];
        while ($row = $res->fetch_assoc()) {
            if ($nama === null) {
                $nama = $row['nama'];
            }
            // Tambahkan jabatan dari setiap baris, trim & unikkan
            $jabatan_arr[] = trim($row['jabatan']);
        }
        // Hilangkan jabatan kosong dan duplikat
        $jabatan_arr = array_filter(array_unique($jabatan_arr));

        if ($nama !== null) {
            $data = [
                'nama' => $nama,
                'jabatan' => array_values($jabatan_arr),
            ];
            echo json_encode($data);
        } else {
            echo json_encode([]);
        }
    } else {
        echo json_encode([]);
    }
    exit;
}

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nik = $_SESSION['user_nik']; // Ambil NIK dari session
    $nama = trim($_POST['nama'] ?? '');
    $jabatan_csv = trim($_POST['jabatan'] ?? ''); // Bisa multiple jabatan CSV
    $status = trim($_POST['jenis_absen'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $unit_kerja = trim($_POST['unit_kerja'] ?? '');

    if ($nama === '' || $jabatan_csv === '' || $status === '' || $keterangan === '' || $unit_kerja === '') {
        $msg = "Semua field wajib diisi.";
    } elseif (empty($_POST['foto_data'])) {
        $msg = "Silakan ambil foto terlebih dahulu (wajib).";
    } else {
        $tanggal = date('Y-m-d');
        $jam = date('H:i:s');
        $folder = __DIR__ . "/uploads/";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        $foto_data = $_POST['foto_data'];
        $foto_data = preg_replace('#^data:image/\w+;base64,#i', '', $foto_data);
        $foto_data = str_replace(' ', '+', $foto_data);
        $foto_bin = base64_decode($foto_data);
        if ($foto_bin === false) {
            $msg = "Format foto tidak valid.";
        } else {
            $foto_nama = "uploads/face_" . time() . "_" . bin2hex(random_bytes(4)) . ".png";
            $file_path = __DIR__ . "/" . $foto_nama;
            if (file_put_contents($file_path, $foto_bin) === false) {
                $msg = "Gagal menyimpan file foto.";
            } else {
                $unit_kerja_lower = strtolower($unit_kerja);
                $table = '';
                if ($unit_kerja_lower === strtolower('Kantor Direksi PT. TDM')) {
                    $table = 'absensi_pttdm';
                } elseif ($unit_kerja_lower === strtolower('Rumah Sakit Tanjung Selamat')) {
                    $table = 'absensi_rsts';
                } elseif ($unit_kerja_lower === strtolower('Klinik Pratama Garuda')) {
                    $table = 'absensi_garuda';
                } elseif ($unit_kerja_lower === strtolower('Klinik Pratama Batang Serangan')) {
                    $table = 'absensi_bts';
                } elseif ($unit_kerja_lower === strtolower('Klinik Rambutan Deli Binjai')) {
                    $table = 'absensi_rdb';
                } elseif ($unit_kerja_lower === strtolower('Karyawan Pimpinan Kantor Direksi PT. TDM')) {
                    $table = 'absensi_karpim_pttdm';
                } elseif ($unit_kerja_lower === strtolower('Karyawan Pelaksana Kantor Direksi PT. TDM')) {
                    $table = 'absensi_karpel_pttdm';
                } else {
                    $msg = "Unit kerja tidak didukung saat ini.";
                }

                if ($table !== '') {
                    $jabatan_arr = array_filter(array_map('trim', explode(',', $jabatan_csv)));
                    $success = true;
                    foreach ($jabatan_arr as $jabatan) {
                        $stmt_check = $conn->prepare("SELECT id FROM $table WHERE nik = ? AND tanggal = ? AND jenis_absen = ? AND jabatan = ?");
                        if ($stmt_check === false) {
                            $msg = "Persiapan query gagal: " . $conn->error;
                            $success = false;
                            break;
                        }
                        $stmt_check->bind_param("ssss", $nik, $tanggal, $status, $jabatan);
                        $stmt_check->execute();
                        $result_check = $stmt_check->get_result();
                        if ($result_check->num_rows > 0) {
                            $sql = "UPDATE $table SET nama = ?, foto = ?, jam = ?, keterangan = ?, unit_kerja = ? WHERE nik = ? AND tanggal = ? AND jenis_absen = ? AND jabatan = ?";
                            $stmt = $conn->prepare($sql);
                            if ($stmt === false) {
                                $msg = "Persiapan query gagal: " . $conn->error;
                                $success = false;
                                break;
                            }
                            $stmt->bind_param("sssssssss", $nama, $foto_nama, $jam, $keterangan, $unit_kerja, $nik, $tanggal, $status, $jabatan);
                            if (!$stmt->execute()) {
                                $msg = "Gagal menyimpan ke database: " . $stmt->error;
                                @unlink($file_path);
                                $success = false;
                                break;
                            }
                            $stmt->close();
                        } else {
                            $sql = "INSERT INTO $table (nik, nama, jabatan, foto, tanggal, jam, jenis_absen, keterangan, unit_kerja)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            if ($stmt === false) {
                                $msg = "Persiapan query gagal: " . $conn->error;
                                $success = false;
                                break;
                            }
                            $stmt->bind_param("sssssssss", $nik, $nama, $jabatan, $foto_nama, $tanggal, $jam, $status, $keterangan, $unit_kerja);
                            if (!$stmt->execute()) {
                                $msg = "Gagal menyimpan ke database: " . $stmt->error;
                                @unlink($file_path);
                                $success = false;
                                break;
                            }
                            $stmt->close();
                        }
                        $stmt_check->close();
                    }
                    if ($success) {
                        $msg = "success";
                    }
                } else {
                    @unlink($file_path);
                }
            }
        }
    }
}
$conn->close();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Absensi Karyawan - PT TDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #1e3a8a, #d4af37);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: #1e293b;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            overflow-x: hidden;
            position: relative;
        }
        .card {
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            animation: slideUp 0.8s ease-out;
            max-width: 600px;
            width: 100%;
        }
        .card h4 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #d4af37;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: 500;
            color: #1e3a8a;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border-color: #d4af37;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1e3a8a;
            box-shadow: 0 0 8px rgba(30, 58, 138, 0.3);
        }
        .camera-box {
            background: #fff;
            color: #000;
            border-radius: 10px;
            padding: 1rem;
        }
        .btn {
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-primary {
            background: linear-gradient(45deg, #d4af37, #1e3a8a);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #1e3a8a, #d4af37);
            transform: scale(1.05);
        }
        .btn-light, .btn-success {
            color: #1e3a8a;
            background: rgba(255, 255, 255, 0.9);
        }
        .btn-success {
            background: #10b981;
            color: #fff;
        }
        video, canvas, img.preview {
            width: 100%;
            border-radius: 8px;
            display: block;
            border: 2px solid #d4af37;
        }
        #preview img.preview {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .jabatan-group {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 5px;
        }
        .jabatan-item {
            display: flex;
            align-items: center;
            padding: 5px 10px;
            border: 1px solid #d4af37;
            border-radius: 6px;
            background: #fff;
            transition: all 0.3s ease;
        }
        .jabatan-item input[type="checkbox"] {
            margin-right: 8px;
            cursor: pointer;
        }
        .jabatan-item label {
            margin: 0;
            font-size: 14px;
            color: #1e3a8a;
            cursor: pointer;
        }
        .jabatan-item:hover {
            background: #f8f9fa;
            border-color: #1e3a8a;
        }
        .jabatan-item input[type="checkbox"]:checked + label {
            font-weight: 600;
            color: #1e3a8a;
        }
        @media (max-width: 576px) {
            .jabatan-group {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-10">
                <div class="card shadow-sm p-4">
                    <h4 class="text-center mb-3">📋 Absensi Karyawan</h4>
                    <?php if ($msg === "success"): ?>
                        <div id="absenSuccessModal" class="active">
                            <div class="modal-content" style="text-align:center; padding:2rem; border-radius:12px; background:#fff; box-shadow:0 8px 32px rgba(0,0,0,0.15);">
                                <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">✅</div>
                                <div style="font-size: 1.25rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Absensi Berhasil!</div>
                                <div style="font-size: 0.95rem; color: #475569;">Absensi Anda untuk hari ini telah tersimpan.</div>
                                <button type="button" onclick="closeAbsenModal()" style="margin-top: 1.25rem; padding: 0.5rem 1.25rem; background: #1e293a; color: #fff; border: none; border-radius: 6px; font-size: 0.9rem; cursor: pointer;">Oke</button>
                            </div>
                        </div>
                        <script>
                            function closeAbsenModal() {
                                document.getElementById('absenSuccessModal').style.display = 'none';
                            }
                        </script>
                    <?php elseif ($msg !== "" && $msg !== "success"): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
                    <?php endif; ?>
                    <form id="absensiForm" method="post" novalidate>
                        <div class="mb-3">
                            <label class="form-label">NIK</label>
                            <input name="nik" type="text" required class="form-control" value="<?= htmlspecialchars($_SESSION['user_nik'] ?? '') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input name="nama" id="nama" type="text" required class="form-control" placeholder="Nama lengkap" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unit Kerja</label>
                            <select name="unit_kerja" id="unit_kerja" class="form-select" required>
                                <option value="">-- Pilih Unit Kerja --</option>
                                <option value="Kantor Direksi PT. TDM">Kantor Direksi PT. TDM</option>
                                <option value="Rumah Sakit Tanjung Selamat">Rumah Sakit Tanjung Selamat</option>
                                <option value="Klinik Pratama Garuda">Klinik Pratama Garuda</option>
                                <option value="Klinik Pratama Batang Serangan">Klinik Pratama Batang Serangan</option>
                                <option value="Klinik Rambutan Deli Binjai">Klinik Rambutan Deli Binjai</option>
                                <option value="Karyawan Pimpinan Kantor Direksi PT. TDM">Karyawan Pimpinan Kantor Direksi PT. TDM</option>
                                <option value="Karyawan Pelaksana Kantor Direksi PT. TDM">Karyawan Pelaksana Kantor Direksi PT. TDM</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <div id="jabatanGroup" class="jabatan-group">
                                <!-- Opsi jabatan akan diisi via JavaScript AJAX -->
                            </div>
                            <input type="hidden" name="jabatan" id="jabatanHidden">
                        </div>
                        <div class="row gx-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Tanggal (lokal)</label>
                                <input id="tanggal" type="text" class="form-control" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Jam (lokal)</label>
                                <input id="jam" type="text" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row gx-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Jenis Absensi</label>
                                <select name="jenis_absen" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Masuk">Masuk</option>
                                    <option value="Pulang">Pulang</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Keterangan</label>
                                <select name="keterangan" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Masuk">Masuk</option>
                                    <option value="Sakit">Sakit</option>
                                    <option value="Izin">Izin</option>
                                    <option value="Libur">Libur</option>
                                    <option value="Cuti">Cuti</option>
                                    <option value="p-3">p-3</option>
                                    <option value="Dinas Luar">Dinas Luar</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Foto (wajib)</label>
                            <div class="camera-box">
                                <div class="d-grid gap-2 mb-2">
                                    <button type="button" class="btn btn-light btn-sm" id="btnOpenCam">📷 Buka Kamera</button>
                                    <button type="button" class="btn btn-success btn-sm d-none" id="btnCapture">📸 Ambil Foto</button>
                                </div>
                                <div id="videoWrap" style="display:none;">
                                    <video id="video" autoplay playsinline style="width:100%; border-radius:8px;"></video>
                                </div>
                                <canvas id="canvas" class="d-none"></canvas>
                                <div id="preview" class="mt-2"></div>
                                <input type="hidden" name="foto_data" id="foto_data">
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">✅ Simpan Absensi</button>
                        </div>
                    </form>
                    <div class="mt-3 small text-muted">Catatan: waktu yang disimpan di database mengikuti waktu server.</div>
                </div>
            </div>
        </div>
        <div id="fotoErrorModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); backdrop-filter:blur(5px); z-index:9999; align-items:center; justify-content:center;">
            <div class="modal-content" style="width:90%; max-width:400px; background:linear-gradient(135deg, #ff4d4d, #e60000); border-radius:15px; box-shadow:0 10px 40px rgba(230,0,0,0.5); padding:2rem 1.5rem; text-align:center; color:#fff; font-family:'Poppins', sans-serif; animation:pulseIn 0.4s ease;">
                <h5>⚠️ Peringatan!</h5>
                <p>Silakan ambil foto terlebih dahulu (wajib).</p>
                <button onclick="closeFotoErrorModal()" style="background:#fff; color:#e60000; border:none; padding:0.75rem 1.5rem; border-radius:8px; font-weight:600; cursor:pointer; transition:transform 0.3s ease, box-shadow 0.3s ease;">Oke</button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            function updateDateTime() {
                const now = new Date();
                const y = now.getFullYear();
                const m = String(now.getMonth() + 1).padStart(2, '0');
                const d = String(now.getDate()).padStart(2, '0');
                document.getElementById('tanggal').value = `${y}-${m}-${d}`;
                document.getElementById('jam').value = now.toLocaleTimeString();
            }
            setInterval(updateDateTime, 1000);
            updateDateTime();

            let streamRef = null;
            const btnOpen = document.getElementById('btnOpenCam');
            const btnCapture = document.getElementById('btnCapture');
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const preview = document.getElementById('preview');
            const fotoInput = document.getElementById('foto_data');
            const videoWrap = document.getElementById('videoWrap');
            if (btnOpen) {
                btnOpen.addEventListener('click', async () => {
                    try {
                        streamRef = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                        video.srcObject = streamRef;
                        videoWrap.style.display = 'block';
                        btnCapture.classList.remove('d-none');
                        btnOpen.classList.add('d-none');
                    } catch (err) {
                        alert('Gagal membuka kamera: ' + err.message + '. Pastikan izin kamera diizinkan di browser.');
                    }
                });
            }
            if (btnCapture) {
                btnCapture.addEventListener('click', () => {
                    if (!streamRef) return alert('Kamera belum aktif.');
                    canvas.width = video.videoWidth || 640;
                    canvas.height = video.videoHeight || 480;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const dataUrl = canvas.toDataURL('image/png');
                    fotoInput.value = dataUrl;
                    preview.innerHTML = '<img class="preview img-fluid" src="' + dataUrl + '" alt="preview">';
                    streamRef.getTracks().forEach(t => t.stop());
                    streamRef = null;
                    videoWrap.style.display = 'none';
                    btnCapture.classList.add('d-none');
                    btnOpen.classList.remove('d-none');
                });
            }

            const unitKerjaSelect = document.getElementById('unit_kerja');
            const jabatanGroup = document.getElementById('jabatanGroup');
            const namaInput = document.getElementById('nama');
            const jabatanHidden = document.getElementById('jabatanHidden');

            unitKerjaSelect.addEventListener('change', function() {
                const unitKerja = this.value;
                jabatanGroup.innerHTML = '';
                namaInput.value = '';

                if (!unitKerja) return;

                // Fetch data karyawan dari server via AJAX
                fetch(`?action=get_karyawan&unit_kerja=${encodeURIComponent(unitKerja)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.nama) {
                            namaInput.value = data.nama;
                            const jabatanArr = data.jabatan || [];
                            if (jabatanArr.length > 0) {
                                jabatanArr.forEach(jabatan => {
                                    const div = document.createElement('div');
                                    div.className = 'jabatan-item';
                                    const safeId = jabatan.replace(/\s+/g, '_').replace(/[^\w\-]/g, '');
                                    div.innerHTML = `<input type="checkbox" name="jabatan_option" value="${jabatan}" id="jabatan_${safeId}"> <label for="jabatan_${safeId}">${jabatan}</label>`;
                                    jabatanGroup.appendChild(div);
                                });
                            } else {
                                jabatanGroup.innerHTML = '<small class="text-muted">Data jabatan tidak ditemukan.</small>';
                            }
                        } else {
                            namaInput.value = '';
                            jabatanGroup.innerHTML = '<small class="text-danger">Data karyawan tidak ditemukan di unit ini.</small>';
                        }
                    })
                    .catch(() => {
                        namaInput.value = '';
                        jabatanGroup.innerHTML = '<small class="text-danger">Gagal mengambil data karyawan.</small>';
                    });
            });

            // Trigger change sekali untuk load data awal jika ada default value
            unitKerjaSelect.dispatchEvent(new Event('change'));

            const form = document.getElementById('absensiForm');
            form.addEventListener('submit', function(e) {
                const foto = fotoInput.value;
                if (!foto) {
                    e.preventDefault();
                    document.getElementById('fotoErrorModal').style.display = 'flex';
                    return false;
                }
                const checkedJabatan = document.querySelectorAll('#jabatanGroup input[type="checkbox"]:checked');
                if (checkedJabatan.length === 0) {
                    e.preventDefault();
                    alert('Silakan pilih minimal satu jabatan.');
                    return false;
                }
                const jabatanValues = Array.from(checkedJabatan).map(cb => cb.value).join(', ');
                jabatanHidden.value = jabatanValues;
                return true;
            });

            window.closeFotoErrorModal = function() {
                document.getElementById('fotoErrorModal').style.display = 'none';
            }
        });
    </script>
</body>
</html>