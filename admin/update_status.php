<?php
require_once __DIR__ . '/../config/db.php';

// Set response content type to JSON
header('Content-Type: application/json');

// Check Session & Role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak: Anda tidak memiliki hak akses Administrator!'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read input from POST or JSON raw body
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $status = isset($_POST['status']) ? sanitize($_POST['status']) : '';

    // Validate inputs
    if ($id <= 0 || !in_array($status, ['diterima', 'ditolak'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Parameter tidak valid atau tidak lengkap!'
        ]);
        exit;
    }

    $conn = get_db_connection();
    $status_clean = db_escape($status);

    // Update query
    $query = "UPDATE tabel_donasi SET status = '$status_clean' WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        // If row was affected, success
        if (mysqli_affected_rows($conn) > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Donasi berhasil " . ($status === 'diterima' ? 'diterima!' : 'ditolak!')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Data donasi tidak ditemukan atau tidak ada perubahan!'
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan database: ' . mysqli_error($conn)
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metode HTTP tidak diizinkan! Gunakan POST.'
    ]);
}
?>
