<?php
/*
* File: fungsi/auth.php
* Deskripsi: Menangani proses otentikasi login.
*/
require_once __DIR__ . '/../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cari user di database berdasarkan username
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifikasi user dan password
    // Catatan: md5() sudah usang dan tidak aman. Pertimbangkan password_hash() untuk keamanan yang lebih baik.
    // Kode ini dibuat agar cocok dengan hash password 'kasir' di database Anda.
    if ($user && md5($password) === $user['password']) {
        // Jika login berhasil, simpan data user ke session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_role'] = $user['role'];
        
        // Arahkan ke halaman utama/dashboard
        header('Location: ../index.php');
        exit;
    } else {
        // Jika login gagal, buat pesan error dan arahkan kembali ke halaman login
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Username atau password salah.'
        ];
        header('Location: ../login.php');
        exit;
    }
} else {
    // Jika file diakses langsung, redirect ke halaman login
    header('Location: ../login.php');
    exit;
}
?>