<?php
// 1. PENGATURAN KONEKSI DATABASE
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_keuangan"; // Sudah diubah ke database kamu

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek apakah koneksi ke database berhasil
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Memastikan data dikirim via method POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan hapus spasi di awal/akhir input
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 2. CEK APAKAH USERNAME SUDAH TERDAFTAR
    $query_cek = "SELECT id FROM users WHERE username = ?";
    $stmt_cek = mysqli_prepare($koneksi, $query_cek);
    mysqli_stmt_bind_param($stmt_cek, "s", $username);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);
    
    // Jika username sudah ada di database
    if (mysqli_stmt_num_rows($stmt_cek) > 0) {
        mysqli_stmt_close($stmt_cek);
        header("Location: ../register.php?pesan=username_ada");
        exit();
    }
    mysqli_stmt_close($stmt_cek);

    // 3. ENKRIPSI PASSWORD (MENGGUNAKAN BCRYPT)
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // 4. INSERT USER BARU KE DATABASE
    $query_insert = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt_insert = mysqli_prepare($koneksi, $query_insert);
    mysqli_stmt_bind_param($stmt_insert, "ss", $username, $password_hashed);
    
    if (mysqli_stmt_execute($stmt_insert)) {
        mysqli_stmt_close($stmt_insert);
        // Registrasi berhasil, kembali ke register.php dengan status sukses
        header("Location: ../register.php?pesan=sukses");
        exit();
    } else {
        mysqli_stmt_close($stmt_insert);
        // Jika terjadi error saat query eksekusi
        header("Location: ../register.php?pesan=gagal");
        exit();
    }
} else {
    // Jika file diakses langsung tanpa form POST, tendang balik ke register.php
    header("Location: ../register.php");
    exit();
}
?>