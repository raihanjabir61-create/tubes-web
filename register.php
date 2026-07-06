<?php
require_once __DIR__ . '/config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit;
}

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $no_telp = sanitize($_POST['no_telp']);
    $alamat = sanitize($_POST['alamat']);

    // Server-side validation
    if (empty($nama) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_msg = 'Semua kolom wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Format email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error_msg = 'Password minimal harus 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error_msg = 'Konfirmasi password tidak sesuai!';
    } else {
        $conn = get_db_connection();
        $email_clean = db_escape($email);
        
        // Check if email already exists
        $check_query = "SELECT id FROM tabel_users WHERE email = '$email_clean'";
        $check_res = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_res) > 0) {
            $error_msg = 'Email ini sudah terdaftar. Silakan gunakan email lain!';
        } else {
            // Hash password
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $nama_clean = db_escape($nama);
            $no_telp_clean = db_escape($no_telp);
            $alamat_clean = db_escape($alamat);

            $insert_query = "INSERT INTO tabel_users (nama, email, password, role, no_telp, alamat) 
                             VALUES ('$nama_clean', '$email_clean', '$hashed_pass', 'user', '$no_telp_clean', '$alamat_clean')";
            
            if (mysqli_query($conn, $insert_query)) {
                // Redirect to login page with success notification parameter
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error_msg = 'Terjadi kesalahan sistem. Silakan coba beberapa saat lagi!';
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
    <title>Daftar Donatur - Sistem Donasi Buku</title>
    
    <!-- Google Fonts & FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <a href="index.php" class="nav-brand" style="justify-content: center; margin-bottom: 1.5rem;">
                    <i class="fas fa-book-open-reader"></i>
                    <span>DonasiBuku</span>
                </a>
                <h2>Mulai Berbagi Kebaikan</h2>
                <p>Daftarkan akun donatur Anda sekarang</p>
            </div>
            
            <form id="register-form" action="register.php" method="POST" novalidate>
                <div class="form-group">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="input-control" placeholder="Masukkan nama lengkap Anda" required value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                    <div class="form-error"></div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" id="email" name="email" class="input-control" placeholder="nama@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <div class="form-error"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="input-control" placeholder="Min. 6 karakter" required>
                        <div class="form-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="input-control" placeholder="Ulangi password" required>
                        <div class="form-error"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="no_telp" class="form-label">Nomor Telepon (WhatsApp)</label>
                    <input type="tel" id="no_telp" name="no_telp" class="input-control" placeholder="Contoh: 0812XXXXXXXX" value="<?php echo isset($_POST['no_telp']) ? htmlspecialchars($_POST['no_telp']) : ''; ?>">
                    <div class="form-error"></div>
                </div>

                <div class="form-group">
                    <label for="alamat" class="form-label">Alamat Pengambilan Buku (Opsional)</label>
                    <textarea id="alamat" name="alamat" class="input-control" placeholder="Tuliskan alamat lengkap untuk penjemputan buku jika diperlukan"><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                    <div class="form-error"></div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    <i class="fas fa-user-plus"></i> Daftar Akun
                </button>
            </form>
            
            <div class="form-footer">
                Sudah punya akun? <a href="login.php">Masuk di sini</a>
            </div>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/validation.js"></script>

    <?php if ($error_msg !== ''): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showToast("<?php echo $error_msg; ?>", "error");
        });
    </script>
    <?php endif; ?>

</body>
</html>
