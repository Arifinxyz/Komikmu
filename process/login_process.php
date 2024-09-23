<?php
require_once '../config.php';
require_once '../helper/helper.php';    

// Menghindari SQL Injection dengan menggunakan mysqli_real_escape_string
$username = mysqli_real_escape_string($koneksi, $_POST["username"]);
$password = md5($_POST["password"]); // Menggunakan hash md5 untuk password (lebih baik gunakan password_hash() untuk keamanan lebih baik)

// Query untuk mengecek keberadaan username dan password
$query = mysqli_query($koneksi, "SELECT * FROM users  WHERE username = '$username' AND password = '$password'");

if (mysqli_num_rows($query) != 0) {
    session_start();

    // Memperbaiki penulisan mysqli_fetch_assoc
    $row = mysqli_fetch_assoc($query); 

    // Menyimpan informasi pengguna ke dalam sesi
    $_SESSION["username"] = $row['username'];
    $_SESSION["id"] = $row['id'];
    $_SESSION["role"] = $row['role'];

    // Mengarahkan pengguna berdasarkan peran (role)
    if ($row['role'] == 'admin') {
        header("Location: " . BASE_URL . "home.php?page=admin");
    } else {
        header("Location: " . BASE_URL . "home.php?page=public");
    } // Menambahkan exit setelah header untuk menghentikan eksekusi script
}  else {
    header("Location: " . BASE_URL . 'login.php?process=false' );
}