<?php
require_once __DIR__ . '/config/db.php';

echo "--- Memulai Inisialisasi Database ---\n";

// 1. Hubungkan ke MySQL tanpa database terlebih dahulu
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);
if (!$link) {
    die("Gagal menghubungkan ke MySQL server: " . mysqli_connect_error() . "\n");
}

// 2. Buat database jika belum ada
$db_name = DB_NAME;
$sql_create_db = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($link, $sql_create_db)) {
    echo "Database `$db_name` berhasil dibuat atau sudah ada.\n";
} else {
    die("Gagal membuat database: " . mysqli_error($link) . "\n");
}

// Tutup koneksi sementara
mysqli_close($link);

// 3. Hubungkan kembali dengan database terpilih
$conn = get_db_connection();

// 4. Baca file schema.sql dan eksekusi
$schema_file = __DIR__ . '/schema.sql';
if (!file_exists($schema_file)) {
    die("File schema.sql tidak ditemukan di: $schema_file\n");
}

$schema_sql = file_get_contents($schema_file);

// Jalankan multi query
if (mysqli_multi_query($conn, $schema_sql)) {
    // Kosongkan hasil query multi_query agar bisa menjalankan query berikutnya
    do {
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));
    echo "Tabel database berhasil diinisialisasi dari schema.sql.\n";
} else {
    die("Gagal menjalankan schema.sql: " . mysqli_error($conn) . "\n");
}

// 5. Seed data Admin dan User
$users_to_seed = [
    [
        'nama' => 'Administrator',
        'email' => 'admin@donasibuku.com',
        'password' => 'admin123',
        'role' => 'admin',
        'no_telp' => '081234567890',
        'alamat' => 'Kantor Pusat Donasi Buku, Jakarta'
    ],
    [
        'nama' => 'Budi Donatur',
        'email' => 'user@donasibuku.com',
        'password' => 'user123',
        'role' => 'user',
        'no_telp' => '089876543210',
        'alamat' => 'Jl. Kemerdekaan No. 45, Bandung'
    ]
];

foreach ($users_to_seed as $u) {
    $email = mysqli_real_escape_string($conn, $u['email']);
    $nama = mysqli_real_escape_string($conn, $u['nama']);
    $role = mysqli_real_escape_string($conn, $u['role']);
    $no_telp = mysqli_real_escape_string($conn, $u['no_telp']);
    $alamat = mysqli_real_escape_string($conn, $u['alamat']);
    
    // Cek apakah user sudah ada
    $check_query = "SELECT id FROM tabel_users WHERE email = '$email'";
    $check_res = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_res) == 0) {
        // Hash password menggunakan password_hash
        $hashed_password = password_hash($u['password'], PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO tabel_users (nama, email, password, role, no_telp, alamat) 
                         VALUES ('$nama', '$email', '$hashed_password', '$role', '$no_telp', '$alamat')";
        
        if (mysqli_query($conn, $insert_query)) {
            echo "Akun {$u['role']} berhasil di-seed: {$u['email']} (Password: {$u['password']})\n";
        } else {
            echo "Gagal men-seed akun {$u['role']}: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "Akun {$u['role']} ({$u['email']}) sudah ada di database.\n";
    }
}

echo "--- Inisialisasi Database Selesai ---\n";
?>
