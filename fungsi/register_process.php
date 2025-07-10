<?php
require_once __DIR__ . '/../config.php';
session_start();

function sanitize_input($data) {
    return htmlentities(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = sanitize_input($_POST['nama']);
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password']; 
    $role     = sanitize_input($_POST['role']);

   
    if (empty($nama) || empty($username) || empty($password)) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Semua kolom harus diisi.'];
        header('Location: ../register.php');
        exit;
    }
    
    
    $sql_check = "SELECT username FROM users WHERE username = ?";
    $stmt_check = $db->prepare($sql_check);
    $stmt_check->execute([$username]);
    if ($stmt_check->fetch()) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Username sudah digunakan, silakan pilih yang lain.'];
        header('Location: ../register.php');
        exit;
    }

    
    $hashed_password = md5($password);

   
    $sql_insert = "INSERT INTO users (nama, username, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt_insert = $db->prepare($sql_insert);
    
    try {
        $stmt_insert->execute([$nama, $username, $hashed_password, $role]);
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Pendaftaran berhasil! Silakan login.'];
        header('Location: ../login.php'); 
        exit;
    } catch (PDOException $e) {
       
        error_log("User registration error: " . $e->getMessage());
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Terjadi kesalahan pada server, silakan coba lagi.'];
        header('Location: ../register.php');
        exit;
    }

} else {
    header('Location: ../register.php');
    exit;
}
?>