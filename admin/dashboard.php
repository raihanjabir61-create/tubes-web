<?php
require_once __DIR__ . '/../config/db.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login-admin.php");
    exit;
}

$conn = get_db_connection();

// 1. Fetch Statistics
// Total Users (role = 'user')
$users_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM tabel_users WHERE role = 'user'");
$total_users = mysqli_fetch_assoc($users_res)['total'] ?? 0;

// Total Books Received
$books_res = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM tabel_donasi WHERE status = 'diterima'");
$total_books = mysqli_fetch_assoc($books_res)['total'] ?? 0;
if ($total_books === null) $total_books = 0;

// Total Pending Donations
$pending_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM tabel_donasi WHERE status = 'pending'");
$total_pending = mysqli_fetch_assoc($pending_res)['total'] ?? 0;

// 2. Fetch Pending Donations List
$pending_list_query = "SELECT d.*, u.nama as donor_nama, u.no_telp as donor_telp, u.alamat as donor_alamat 
                       FROM tabel_donasi d
                       JOIN tabel_users u ON d.id_user = u.id
                       WHERE d.status = 'pending'
                       ORDER BY d.tanggal ASC";
$pending_list_res = mysqli_query($conn, $pending_list_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sistem Donasi Buku</title>
    
    <!-- Fonts & FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    
    <style>
        /* Specific overrides for AJAX slide-out rows */
        .fade-out-row {
            opacity: 0;
            transform: translateX(-20px);
            transition: opacity 0.4s ease, transform 0.4s ease;
        }
    </style>
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
                <li class="sidebar-item active">
                    <a href="dashboard.php">
                        <i class="fas fa-gauge"></i>
                        <span>Dashboard Admin</span>
                    </a>
                </li>
                <li class="sidebar-item">
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
                    <h1>Dashboard Utama Admin</h1>
                    <p>Halo, <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong>! Kelola pengajuan donasi buku di sini.</p>
                </div>
            </div>
            
            <!-- Statistics Panel -->
            <div class="stats-grid">
                <div class="stats-card">
                    <div class="stats-icon" style="color: var(--primary); background-color: var(--primary-light);"><i class="fas fa-users-viewfinder"></i></div>
                    <div class="stats-info">
                        <h3><?php echo number_format($total_users); ?></h3>
                        <p>Total Donatur</p>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon" style="color: var(--success); background-color: var(--success-bg);"><i class="fas fa-circle-check"></i></div>
                    <div class="stats-info">
                        <h3><?php echo number_format($total_books); ?></h3>
                        <p>Buku Diterima</p>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon" style="color: var(--warning); background-color: var(--warning-bg);"><i class="fas fa-hourglass-half"></i></div>
                    <div class="stats-info">
                        <h3 id="stats-pending-count"><?php echo number_format($total_pending); ?></h3>
                        <p>Menunggu Verifikasi</p>
                    </div>
                </div>
            </div>
            
            <!-- Pending Donations Table -->
            <div class="data-container">
                <div class="data-header">
                    <h2>Verifikasi Ajuan Donasi Masuk</h2>
                    <span id="table-total-count" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                        Ada <?php echo mysqli_num_rows($pending_list_res); ?> pengajuan menunggu verifikasi
                    </span>
                </div>
                
                <div class="table-responsive">
                    <table class="custom-table" id="pending-table">
                        <thead>
                            <tr>
                                <th>Cover</th>
                                <th>Buku & Penulis</th>
                                <th>Kategori</th>
                                <th>Kondisi & Jml</th>
                                <th>Profil Donatur</th>
                                <th>Tanggal Masuk</th>
                                <th>Aksi Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody id="pending-table-body">
                            <?php if (mysqli_num_rows($pending_list_res) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($pending_list_res)): ?>
                                    <?php 
                                        $foto_path = '../uploads/' . htmlspecialchars($row['foto']);
                                        if (!file_exists($foto_path) || empty($row['foto'])) {
                                            $foto_path = 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&q=80&w=150';
                                        }
                                        $kondisi_text = ($row['kondisi'] === 'baru') ? 'Baru' : 'Bekas Layak';
                                        $wa_link = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $row['donor_telp']);
                                    ?>
                                    <tr id="row-<?php echo $row['id']; ?>">
                                        <td>
                                            <!-- Opens Lightbox Modal -->
                                            <a href="#" data-lightbox-src="<?php echo $foto_path; ?>" data-title="<?php echo htmlspecialchars($row['judul_buku']); ?>" data-meta="Kategori: <?php echo htmlspecialchars($row['kategori']); ?> | Penulis: <?php echo htmlspecialchars($row['penulis']); ?>">
                                                <img src="<?php echo $foto_path; ?>" class="table-book-cover" alt="Cover">
                                            </a>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($row['judul_buku']); ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">Penulis: <?php echo htmlspecialchars($row['penulis']); ?></div>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.85rem; background-color: var(--primary-light); color: var(--primary); padding: 0.25rem 0.5rem; border-radius: var(--radius-sm); font-weight: 500;">
                                                <?php echo htmlspecialchars($row['kategori']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div><?php echo $kondisi_text; ?></div>
                                            <div style="font-weight: 700; font-size: 0.9rem; color: var(--primary);"><?php echo htmlspecialchars($row['jumlah']); ?> eks</div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($row['donor_nama']); ?></div>
                                            <?php if (!empty($row['donor_telp'])): ?>
                                                <div style="font-size: 0.8rem;">
                                                    <a href="<?php echo $wa_link; ?>" target="_blank" style="color: var(--success); font-weight: 500;">
                                                        <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($row['donor_telp']); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d M Y, H:i', strtotime($row['tanggal'])); ?> WIB</td>
                                        <td>
                                            <div class="table-actions">
                                                <button class="btn btn-success btn-sm action-verify" data-id="<?php echo $row['id']; ?>" data-status="diterima">
                                                    <i class="fas fa-check"></i> Terima
                                                </button>
                                                <button class="btn btn-danger btn-sm action-verify" data-id="<?php echo $row['id']; ?>" data-status="ditolak">
                                                    <i class="fas fa-xmark"></i> Tolak
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr id="no-pending-row">
                                    <td colspan="7" style="text-align: center; padding: 4rem 2rem;">
                                        <i class="fas fa-circle-check" style="font-size: 3rem; color: var(--success); margin-bottom: 1rem;"></i>
                                        <h3 style="color: var(--text-main);">Semua donasi telah ditinjau!</h3>
                                        <p style="color: var(--text-muted); max-width: 400px; margin: 0.5rem auto 0;">Tidak ada pengajuan donasi baru yang menunggu verifikasi saat ini.</p>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle AJAX Status Verification (Accept/Reject)
            const buttons = document.querySelectorAll('.action-verify');
            
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    const donationId = this.getAttribute('data-id');
                    const status = this.getAttribute('data-status');
                    const row = document.getElementById(`row-${donationId}`);
                    
                    if (!donationId || !status || !row) return;
                    
                    // Show a confirm dialog (optional, let's process directly or confirm for delete/reject)
                    if (status === 'ditolak' && !confirm('Apakah Anda yakin ingin menolak pengajuan donasi buku ini?')) {
                        return;
                    }
                    
                    // Disable row actions during process
                    const actionButtons = row.querySelectorAll('.action-verify');
                    actionButtons.forEach(btn => btn.disabled = true);
                    
                    // Prepare AJAX POST request
                    const formData = new FormData();
                    formData.append('id', donationId);
                    formData.append('status', status);
                    
                    fetch('update_status.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success toast
                            window.showToast(data.message, 'success');
                            
                            // Row visual slide/fade deletion animation
                            row.classList.add('fade-out-row');
                            row.addEventListener('transitionend', function() {
                                row.remove();
                                
                                // Recalculate remaining pending listings in UI
                                const tbody = document.getElementById('pending-table-body');
                                const remainingRows = tbody.querySelectorAll('tr:not(#no-pending-row)');
                                
                                // Update Stats Header Counters
                                const tableCounter = document.getElementById('table-total-count');
                                const statCounter = document.getElementById('stats-pending-count');
                                
                                const countVal = remainingRows.length;
                                if (tableCounter) tableCounter.textContent = `Ada ${countVal} pengajuan menunggu verifikasi`;
                                if (statCounter) statCounter.textContent = countVal;
                                
                                // Show placeholder if no entries remain
                                if (countVal === 0) {
                                    tbody.innerHTML = `
                                        <tr id="no-pending-row">
                                            <td colspan="7" style="text-align: center; padding: 4rem 2rem;">
                                                <i class="fas fa-circle-check" style="font-size: 3rem; color: var(--success); margin-bottom: 1rem;"></i>
                                                <h3 style="color: var(--text-main);">Semua donasi telah ditinjau!</h3>
                                                <p style="color: var(--text-muted); max-width: 400px; margin: 0.5rem auto 0;">Tidak ada pengajuan donasi baru yang menunggu verifikasi saat ini.</p>
                                            </td>
                                        </tr>
                                    `;
                                }
                            });
                        } else {
                            window.showToast(data.message, 'error');
                            actionButtons.forEach(btn => btn.disabled = false);
                        }
                    })
                    .catch(error => {
                        window.showToast('Gagal memproses data. Silakan hubungi admin sistem!', 'error');
                        actionButtons.forEach(btn => btn.disabled = false);
                        console.error('Error:', error);
                    });
                });
            });
        });
    </script>

</body>
</html>
