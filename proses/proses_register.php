<?php
// Aktifkan error reporting untuk mempermudah tracking jika ada error lain
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. PENGATURAN KONEKSI DATABASE
$host = "sql103.infinityfree.com";
$user = "if0_42410902";
$pass = "elmon22072007";
$db   = "if0_42410902_Fisclux";

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek apakah koneksi ke database berhasil
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Memastikan data dikirim via method POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Cek apakah input kosong
    if (empty($username) || empty($password)) {
        header("Location: ../register.php?pesan=kosong");
        exit();
    }

    // 2. CEK APAKAH USERNAME SUDAH TERDAFTAR
    $query_cek = "SELECT id FROM users WHERE username = ?";
    $stmt_cek = mysqli_prepare($koneksi, $query_cek);
    mysqli_stmt_bind_param($stmt_cek, "s", $username);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);
    
    if (mysqli_stmt_num_rows($stmt_cek) > 0) {
        mysqli_stmt_close($stmt_cek);
        header("Location: ../register.php?pesan=username_ada");
        exit();
    }
    mysqli_stmt_close($stmt_cek);

    // 3. ENKRIPSI PASSWORD
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // 4. INSERT USER BARU KE DATABASE
    $query_insert = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt_insert = mysqli_prepare($koneksi, $query_insert);
    mysqli_stmt_bind_param($stmt_insert, "ss", $username, $password_hashed);
    
    if (mysqli_stmt_execute($stmt_insert)) {
        mysqli_stmt_close($stmt_insert);
        header("Location: ../register.php?pesan=sukses");
        exit();
    } else {
        mysqli_stmt_close($stmt_insert);
        header("Location: ../register.php?pesan=gagal");
        exit();
    }
} else {
    header("Location: ../register.php");
    exit();
}
?>
