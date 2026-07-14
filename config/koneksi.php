<?php
$host = "localhost";
$user = "root";
$pass = ""; // Default Laragon kosong
$db   = "db_keuangan";

// Menghubungkan PHP ke database MySQL
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Jika koneksi gagal, hentikan aplikasi dan tampilkan error
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>