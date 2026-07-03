<?php
require_once __DIR__ . '/../config/db.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login-admin.php");
    exit;
}

$conn = get_db_connection();

// Fetch Donors and their stats
$donors_query = "SELECT u.*, 
                 COUNT(d.id) as total_submissions, 
                 SUM(CASE WHEN d.status = 'diterima' THEN d.jumlah ELSE 0 END) as total_books_accepted 
                 FROM tabel_users u
                 LEFT JOIN tabel_donasi d ON u.id = d.id_user
                 WHERE u.role = 'user'
                 GROUP BY u.id
                 ORDER BY u.created_at DESC";
$donors_res = mysqli_query($conn, $donors_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Admin Dashboard</title>
    
    <!-- Fonts & FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

    <div class="dashboard-wrapper">
        
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-shield-halved"></i>
                <span>DonasiBuku</span>
            </div>
            
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="dashboard.php">
                        <i class="fas fa-gauge"></i>
                        <span>Dashboard Admin</span>
                    </a>
                </li>
                <li class="sidebar-item active">
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Manajemen Pengguna</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="catalog.php">
                        <i class="fas fa-book-bookmark"></i>
                        <span>Katalog Buku</span>
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
                    <h1>Manajemen Pengguna (Donatur)</h1>
                    <p>Melihat profil lengkap dan performa donasi dari donatur terdaftar.</p>
                </div>
            </div>
            
            <!-- Users List Table -->
            <div class="data-container">
                <div class="data-header">
                    <h2>Daftar Donatur Terdaftar</h2>
                    <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                        Total <?php echo mysqli_num_rows($donors_res); ?> donatur
                    </span>
                </div>
                
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Nama Donatur</th>
                                <th>Email</th>
                                <th>No. Telepon / WA</th>
                                <th>Alamat Rumah</th>
                                <th>Terdaftar Pada</th>
                                <th>Ajuan Pengajuan</th>
                                <th>Buku Disumbangkan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($donors_res) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($donors_res)): ?>
                                    <?php 
                                        $wa_link = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $row['no_telp']);
                                        $books_count = $row['total_books_accepted'] ?? 0;
                                    ?>
                                    <tr>
                                        <td style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($row['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td>
                                            <?php if (!empty($row['no_telp'])): ?>
                                                <a href="<?php echo $wa_link; ?>" target="_blank" style="color: var(--success); font-weight: 600;">
                                                    <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($row['no_telp']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-style: italic;">Tidak ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['alamat'])): ?>
                                                <span style="font-size: 0.9rem;" title="<?php echo htmlspecialchars($row['alamat']); ?>">
                                                    <?php echo (strlen($row['alamat']) > 45) ? htmlspecialchars(substr($row['alamat'], 0, 42)) . '...' : htmlspecialchars($row['alamat']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-style: italic;">Belum diisi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                        <td style="text-align: center;"><strong><?php echo htmlspecialchars($row['total_submissions']); ?></strong> kali</td>
                                        <td style="text-align: center;">
                                            <span style="background-color: var(--success-bg); color: var(--success); padding: 0.25rem 0.6rem; border-radius: 50px; font-weight: 700;">
                                                <?php echo htmlspecialchars($books_count); ?> eks
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 4rem 2rem;">
                                        <i class="fas fa-users-slash" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                                        <h3 style="color: var(--text-main);">Belum ada donatur terdaftar</h3>
                                        <p style="color: var(--text-muted); max-width: 400px; margin: 0.5rem auto 0;">Sistem belum mendeteksi pendaftaran donatur baru saat ini.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </main>
        
    </div>

    <!-- JS Scripts -->
    <script src="../assets/js/main.js"></script>

</body>
</html>
