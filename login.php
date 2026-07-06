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

// Check if redirected after registration
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $success_msg = 'Pendaftaran berhasil! Silakan masuk dengan email dan password Anda.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_msg = 'Email dan Password wajib diisi!';
    } else {
        $conn = get_db_connection();
        $email_clean = db_escape($email);

        $query = "SELECT * FROM tabel_users WHERE email = '$email_clean'";
        $res = mysqli_query($conn, $query);

        if ($res && mysqli_num_rows($res) === 1) {
            $user = mysqli_fetch_assoc($res);
            
            // Verify password hash
            if (password_verify($password, $user['password'])) {
                // Initialize session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Route based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: user/dashboard.php");
                }
                exit;
            } else {
                $error_msg = 'Password yang Anda masukkan salah!';
            }
        } else {
            $error_msg = 'Email tidak terdaftar!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Donatur - Sistem Donasi Buku</title>
    
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
                <h2>Selamat Datang Kembali</h2>
                <p>Masuk ke akun donatur Anda</p>
            </div>
            
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" id="email" name="email" class="input-control" placeholder="nama@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="input-control" placeholder="Masukkan password Anda" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    <i class="fas fa-sign-in-alt"></i> Masuk
                </button>
            </form>
            
            <div class="form-footer">
                Belum punya akun? <a href="register.php">Daftar sekarang</a>
                <div style="margin-top: 1.5rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <a href="login-admin.php" style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">
                        <i class="fas fa-user-shield"></i> Masuk sebagai Admin
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="assets/js/main.js"></script>

    <?php if ($error_msg !== ''): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showToast("<?php echo $error_msg; ?>", "error");
        });
    </script>
    <?php endif; ?>

    <?php if ($success_msg !== ''): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showToast("<?php echo $success_msg; ?>", "success");
        });
    </script>
    <?php endif; ?>

</body>
</html>
