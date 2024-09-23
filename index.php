<?php
require_once "config.php";
require_once "helper/helper.php";
session_start();

// Cek apakah role ada di session
if (!isset($_SESSION["role"])) {
    // Jika tidak ada, redirect ke home.php
    header("Location: " . BASE_URL . 'home.php?page=public');
    exit();
}

// Redirect berdasarkan nilai role
if ($_SESSION["role"] == 'admin') {
    header("Location: " . BASE_URL . 'home.php?page=admin');
    exit();
} elseif ($_SESSION["role"] == null) {
    header("Location: " . BASE_URL . 'home.php?page=public');
    exit();
}

// Jika tidak memenuhi salah satu kondisi, redirect ke home.php
header("Location: " . BASE_URL . 'home.php');
exit();
?>
