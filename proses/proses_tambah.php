<?php
session_start();

// Proteksi: Jika user mencoba akses langsung tanpa login, lempar ke login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// 1. PENGATURAN KONEKSI DATABASE
$host = "sql103.infinityfree.com";
$user = "if0_42410902";
$pass = "elmon22072007";
$db   = "if0_42410902_Fisclux";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form transaksi
    $nama    = trim($_POST['nama']);
    $jenis   = trim($_POST['jenis']);
    $jumlah  = trim($_POST['jumlah']);
    $tanggal = trim($_POST['tanggal']);
    
    // AMBIL ID USER YANG SEDANG LOGIN (Kunci untuk data privat)
    $user_id = $_SESSION['user_id']; 

    if (empty($nama) || empty($jenis) || empty($jumlah) || empty($tanggal)) {
        header("Location: ../tambah.php?pesan=kosong");
        exit();
    }

    // 2. QUERY SIMPAN DATA (Sertakan user_id)
    $query = "INSERT INTO transaksi (user_id, nama_transaksi, jenis, jumlah, tanggal) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    
    // Binding parameters (i = integer untuk user_id & jumlah, s = string untuk nama, jenis, tanggal)
    mysqli_stmt_bind_param($stmt, "issis", $user_id, $nama, $jenis, $jumlah, $tanggal);

    if (mysqli_stmt_execute($stmt)) {
        // Jika berhasil, balikkan ke halaman utama index.php
        header("Location: ../index.php?pesan=sukses_tambah");
        exit();
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($koneksi);
    }

    mysqli_stmt_close($stmt);
} else {
    header("Location: ../tambah.php");
    exit();
}
?>
