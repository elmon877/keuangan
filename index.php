<?php
session_start();

// Cek apakah session username dan user_id sudah ada
if(!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("location:login.php");
    exit;
}

include 'config/koneksi.php';

// Ambil ID user dari session dan pastikan aman dari SQL Injection
$user_id = mysqli_real_escape_string($koneksi, $_SESSION['user_id']);

// 1. Ambil data transaksi HANYA untuk user yang sedang login
$query = "SELECT * FROM transaksi WHERE user_id = '$user_id' ORDER BY id DESC";
$ambil_data = mysqli_query($koneksi, $query);

// 2. Hitung total pemasukan user tersebut
$query_masuk = "SELECT SUM(jumlah) AS total FROM transaksi WHERE jenis='pemasukan' AND user_id = '$user_id'";
$hasil_masuk = mysqli_query($koneksi, $query_masuk);
$data_masuk  = mysqli_fetch_array($hasil_masuk);
$total_pemasukan = $data_masuk['total'] ?? 0;

// 3. Hitung total pengeluaran user tersebut
$query_keluar = "SELECT SUM(jumlah) AS total FROM transaksi WHERE jenis='pengeluaran' AND user_id = '$user_id'";
$hasil_keluar = mysqli_query($koneksi, $query_keluar);
$data_keluar  = mysqli_fetch_array($hasil_keluar);
$total_pengeluaran = $data_keluar['total'] ?? 0;

$sisa_saldo = $total_pemasukan - $total_pengeluaran;

