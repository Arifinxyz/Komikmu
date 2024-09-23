<?php
require_once '../config.php';
require_once '../helper/helper.php';

session_start();

if ($_SESSION["role"] != "admin") { header("Location: " . BASE_URL); exit(); } 

// Ambil ID buku dari URL
$buku_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cek apakah formulir telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    // Hapus data buku dari database
    $query = "DELETE FROM buku WHERE id_buku = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $buku_id);

    if ($stmt->execute()) {
        // Redirect ke halaman admin setelah sukses
        header("Location: " . BASE_URL);
        exit();
    } else {
        echo "Terjadi kesalahan saat menghapus data: " . $koneksi->error;
    }
    $stmt->close();
}

// Ambil data buku dari database untuk konfirmasi
$sql = "SELECT * FROM buku WHERE id_buku = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $buku_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Buku</title>
    <link rel="stylesheet" href="assets/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        main {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #333;
        }
        p {
            font-size: 18px;
            color: #333;
        }
        button {
            padding: 10px 15px;
            background-color: #d9534f;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>
    <main>
        <h1>Hapus Buku</h1>
        <p>Apakah Anda yakin ingin menghapus buku "<strong><?= htmlspecialchars($data['judul_buku'], ENT_QUOTES, 'UTF-8'); ?></strong>"?</p>
        <form action="buku_hapus.php?id=<?= $buku_id; ?>" method="POST">
            <button type="submit" name="delete">ok</button>
         </form>
