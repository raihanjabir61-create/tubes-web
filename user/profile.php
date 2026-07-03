<?php
require_once __DIR__ . '/../config/db.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = get_db_connection();

$error_msg = '';
$success_msg = '';

// Check if redirected after update
if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $success_msg = 'Data profil Anda berhasil diperbarui!';
}

// 1. Fetch current profile data
$profile_query = "SELECT nama, email, no_telp, alamat FROM tabel_users WHERE id = $user_id";
$profile_res = mysqli_query($conn, $profile_query);
$user = mysqli_fetch_assoc($profile_res);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama']);
    $no_telp = sanitize($_POST['no_telp']);
    $alamat = sanitize($_POST['alamat']);

    // Validation
    if (empty($nama)) {
        $error_msg = 'Nama lengkap tidak boleh kosong!';
    } else {
        $nama_clean = db_escape($nama);
        $no_telp_clean = db_escape($no_telp);
        $alamat_clean = db_escape($alamat);

        $update_query = "UPDATE tabel_users 
                         SET nama = '$nama_clean', no_telp = '$no_telp_clean', alamat = '$alamat_clean' 
                         WHERE id = $user_id";
        
        if (mysqli_query($conn, $update_query)) {
            // Update session variables
            $_SESSION['nama'] = $nama;
            
            // Redirect to refresh profile page
            header("Location: profile.php?updated=1");
            exit;
        } else {
            $error_msg = 'Gagal menyimpan perubahan profil: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - Sistem Donasi Buku</title>
    
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
                <li class="sidebar-item">
                    <a href="donate.php">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>Donasi Buku</span>
                    </a>
                </li>
                <li class="sidebar-item active">
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
                    <h1>Pengaturan Profil Donatur</h1>
                    <p>Ubah detail kontak dan alamat penjemputan buku Anda di sini.</p>
                </div>
            </div>
            
            <!-- Profile Form Card -->
            <div class="data-container" style="max-width: 700px; padding: 2.5rem;">
                <form id="profile-form" action="profile.php" method="POST" novalidate>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Alamat Email (Tidak dapat diubah)</label>
                        <!-- Email is unique ID, locked out for safety in edit -->
                        <input type="email" id="email" class="input-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background-color: var(--light-bg); cursor: not-allowed; border-color: var(--border-color);">
                    </div>

                    <div class="form-group">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" class="input-control" placeholder="Masukkan nama lengkap Anda" required value="<?php echo htmlspecialchars($user['nama']); ?>">
                        <div class="form-error"></div>
                    </div>

                    <div class="form-group">
                        <label for="no_telp" class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="tel" id="no_telp" name="no_telp" class="input-control" placeholder="Contoh: 0812XXXXXXXX" value="<?php echo htmlspecialchars($user['no_telp'] ?? ''); ?>">
                        <div class="form-error"></div>
                    </div>

                    <div class="form-group">
                        <label for="alamat" class="form-label">Alamat Default Penjemputan Buku</label>
                        <textarea id="alamat" name="alamat" class="input-control" placeholder="Tuliskan alamat tinggal lengkap Anda untuk opsi penjemputan buku oleh kurir"><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
                        <div class="form-error"></div>
                    </div>

                    <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2.5rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-floppy-disk"></i> Simpan Perubahan
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

    <?php if ($success_msg !== ''): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showToast("<?php echo $success_msg; ?>", "success");
        });
    </script>
    <?php endif; ?>

</body>
</html>