// 4. Hitung total baris transaksi user tersebut
$query_count = "SELECT COUNT(*) AS total FROM transaksi WHERE user_id = '$user_id'";
$hasil_count = mysqli_query($koneksi, $query_count);
$data_count  = mysqli_fetch_array($hasil_count);
$total_transaksi = $data_count['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor — Fisclux</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:    #0F1B2D;
            --navy2:   #162438;
            --card:    #1A2B42;
            --card2:   #1F3250;
            --gold:    #C9A84C;
            --gold2:   #E8C96A;
            --gold-bg: rgba(201,168,76,.08);
            --text:    #E8EDF4;
            --muted:   #6B7A8D;
            --border:  rgba(255,255,255,.07);
            --green:   #2DBD8F;
            --green-bg:rgba(45,189,143,.1);
            --red:     #E05C5C;
            --red-bg:  rgba(224,92,92,.1);
            --sidebar: 240px;
        }

        html, body {
            min-height: 100vh;
            background: var(--navy);
            font-family: 'Inter', sans-serif;
            color: var(--text);
        }

        /* ─── Layout ─── */
        .layout { display: flex; min-height: 100vh; }

        /* ─── Sidebar ─── */
        .sidebar {
            width: var(--sidebar);
            background: var(--navy2);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }

        .sidebar-logo {
            display: flex; align-items: center; gap: 10px;
            padding: 28px 24px;
            border-bottom: 1px solid var(--border);
        }
        .logo-icon {
            width: 36px; height: 36px;
            background: var(--gold);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .logo-icon svg { width: 20px; height: 20px; }
        .logo-text { font-size: 16px; font-weight: 600; }

        .sidebar-nav { flex: 1; padding: 20px 12px; }
        .nav-label {
            font-size: 10px; font-weight: 600; letter-spacing: .1em;
            text-transform: uppercase; color: var(--muted);
            padding: 0 12px; margin-bottom: 8px; margin-top: 20px;
        }
        .nav-label:first-child { margin-top: 0; }

        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 10px;
            font-size: 14px; font-weight: 500; color: var(--muted);
            text-decoration: none; transition: background .15s, color .15s;
            cursor: pointer; margin-bottom: 2px;
        }
        .nav-item svg { width: 18px; height: 18px; fill: currentColor; flex-shrink: 0; }
        .nav-item:hover { background: rgba(255,255,255,.05); color: var(--text); }
        .nav-item.active {
            background: var(--gold-bg); color: var(--gold);
            border: 1px solid rgba(201,168,76,.18);
        }

        .sidebar-footer { padding: 16px 12px; border-top: 1px solid var(--border); }
        .user-block {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 10px; background: var(--card);
        }
        .avatar {
            width: 34px; height: 34px; background: var(--gold);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px; color: var(--navy); flex-shrink: 0;
        }
        .user-info { flex: 1; overflow: hidden; }
        .user-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 11px; color: var(--muted); }
        .logout-btn {
            display: flex; align-items: center; justify-content: center;
            width: 28px; height: 28px; border-radius: 7px;
            background: rgba(224,92,92,.12); color: var(--red);
            text-decoration: none; transition: background .15s; flex-shrink: 0;
        }
        .logout-btn:hover { background: rgba(224,92,92,.25); }
        .logout-btn svg { width: 15px; height: 15px; fill: currentColor; }

        /* ─── Main ─── */
        .main {
            margin-left: var(--sidebar);
            flex: 1; display: flex; flex-direction: column;
        }

        /* ─── Top bar ─── */
        .topbar {
            padding: 24px 32px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .topbar-left h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 24px; line-height: 1.2;
        }
        .topbar-left p { color: var(--muted); font-size: 13px; margin-top: 2px; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }

        .add-btn, button.add-btn {
            display: flex; align-items: center; gap: 8px;
            background: var(--gold); color: var(--navy);
            font-weight: 600; font-size: 14px; padding: 9px 18px;
            border-radius: 10px; text-decoration: none;
            border: none; cursor: pointer; font-family: inherit;
            transition: background .2s, box-shadow .2s, transform .15s;
        }
        .add-btn:hover, button.add-btn:hover {
            background: var(--gold2);
            box-shadow: 0 6px 20px rgba(201,168,76,.3);
            transform: translateY(-1px);
        }
        .add-btn svg { width: 16px; height: 16px; fill: currentColor; }

        /* ─── Content ─── */
        .content { padding: 32px; flex: 1; }

        /* ─── KPI Cards ─── */
        .kpi-row {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 20px; margin-bottom: 32px;
        }
        .kpi-card {
            background: var(--card); border-radius: 16px; padding: 24px;
            border: 1px solid var(--border); position: relative; overflow: hidden;
            animation: fadeUp .5s ease both;
        }
        .kpi-card:nth-child(2) { animation-delay: .1s; }
        .kpi-card:nth-child(3) { animation-delay: .2s; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .kpi-card::before {
            content: ''; position: absolute; top: 0; left: 24px; right: 24px;
            height: 2px; border-radius: 0 0 4px 4px;
        }
        .kpi-saldo::before   { background: linear-gradient(90deg, var(--gold), var(--gold2)); }
        .kpi-masuk::before   { background: linear-gradient(90deg, var(--green), #5eecc8); }
        .kpi-keluar::before  { background: linear-gradient(90deg, var(--red), #f09090); }

        .kpi-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .kpi-label {
            font-size: 12px; font-weight: 600; letter-spacing: .06em;
            text-transform: uppercase; color: var(--muted);
        }
        .kpi-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .kpi-saldo   .kpi-icon { background: var(--gold-bg); }
        .kpi-masuk   .kpi-icon { background: var(--green-bg); }
        .kpi-keluar  .kpi-icon { background: var(--red-bg); }
        .kpi-icon svg { width: 18px; height: 18px; }
        .kpi-saldo   .kpi-icon svg { fill: var(--gold); }
        .kpi-masuk   .kpi-icon svg { fill: var(--green); }
        .kpi-keluar  .kpi-icon svg { fill: var(--red); }

        .kpi-value {
            font-family: 'DM Serif Display', serif;
            font-size: 28px; line-height: 1.1; margin-bottom: 6px;
        }
        .kpi-saldo  .kpi-value { color: var(--gold); }
        .kpi-masuk  .kpi-value { color: var(--green); }
        .kpi-keluar .kpi-value { color: var(--red); }
        .kpi-sub { font-size: 12px; color: var(--muted); }

        /* ─── Table card ─── */
        .table-card {
            background: var(--card); border-radius: 16px;
            border: 1px solid var(--border); overflow: hidden;
            animation: fadeUp .5s .3s ease both;
        }
        .table-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 24px; border-bottom: 1px solid var(--border);
        }
        .table-title { font-size: 16px; font-weight: 600; }
        .table-count {
            font-size: 12px; color: var(--muted);
            background: rgba(255,255,255,.06); padding: 4px 10px; border-radius: 20px;
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            padding: 12px 20px; text-align: left; font-size: 11px; font-weight: 600;
            letter-spacing: .07em; text-transform: uppercase; color: var(--muted);
            background: rgba(255,255,255,.02); border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        tbody td {
            padding: 14px 20px; font-size: 14px; border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr { transition: background .15s; }
        tbody tr:hover { background: rgba(255,255,255,.025); }

        .td-num { width: 40px; color: var(--muted); font-size: 13px; font-weight: 500; }
        .td-name { font-weight: 500; }

        .badge-jenis {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .badge-jenis::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
        .badge-masuk   { background: var(--green-bg); color: var(--green); }
        .badge-masuk::before   { background: var(--green); }
        .badge-keluar  { background: var(--red-bg);   color: var(--red); }
        .badge-keluar::before  { background: var(--red); }

        .td-amount { font-weight: 600; font-variant-numeric: tabular-nums; }
        .amount-masuk  { color: var(--green); }
        .amount-keluar { color: var(--red); }
        .td-date { color: var(--muted); font-size: 13px; white-space: nowrap; }

        .action-btn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 12px; border-radius: 7px; font-size: 12px; font-weight: 600;
            text-decoration: none; background: rgba(224,92,92,.12); color: var(--red);
            border: 1px solid rgba(224,92,92,.2); transition: background .15s, border-color .15s;
        }
        .action-btn:hover { background: rgba(224,92,92,.22); border-color: rgba(224,92,92,.4); }
        .action-btn svg { width: 12px; height: 12px; fill: currentColor; }

        .empty-state { text-align: center; padding: 60px 24px; color: var(--muted); }
        .empty-state svg { width: 48px; height: 48px; fill: var(--muted); margin-bottom: 16px; opacity: .4; }
        .empty-state p { font-size: 14px; }

        /* ─── Modal (Floating Form) ─── */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 27, 45, 0.75);
            backdrop-filter: blur(6px);
            display: flex; align-items: center; justify-content: center;
            z-index: 1000; opacity: 0; visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            padding: 20px;
        }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        
        .modal-content {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 16px; width: 100%; max-width: 480px; padding: 28px;
            transform: translateY(20px) scale(0.95);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 24px 48px rgba(0,0,0,0.4);
        }
        .modal-overlay.active .modal-content { transform: translateY(0) scale(1); }
        
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .modal-title { font-family: 'DM Serif Display', serif; font-size: 22px; color: var(--text); }
        
        .modal-close {
            background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            color: var(--muted); width: 36px; height: 36px; border-radius: 10px;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .modal-close:hover { background: var(--red-bg); color: var(--red); border-color: rgba(224,92,92,.2); }
        .modal-close svg { width: 18px; height: 18px; fill: currentColor; }

        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 500; color: var(--muted); margin-bottom: 8px; }
        
        .form-input, .form-select {
            width: 100%; background: var(--navy2); border: 1px solid var(--border);
            border-radius: 10px; padding: 12px 14px; color: var(--text);
            font-family: 'Inter', sans-serif; font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus, .form-select:focus {
            outline: none; border-color: var(--gold); box-shadow: 0 0 0 3px var(--gold-bg);
        }
        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236B7A8D' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 14px center;
        }
        .form-input[type="date"] { color-scheme: dark; }
        
        .btn-submit {
            width: 100%; background: var(--gold); color: var(--navy);
            font-weight: 600; font-size: 15px; padding: 12px; border-radius: 10px;
            border: none; cursor: pointer; font-family: inherit;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s; margin-top: 8px;
        }
        .btn-submit:hover {
            background: var(--gold2); transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(201,168,76,.3);
        }

        /* ─── Responsive ─── */
        @media (max-width: 900px) { .kpi-row { grid-template-columns: 1fr; } }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main    { margin-left: 0; }
            .content { padding: 20px 16px; }
            .topbar  { padding: 16px 20px; }
            .kpi-value { font-size: 22px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .kpi-card, .table-card, .modal-content { animation: none; transition: none; }
        }
    </style>
</head>
<body>

<div class="layout">
    <!-- ─── Sidebar ─── -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 3v18h18M7 16l4-4 4 4 4-6" stroke="#0F1B2D" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <span class="logo-text">Fisclux</span>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Menu Utama</div>
            <a class="nav-item active" href="#">
                <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                Dasbor
            </a>

            <?php if($_SESSION['level'] == 'admin'): ?>
            <a class="nav-item" href="#" onclick="openModal(); return false;">
                <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Transaksi
            </a>
            <?php endif; ?>

            <div class="nav-label">Laporan</div>
            <a class="nav-item" href="#">
                <svg viewBox="0 0 24 24"><path d="M9 17v-2m3 2v-4m3 4v-6M5 20h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/></svg>
                Rekap Bulanan
            </a>
            <a class="nav-item" href="#">
                <svg viewBox="0 0 24 24"><path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                Statistik
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-block">
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <div class="user-role"><?php echo ucfirst($_SESSION['level']); ?></div>
                </div>
                <a href="proses/logout.php" class="logout-btn" title="Keluar">
                    <svg viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- ─── Main content ─── -->
    <div class="main">
        <div class="topbar">
            <div class="topbar-left">
                <h1>Catatan Keuangan</h1>
                <p>Ringkasan &amp; riwayat transaksi Anda</p>
            </div>
            <div class="topbar-right">
                <?php if($_SESSION['level'] == 'admin'): ?>
                <button type="button" class="add-btn" onclick="openModal()">
                    <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    Tambah Transaksi
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="content">
            <div class="kpi-row">
                <div class="kpi-card kpi-saldo">
                    <div class="kpi-header">
                        <span class="kpi-label">Sisa Saldo</span>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value">Rp <?php echo number_format($sisa_saldo, 0, ',', '.'); ?></div>
                    <div class="kpi-sub">Saldo tersedia saat ini</div>
                </div>

                <div class="kpi-card kpi-masuk">
                    <div class="kpi-header">
                        <span class="kpi-label">Total Pemasukan</span>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1zM4 22v-7"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value">Rp <?php echo number_format($total_pemasukan, 0, ',', '.'); ?></div>
                    <div class="kpi-sub">Akumulasi seluruh pemasukan</div>
                </div>

                <div class="kpi-card kpi-keluar">
                    <div class="kpi-header">
                        <span class="kpi-label">Total Pengeluaran</span>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24"><path d="M20 12V22H4V12M22 7H2v5h20V7zM12 22V7M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value">Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></div>
                    <div class="kpi-sub">Akumulasi seluruh pengeluaran</div>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <span class="table-title">Riwayat Transaksi</span>
                    <span class="table-count"><?php echo $total_transaksi; ?> transaksi</span>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="td-num">#</th>
                                <th>Nama Transaksi</th>
                                <th>Jenis</th>
                                <th>Jumlah</th>
                                <th>Tanggal</th>
                                <?php if($_SESSION['level'] == 'admin') echo "<th>Aksi</th>"; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $has_data = false;
                            while($data = mysqli_fetch_array($ambil_data)):
                                $has_data = true;
                                $is_masuk = $data['jenis'] == 'pemasukan';
                            ?>
                            <tr>
                                <td class="td-num"><?php echo $no++; ?></td>
                                <td class="td-name"><?php echo htmlspecialchars($data['nama']); ?></td>
                                <td>
                                    <span class="badge-jenis <?php echo $is_masuk ? 'badge-masuk' : 'badge-keluar'; ?>">
                                        <?php echo ucfirst($data['jenis']); ?>
                                    </span>
                                </td>
                                <td class="td-amount <?php echo $is_masuk ? 'amount-masuk' : 'amount-keluar'; ?>">
                                    <?php echo $is_masuk ? '+' : '−'; ?> Rp <?php echo number_format($data['jumlah'], 0, ',', '.'); ?>
                                </td>
                                <td class="td-date"><?php echo date('d M Y', strtotime($data['tanggal'])); ?></td>
                                <?php if($_SESSION['level'] == 'admin'): ?>
                                <td>
                                    <a href="proses/hapus.php?id=<?php echo $data['id']; ?>"
                                       class="action-btn"
                                       onclick="return confirm('Yakin ingin menghapus transaksi ini?')">
                                        <svg viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>

                            <?php if(!$has_data): ?>
                            <tr>
                                <td colspan="<?php echo $_SESSION['level'] == 'admin' ? 6 : 5; ?>">
                                    <div class="empty-state">
                                        <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <p>Belum ada transaksi yang dicatat.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ─── Modal Tambah Transaksi (Floating) ─── -->
<div class="modal-overlay" id="transactionModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Transaksi</h2>
            <button class="modal-close" onclick="closeModal()" title="Tutup">
                <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        
        <!-- Pastikan file 'proses/tambah_transaksi.php' sudah kamu buat untuk menangani $_POST -->
        <form action="proses/tambah_transaksi.php" method="POST">
            <div class="form-group">
                <label class="form-label">Nama Transaksi</label>
                <input type="text" name="nama" class="form-input" placeholder="Contoh: Gaji, Makan Siang, Listrik" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Jenis</label>
                <select name="jenis" class="form-select" required>
                    <option value="pemasukan">Pemasukan</option>
                    <option value="pengeluaran">Pengeluaran</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Jumlah (Rp)</label>
                <input type="number" name="jumlah" class="form-input" placeholder="0" min="0" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <button type="submit" class="btn-submit">Simpan Transaksi</button>
        </form>
    </div>
</div>

<script>
    // Fungsi membuka modal
    function openModal() {
        document.getElementById('transactionModal').classList.add('active');
    }
    
    // Fungsi menutup modal
    function closeModal() {
        document.getElementById('transactionModal').classList.remove('active');
    }
    
    // Tutup modal jika user klik di area gelap (luar form)
    document.getElementById('transactionModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Tutup modal dengan tombol Escape di keyboard
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>

</body>
</html>
