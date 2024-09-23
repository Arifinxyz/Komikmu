<?php 
require_once 'config.php';
require_once 'helper/helper.php';
require_once 'helper/viewformat.php';

// Hitung tanggal satu minggu yang lalu
$tanggal_minggu_lalu = date('Y-m-d H:i:s', strtotime('-1 week'));

// Query untuk mendapatkan buku yang paling banyak dilihat dalam 1 minggu terakhir dari tabel view
$query = "SELECT b.id_buku, b.judul_buku, b.sampul, COALESCE(SUM(v.jumlah_view), 0) AS jumlah_view
            FROM buku b
            LEFT JOIN view v ON b.id_buku = v.id_buku
            WHERE v.waktu_update >= ?
            GROUP BY b.id_buku, b.judul_buku, b.sampul
            ORDER BY jumlah_view DESC"; // Batasi hanya untuk 10 buku terpopuler
$stmt = $koneksi->prepare($query);
if (!$stmt) {
    die('Query prepare failed: ' . $koneksi->error);
}
$stmt->bind_param('s', $tanggal_minggu_lalu);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Populer Minggu Ini</title>
    <link rel="stylesheet" href="assets/buku.css">
</head>
<body>
    <header>
        <h1 style="
        background-color: blue;
        color: white;
        padding-bottom: 10px;">Populer:</h1>
    </header>
    <div class="bukuMain">
        <?php if ($result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <?php
                // Ambil chapter terbaru untuk setiap buku
                $sampul_path = "assets/sampul_buku/" . htmlspecialchars($row['sampul'], ENT_QUOTES, 'UTF-8'); 
                $chapter_query = "SELECT judul_chapter FROM chapters WHERE buku_id = ? ORDER BY id_chapter DESC LIMIT 1";
                $chapter_stmt = $koneksi->prepare($chapter_query);
                if (!$chapter_stmt) {
                    die('Chapter query prepare failed: ' . $koneksi->error);
                }
                $chapter_stmt->bind_param('i', $row['id_buku']);
                $chapter_stmt->execute();
                $chapter_result = $chapter_stmt->get_result();
                $chapter_terbaru = $chapter_result->fetch_assoc();
                $chapter_stmt->close();
                ?>
                <div class="buku">
                    <a href="home.php?page=chapter_select&buku_id=<?= htmlspecialchars($row['id_buku'], ENT_QUOTES, 'UTF-8'); ?>">
                        <img src="<?= $sampul_path; ?>" alt="Sampul Buku" class="sampul"/>
                        <p class="tittle"><?= htmlspecialchars($row['judul_buku'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php if ($chapter_terbaru) { ?>
                        <div class="conP">
                            <p class="ch">Chapter: <?= htmlspecialchars($chapter_terbaru['judul_chapter'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="ch"><?= formatView($row['jumlah_view']); ?> Views</p>
                        </div>
                        <?php } ?>
                    </a>
                </div>
            <?php } ?> 
        <?php } else { ?>
            <p>Tidak ada data buku yang populer minggu ini.</p>
        <?php } ?>
    </div>
</body>
</html>
