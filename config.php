<?php
$host = 'localhost';
$user = 'root'; // Ganti jika perlu
$pass = ''; // Ganti jika perlu
$db = 'login_project'; // Ganti dengan nama database Anda

$koneksi = mysqli_connect($host, $user, $pass, $db) or die("koneksi database gagal");
