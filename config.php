<?php
/*
* File: config.php
* Deskripsi: File konfigurasi database untuk aplikasi kasir.
* Menggunakan PDO untuk koneksi ke database MySQL.
*/

// Informasi koneksi database
$host = "localhost"; // Host database Anda, biasanya 'localhost'
$dbname = "kasir-app"; // Nama database Anda
$username = "root"; // Username database Anda
$password = ""; // Password database Anda (kosong jika tidak ada)

try {
    // Buat objek PDO untuk koneksi database
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    // Atur mode error untuk PDO ke Exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Atur mode fetch default ke associative array
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // echo "Koneksi database berhasil!"; // Uncomment untuk pengujian koneksi
} catch (PDOException $e) {
    // Tangani error koneksi database
    die("Koneksi database gagal: " . $e->getMessage());
}
?>