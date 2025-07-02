<?php
/*
* File: fungsi/register_process.php
* Deskripsi: Menangani proses pendaftaran user baru.
*/
require_once __DIR__ . '/../config.php';
session_start();

function sanitize_input($data) {
    return htmlentities(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = sanitize_input($_POST['nama']);
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password']; // Password tidak perlu sanitize karena akan di-hash
    $role     = sanitize_input($_POST['role']);

    // --- Validasi Sederhana ---
    if (empty($nama) || empty($username) || empty($password)) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Semua kolom harus diisi.'];
        header('Location: ../register.php');
        exit;
    }
    
    // Cek apakah username sudah ada
    $sql_check = "SELECT username FROM users WHERE username = ?";
    $stmt_check = $db->prepare($sql_check);
    $stmt_check->execute([$username]);
    if ($stmt_check->fetch()) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Username sudah digunakan, silakan pilih yang lain.'];
        header('Location: ../register.php');
        exit;
    }

    // Hash password untuk keamanan (md5 tidak disarankan, gunakan password_hash untuk proyek baru)
    $hashed_password = md5($password);

    // Masukkan data user baru ke database
    $sql_insert = "INSERT INTO users (nama, username, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt_insert = $db->prepare($sql_insert);
    
    try {
        $stmt_insert->execute([$nama, $username, $hashed_password, $role]);
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Pendaftaran berhasil! Silakan login.'];
        header('Location: ../login.php'); // Arahkan ke halaman login setelah berhasil
        exit;
    } catch (PDOException $e) {
        // Jika terjadi error database
        error_log("User registration error: " . $e->getMessage());
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Terjadi kesalahan pada server, silakan coba lagi.'];
        header('Location: ../register.php');
        exit;
    }

} else {
    // Jika file diakses langsung, redirect
    header('Location: ../register.php');
    exit;
}
?>