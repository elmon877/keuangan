<?php
session_start();

// 1. Pastikan user sudah login sebelum bisa menambah data
if(!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("location:../login.php");
    exit;
}

// 2. Hubungkan ke database (sesuaikan path jika berbeda)
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil user_id dari session dan data input dari form
    $user_id = $_SESSION['user_id'];
    $nama    = $_POST['nama'];
    $jenis   = $_POST['jenis'];
    $jumlah  = $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];

    // 3. Query INSERT yang menyertakan 'user_id'
    $query = "INSERT INTO transaksi (user_id, nama, jenis, jumlah, tanggal) VALUES (?, ?, ?, ?, ?)";
    
    // 4. Siapkan prepared statement agar aman dari SQL Injection
    if ($stmt = $koneksi->prepare($query)) {
        // "isss" berarti: i = integer (user_id), s = string (nama, jenis, tanggal), i = integer (jumlah)
        $stmt->bind_param("issis", $user_id, $nama, $jenis, $jumlah, $tanggal);
        
        if ($stmt->execute()) {
            // Jika sukses, kembali ke halaman utama (index.php)
            header("location:../index.php");
            exit;
        } else {
            echo "Gagal menyimpan transaksi: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Gagal menyiapkan query: " . $koneksi->error;
    }
} else {
    // Jika diakses langsung tanpa POST, lempar balik ke index
    header("location:../index.php");
    exit;
}
?>
