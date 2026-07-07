<?php
require_once __DIR__ . '/../config/db.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_buku = sanitize($_POST['judul_buku']);
    $penulis = sanitize($_POST['penulis']);
    $kategori = sanitize($_POST['kategori']);
    $kondisi = sanitize($_POST['kondisi']);
    $jumlah = intval($_POST['jumlah']);
    $user_id = $_SESSION['user_id'];

    // Server-side validation
    if (empty($judul_buku) || empty($penulis) || empty($kategori) || empty($kondisi) || $jumlah <= 0) {
        $error_msg = 'Harap isi semua kolom formulir dengan benar!';
    } elseif (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $error_msg = 'Wajib mengunggah foto buku!';
    } else {
        // Handle file upload
        $file = $_FILES['foto'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_exts)) {
            $error_msg = 'Ekstensi file tidak diizinkan! Hanya JPG, JPEG, PNG, dan WebP.';
        } elseif ($file_size > 2 * 1024 * 1024) {
            $error_msg = 'Ukuran file terlalu besar! Maksimal 2MB.';
        } else {
            // Read file content and convert to base64
            $file_data = file_get_contents($file_tmp);
            $mime_type = isset($file['type']) && !empty($file['type']) ? $file['type'] : 'image/jpeg';
            if (function_exists('mime_content_type')) {
                $mime_type = mime_content_type($file_tmp);
            }
            $base64_data = 'data:' . $mime_type . ';base64,' . base64_encode($file_data);

            // Optional: save to local uploads folder if writable (for local fallback)
            $upload_dir = '../uploads/';
            if (is_writable($upload_dir) || (!file_exists($upload_dir) && @mkdir($upload_dir, 0755, true))) {
                $unique_filename = uniqid('book_', true) . '.' . $ext;
                @move_uploaded_file($file_tmp, $upload_dir . $unique_filename);
            }

            $conn = get_db_connection();
            
            $judul_clean = db_escape($judul_buku);
            $penulis_clean = db_escape($penulis);
            $kategori_clean = db_escape($kategori);
            $kondisi_clean = db_escape($kondisi);
            $foto_clean = db_escape($base64_data);

            $insert_query = "INSERT INTO tabel_donasi (id_user, judul_buku, penulis, kategori, kondisi, jumlah, foto, status) 
                             VALUES ($user_id, '$judul_clean', '$penulis_clean', '$kategori_clean', '$kondisi_clean', $jumlah, '$foto_clean', 'pending')";
            
            if (mysqli_query($conn, $insert_query)) {
                // Redirect back to dashboard with success query param
                header("Location: dashboard.php?donated=1");
                exit;
            } else {
                $error_msg = 'Gagal menyimpan data donasi ke database: ' . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi Buku Baru - Sistem Donasi Buku</title>
    
    <!-- Fonts & FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/form.css">
</head>
<body>

    <div class="dashboard-wrapper">
        
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-book-open-reader"></i>
                <span>DonasiBuku</span>
            </div>
            
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="dashboard.php">
                        <i class="fas fa-gauge"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item active">
                    <a href="donate.php">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>Donasi Buku</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="profile.php">
                        <i class="fas fa-user-gear"></i>
                        <span>Edit Profil</span>
                    </a>
                </li>
                <li class="sidebar-item" style="margin-top: 2rem;">
                    <a href="../index.php">
                        <i class="fas fa-home"></i>
                        <span>Kembali ke Beranda</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="../logout.php" style="display: flex; align-items: center; gap: 1rem; color: var(--danger); font-weight: 600; padding: 0.85rem 1.25rem;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>
        
        <!-- MAIN CONTENT -->
        <main class="main-content">
            
            <div class="content-header">
                <div class="content-title">
                    <h1>Formulir Donasi Buku</h1>
                    <p>Masukkan rincian buku yang ingin Anda sumbangkan di bawah ini.</p>
                </div>
            </div>
            
            <!-- Donation Form Card -->
            <div class="data-container" style="max-width: 800px; padding: 2.5rem;">
                <form id="donation-form" action="donate.php" method="POST" enctype="multipart/form-data" novalidate>
                    
                    <div class="form-group">
                        <label for="judul_buku" class="form-label">Judul Buku</label>
                        <input type="text" id="judul_buku" name="judul_buku" class="input-control" placeholder="Tuliskan judul lengkap buku" required>
                        <div class="form-error"></div>
                    </div>

                    <div class="form-group">
                        <label for="penulis" class="form-label">Penulis / Pengarang</label>
                        <input type="text" id="penulis" name="penulis" class="input-control" placeholder="Nama penulis buku" required>
                        <div class="form-error"></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="kategori" class="form-label">Kategori / Genre</label>
                            <select id="kategori" name="kategori" class="input-control" required>
                                <option value="" disabled selected>Pilih Kategori</option>
                                <option value="Fiksi">Fiksi (Novel/Cerpen)</option>
                                <option value="Non-Fiksi">Non-Fiksi (Memoar/Esai)</option>
                                <option value="Pelajaran">Pelajaran & Edukasi</option>
                                <option value="Anak-Anak">Buku Anak-Anak</option>
                                <option value="Agama">Spiritual & Agama</option>
                                <option value="Teknologi">Sains & Teknologi</option>
                                <option value="Sosial">Sosial & Budaya</option>
                                <option value="Lainnya">Lain-lain</option>
                            </select>
                            <div class="form-error"></div>
                        </div>

                        <div class="form-group">
                            <label for="kondisi" class="form-label">Kondisi Buku</label>
                            <select id="kondisi" name="kondisi" class="input-control" required>
                                <option value="baru" selected>Baru (Masih Segel/Gress)</option>
                                <option value="bekas_layak">Bekas Layak Baca (Tanpa Halaman Hilang)</option>
                            </select>
                            <div class="form-error"></div>
                        </div>
                    </div>

                    <div class="form-group" style="max-width: 200px;">
                        <label for="jumlah" class="form-label">Jumlah Buku (Eks)</label>
                        <input type="number" id="jumlah" name="jumlah" class="input-control" min="1" value="1" required>
                        <div class="form-error"></div>
                    </div>

                    <!-- Beautiful Custom Drag and Drop File Upload Area -->
                    <div class="form-group">
                        <label class="form-label">Foto Buku</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="foto" name="foto" class="file-upload-input" accept="image/*" required>
                            <div class="file-upload-content">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <div class="file-upload-text">Klik untuk telusuri file atau seret gambar ke sini</div>
                                <div class="file-upload-hint">Format yang diizinkan: JPG, JPEG, PNG, WebP (Maksimal 2MB)</div>
                            </div>
                            <img src="" class="file-upload-preview" alt="Pratinjau Foto Buku">
                        </div>
                        <div class="form-error" style="display: none; color: var(--danger); margin-top: 0.5rem; font-weight: 500;"></div>
                    </div>

                    <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2.5rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Kirim Pengajuan
                        </button>
                    </div>

                </form>
            </div>
            
        </main>
        
    </div>

    <!-- JS Scripts -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/validation.js"></script>

    <?php if ($error_msg !== ''): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showToast("<?php echo $error_msg; ?>", "error");
        });
    </script>
    <?php endif; ?>

</body>
</html>
