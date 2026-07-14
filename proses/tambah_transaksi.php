<?php
// 1. Memanggil file koneksi
include '../config/koneksi.php';

// 2. Pastikan data dikirim melalui method POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil input dan bersihkan (opsional tapi disarankan)
    $nama    = $_POST['nama'];
    $jenis   = $_POST['jenis'];
    $jumlah  = $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];

    // 3. Gunakan Prepared Statements (Mencegah SQL Injection)
    // Tanda '?' adalah placeholder untuk data
    $stmt = $koneksi->prepare("INSERT INTO transaksi (nama, jenis, jumlah, tanggal) VALUES (?, ?, ?, ?)");

    // 4. Bind parameter (s = string, i = integer/angka)
    // Sesuaikan tipe data: "ssis" artinya (nama=string, jenis=string, jumlah=int, tanggal=string)
    $stmt->bind_param("ssis", $nama, $jenis, $jumlah, $tanggal);

    // 5. Jalankan query
    if ($stmt->execute()) {
        header("location:../index.php?status=sukses");
        exit; // Selalu tambahkan exit setelah header location
    } else {
        echo "Gagal menyimpan data: " . $stmt->error;
    }

    // Tutup statement
    $stmt->close();
} else {
    // Jika user akses file ini langsung lewat URL, lempar balik ke index
    header("location:../index.php");
}
?>