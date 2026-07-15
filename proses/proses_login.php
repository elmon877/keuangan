<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        header("Location: ../login.php?pesan=gagal");
        exit();
    }

    // 2. AMBIL DATA USER DAN LEVEL-NYA
    $query = "SELECT id, password, level FROM users WHERE username = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // 3. VERIFIKASI PASSWORD
        if (password_verify($password, $row['password'])) {
            session_start();
            $_SESSION['user_id']  = $row['id'];      // MENYIMPAN ID USER (Untuk Fitur Privat)
            $_SESSION['username'] = $username;
            $_SESSION['level']    = $row['level'];   // Menyimpan level ke session
            
            header("Location: ../index.php"); 
            exit();
        }
    }
    
    mysqli_stmt_close($stmt);
    header("Location: ../login.php?pesan=gagal");
    exit();
} else {
    header("Location: ../login.php");
    exit();
}
?>
