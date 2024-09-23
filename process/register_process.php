<?php
require_once '../config.php';
require_once '../helper/helper.php';

$process = isset($_GET['process']) ? ($_GET["process"]) : false;

$username = $_POST["username"];
$password = $_POST["password"];
$repassword = $_POST["repassword"];

if (empty($username) || empty($password) || empty($repassword)) {
    header("Location: " . BASE_URL . 'register.php?process=failedempty');
} else {
    if ($password != $repassword) {
        header("Location: " . BASE_URL . 'register.php?process=failedpassword');
    } else {
        $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");

        if (mysqli_num_rows($query) !=0) {
            header("Location: " . BASE_URL . 'register.php?process=failedusername');
            
        } else {
            $passwordmd5 = md5($password);
            mysqli_query($koneksi, "INSERT INTO users (username, password) VALUES ('$username','$passwordmd5')");
            header("Location: " . BASE_URL);
        }
    } 
    
}