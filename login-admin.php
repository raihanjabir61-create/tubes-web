<?php
require_once __DIR__ . '/config/db.php';

// Redirect if already logged in and is admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit;
}

$error_msg = '';

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
            
            // Check if user is admin
            if ($user['role'] !== 'admin') {
                $error_msg = 'Akses Ditolak: Akun Anda tidak memiliki hak akses Administrator!';
            } else {
                // Verify password hash
                if (password_verify($password, $user['password'])) {
                    // Initialize session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    header("Location: admin/dashboard.php");
                    exit;
                } else {
                    $error_msg = 'Password administrator salah!';
                }
            }
        } else {
            $error_msg = 'Akun administrator tidak ditemukan!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - Sistem Donasi Buku</title>
    
    <!-- Google Fonts & FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
    
    <style>
        /* Dedicated styling overrides for admin login */
        body {
            background-color: hsl(222, 47%, 6%);
        }
        .auth-card {
            background-color: var(--dark-card);
            border-color: rgba(255, 255, 255, 0.05);
            color: white;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }
        .auth-header h2 {
            color: white;
        }
        .form-label {
            color: hsl(215, 16%, 75%);
        }
        .input-control {
            background-color: hsl(222, 47%, 10%);
            border-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .input-control:focus {
            background-color: hsl(222, 47%, 8%);
            border-color: var(--primary);
        }
        .form-footer a {
            color: var(--primary);
        }
        .admin-badge {
            background-color: rgba(114, 46, 209, 0.2);
            color: hsl(256, 82%, 75%);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 1rem;
            border: 1px solid rgba(114, 46, 209, 0.3);
        }
    </style>
</head>
<body>

    <div class="auth-wrapper" style="background-color: transparent;">
        <div class="auth-card">
            <div class="auth-header">
                <a href="index.php" class="nav-brand" style="justify-content: center; margin-bottom: 1rem;">
                    <i class="fas fa-shield-halved"></i>
                    <span>DonasiBuku</span>
                </a>
                <div class="admin-badge">ADMINISTRATOR GATE</div>
                <h2>Kelola Dashboard</h2>
                <p>Silakan masuk menggunakan kredensial admin</p>
            </div>
            
            <form action="login-admin.php" method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">Email Administrator</label>
                    <input type="email" id="email" name="email" class="input-control" placeholder="admin@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="input-control" placeholder="Masukkan password admin" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    <i class="fas fa-lock-open"></i> Verifikasi Kunci
                </button>
            </form>
            
            <div class="form-footer">
                Bukan admin? <a href="login.php" style="color: hsl(256, 82%, 75%);">Kembali ke Portal User</a>
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

</body>
</html>
