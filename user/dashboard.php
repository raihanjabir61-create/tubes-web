<?php
require_once __DIR__ . '/../config/db.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = get_db_connection();

// 1. Fetch User Statistics
// Total books donated (sum of jumlah where status = 'diterima')
$stats_diterima_res = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM tabel_donasi WHERE id_user = $user_id AND status = 'diterima'");
$stats_diterima = mysqli_fetch_assoc($stats_diterima_res)['total'] ?? 0;

// Total books pending (sum of jumlah where status = 'pending')
$stats_pending_res = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM tabel_donasi WHERE id_user = $user_id AND status = 'pending'");
$stats_pending = mysqli_fetch_assoc($stats_pending_res)['total'] ?? 0;

// Total donations submissions count
$stats_total_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM tabel_donasi WHERE id_user = $user_id");
$stats_total = mysqli_fetch_assoc($stats_total_res)['total'] ?? 0;

// 2. Fetch Donation History List
$history_query = "SELECT * FROM tabel_donasi WHERE id_user = $user_id ORDER BY tanggal DESC";
$history_res = mysqli_query($conn, $history_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Donatur - Sistem Donasi Buku</title>
    
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
                <i class="fas fa-book-open-reader"></i>
                <span>DonasiBuku</span>
            </div>
            
            <ul class="sidebar-menu">
                <li class="sidebar-item active">
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
                    <h1>Dashboard Donatur</h1>
                    <p>Halo, <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong>! Terima kasih atas kontribusi kebaikan Anda.</p>
                </div>
                <div class="header-action">
                    <a href="donate.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Donasikan Buku
                    </a>
                </div>
            </div>
            
            <!-- Statistics Overview -->
            <div class="stats-grid">
                <div class="stats-card">
                    <div class="stats-icon"><i class="fas fa-book"></i></div>
                    <div class="stats-info">
                        <h3><?php echo number_format($stats_diterima); ?></h3>
                        <p>Buku Diterima</p>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon" style="color: var(--warning); background-color: var(--warning-bg);"><i class="fas fa-clock"></i></div>
                    <div class="stats-info">
                        <h3><?php echo number_format($stats_pending); ?></h3>
                        <p>Buku Menunggu Verifikasi</p>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon" style="color: var(--primary); background-color: var(--primary-light);"><i class="fas fa-clipboard-list"></i></div>
                    <div class="stats-info">
                        <h3><?php echo number_format($stats_total); ?></h3>
                        <p>Total Ajuan Donasi</p>
                    </div>
                </div>
            </div>
            
            <!-- History Table -->
            <div class="data-container">
                <div class="data-header">
                    <h2>Riwayat Donasi Buku</h2>
                    <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                        Menampilkan <?php echo mysqli_num_rows($history_res); ?> ajuan donasi
                    </span>
                </div>
                
                <div class="table-responsive">
                    <?php if (mysqli_num_rows($history_res) > 0): ?>
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Cover</th>
                                    <th>Judul Buku</th>
                                    <th>Penulis</th>
                                    <th>Kategori</th>
                                    <th>Kondisi</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($history_res)): ?>
                                    <?php 
                                        $foto_path = '../uploads/' . htmlspecialchars($row['foto']);
                                        if (!file_exists($foto_path) || empty($row['foto'])) {
                                            $foto_path = 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&q=80&w=150';
                                        }
                                        $status = $row['status'];
                                        $badge_class = 'badge-pending';
                                        $status_text = 'Menunggu Konfirmasi';
                                        
                                        if ($status === 'diterima') {
                                            $badge_class = 'badge-diterima';
                                            $status_text = 'Diterima';
                                        } elseif ($status === 'ditolak') {
                                            $badge_class = 'badge-ditolak';
                                            $status_text = 'Ditolak';
                                        }
                                        
                                        $kondisi_text = ($row['kondisi'] === 'baru') ? 'Baru' : 'Bekas Layak';
                                    ?>
                                    <tr>
                                        <td>
                                            <!-- Clicking thumbnail opens Lightbox modal -->
                                            <a href="#" data-lightbox-src="<?php echo $foto_path; ?>" data-title="<?php echo htmlspecialchars($row['judul_buku']); ?>" data-meta="Kategori: <?php echo htmlspecialchars($row['kategori']); ?> | Penulis: <?php echo htmlspecialchars($row['penulis']); ?>">
                                                <img src="<?php echo $foto_path; ?>" class="table-book-cover" alt="Cover">
                                            </a>
                                        </td>
                                        <td style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($row['judul_buku']); ?></td>
                                        <td><?php echo htmlspecialchars($row['penulis']); ?></td>
                                        <td><span style="font-size: 0.85rem; background-color: var(--primary-light); color: var(--primary); padding: 0.25rem 0.5rem; border-radius: var(--radius-sm); font-weight: 500;"><?php echo htmlspecialchars($row['kategori']); ?></span></td>
                                        <td><?php echo $kondisi_text; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['jumlah']); ?></strong> eks</td>
                                        <td><?php echo date('d M Y, H:i', strtotime($row['tanggal'])); ?> WIB</td>
                                        <td>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 4rem 2rem;">
                            <i class="fas fa-folder-open" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <h3 style="color: var(--text-main);">Belum ada riwayat donasi</h3>
                            <p style="color: var(--text-muted); max-width: 400px; margin: 0.5rem auto 1.5rem;">Anda belum pernah mengajukan donasi buku. Mari bagikan buku bermanfaat pertama Anda!</p>
                            <a href="donate.php" class="btn btn-primary"><i class="fas fa-plus"></i> Mulai Donasi Pertama</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </main>
        
    </div>

    <!-- JS Scripts -->
    <script src="../assets/js/main.js"></script>

</body>
</html>
