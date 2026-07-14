<?php
include '../config/koneksi.php';

$id = $_GET['id'];

$query = "DELETE FROM transaksi WHERE id = '$id'";
$hapus = mysqli_query($koneksi, $query);

if($hapus) {
    header("location:../index.php");
} else {
    echo "Gagal menghapus data transaksi: " . mysqli_error($koneksi);
}
?>