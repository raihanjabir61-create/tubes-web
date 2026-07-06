<?php
require_once __DIR__ . '/../config/db.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login-admin.php");
    exit;
}

$conn = get_db_connection();

// Filter inputs
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search_query = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build Query
$query_str = "SELECT d.*, u.nama as donor_nama FROM tabel_donasi d
              JOIN tabel_users u ON d.id_user = u.id";
$where_clauses = [];

if (!empty($filter_status)) {
    $status_esc = db_escape($filter_status);
    $where_clauses[] = "d.status = '$status_esc'";
}

if (!empty($search_query)) {
    $search_esc = db_escape($search_query);
    $where_clauses[] = "(d.judul_buku LIKE '%$search_esc%' OR d.penulis LIKE '%$search_esc%' OR u.nama LIKE '%$search_esc%')";
}

if (count($where_clauses) > 0) {
    $query_str .= " WHERE " . implode(" AND ", $where_clauses);
}

$query_str .= " ORDER BY d.tanggal DESC";
$catalog_res = mysqli_query($conn, $query_str);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku - Admin Dashboard</title>
    
    <!-- Fonts & FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    
    <style>
        /* Specific filters row */
        .filter-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .filter-item {
            flex: 1;
            min-width: 200px;
        }
        .filter-search-group {
            display: flex;
            gap: 0.5rem;
            flex: 2;
            min-width: 300px;
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
                <li class="sidebar-item">
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
                <li class="sidebar-item active">
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
                    <h1>Katalog Buku Donasi</h1>
                    <p>Melihat dan melacak seluruh riwayat buku yang diajukan ke sistem.</p>
                </div>
            </div>
            
            <!-- Filters Section -->
            <form action="catalog.php" method="GET" class="filter-row">
                <div class="filter-item">
                    <select name="status" class="input-control" onchange="this.form.submit()" style="padding: 0.65rem 1.25rem;">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo ($filter_status === 'pending') ? 'selected' : ''; ?>>Menunggu Konfirmasi</option>
                        <option value="diterima" <?php echo ($filter_status === 'diterima') ? 'selected' : ''; ?>>Diterima</option>
                        <option value="ditolak" <?php echo ($filter_status === 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>
                
                <div class="filter-search-group">
                    <input type="text" name="search" class="input-control" placeholder="Cari judul buku, penulis, atau donatur..." value="<?php echo htmlspecialchars($search_query); ?>" style="padding: 0.65rem 1.25rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem;">
                        <i class="fas fa-magnifying-glass"></i> Cari
                    </button>
                    <?php if (!empty($search_query) || !empty($filter_status)): ?>
                        <a href="catalog.php" class="btn btn-secondary" style="padding: 0.65rem 1.25rem; display: flex; align-items: center;">
                            <i class="fas fa-arrow-rotate-left"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
            
            <!-- Catalog List Table -->
            <div class="data-container">
                <div class="data-header">
                    <h2>Daftar Buku Terkumpul</h2>
                    <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                        Ditemukan <?php echo mysqli_num_rows($catalog_res); ?> buku
                    </span>
                </div>
                
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Cover</th>
                                <th>Buku & Penulis</th>
                                <th>Kategori</th>
                                <th>Kondisi</th>
                                <th>Jumlah</th>
                                <th>Nama Donatur</th>
                                <th>Tanggal Submit</th>
                                <th>Status</th>
                                <th>Koreksi Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($catalog_res) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($catalog_res)): ?>
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
                                            <span style="font-size: 0.85rem; background-color: var(--teal-light); color: var(--teal); padding: 0.25rem 0.5rem; border-radius: var(--radius-sm); font-weight: 500;">
                                                <?php echo htmlspecialchars($row['kategori']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $kondisi_text; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['jumlah']); ?></strong> eks</td>
                                        <td><?php echo htmlspecialchars($row['donor_nama']); ?></td>
                                        <td><?php echo date('d M Y, H:i', strtotime($row['tanggal'])); ?> WIB</td>
                                        <td>
                                            <span id="badge-status-<?php echo $row['id']; ?>" class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td>
                                            <!-- Status Correction Button Actions -->
                                            <div style="display: flex; gap: 0.25rem;">
                                                <button class="btn btn-secondary btn-sm action-correct" data-id="<?php echo $row['id']; ?>" data-status="diterima" title="Ubah status ke Diterima" <?php echo ($status === 'diterima') ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-circle-check" style="color: var(--success);"></i>
                                                </button>
                                                <button class="btn btn-secondary btn-sm action-correct" data-id="<?php echo $row['id']; ?>" data-status="ditolak" title="Ubah status ke Ditolak" <?php echo ($status === 'ditolak') ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-circle-xmark" style="color: var(--danger);"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 4rem 2rem;">
                                        <i class="fas fa-book-open" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                                        <h3 style="color: var(--text-main);">Tidak ada data katalog buku</h3>
                                        <p style="color: var(--text-muted); max-width: 400px; margin: 0.5rem auto 0;">Belum ada data buku yang sesuai dengan filter pencarian Anda saat ini.</p>
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
            // Bind status adjustment actions
            const correctBtns = document.querySelectorAll('.action-correct');
            correctBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const donationId = this.getAttribute('data-id');
                    const status = this.getAttribute('data-status');
                    const row = document.getElementById(`row-${donationId}`);
                    
                    if (!donationId || !status || !row) return;
                    
                    if (!confirm(`Apakah Anda yakin ingin mengganti status donasi ini menjadi ${status === 'diterima' ? 'DITERIMA' : 'DITOLAK'}?`)) {
                        return;
                    }

                    // Prepare form data
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
                            window.showToast(data.message, 'success');
                            
                            // Dynamically update status badge
                            const badge = document.getElementById(`badge-status-${donationId}`);
                            if (badge) {
                                badge.className = `badge badge-${status}`;
                                badge.textContent = status === 'diterima' ? 'Diterima' : 'Ditolak';
                            }
                            
                            // Re-enable/disable correction buttons based on status
                            const rowButtons = row.querySelectorAll('.action-correct');
                            rowButtons.forEach(btnEl => {
                                const btnStatus = btnEl.getAttribute('data-status');
                                btnEl.disabled = (btnStatus === status);
                            });
                        } else {
                            window.showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        window.showToast('Gagal memproses data. Silakan hubungi admin sistem!', 'error');
                        console.error('Error:', error);
                    });
                });
            });
        });
    </script>

</body>
</html>
