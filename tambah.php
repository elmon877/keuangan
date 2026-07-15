<?php
session_start();
// Proteksi: Jika belum login, paksa balik ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Transaksi Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h3 class="mb-4 text-center">Form Tambah Transaksi</h3>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Form dikirim ke proses_tambah.php di folder proses -->
                    <form action="proses/proses_tambah.php" method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Transaksi</label>
                            <input type="text" name="nama" class="form-control" placeholder="Contoh: Gaji Bulanan / Beli Kopi" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Transaksi</label>
                            <select name="jenis" class="form-select" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="pemasukan">Pemasukan</option>
                                <option value="pengeluaran">Pengeluaran</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah Uang (Nominal)</label>
                            <input type="number" name="jumlah" class="form-control" placeholder="Contoh: 50000" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Transaksi</label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-success">Simpan Transaksi</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
