<?php
session_start();
include '../config/koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

$query = "SELECT * FROM pengguna WHERE username='$username' AND password='$password'";
$cek_data = mysqli_query($koneksi, $query);
$ketemu = mysqli_num_rows($cek_data);

if($ketemu > 0) {
    $data = mysqli_fetch_array($cek_data);
    $_SESSION['username'] = $data['username'];
    $_SESSION['level']    = $data['level'];
    header("location:../index.php");
} else {
    header("location:../login.php?pesan=gagal");
}
?>