<?php
require_once 'config.php';
require_once 'helper/helper.php';
require_once 'helper/viewformat.php';

// Pastikan buku_id ada dan valid
$buku_id = isset($_GET['buku_id']) ? intval($_GET['buku_id']) : 0;

$check_query = "SELECT id_view FROM view WHERE id_buku = ? AND DATE(waktu_update) = CURDATE()";
$check_stmt = $koneksi->prepare($check_query);
$check_stmt->bind_param('i', $buku_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$view_count = 1; // Jumlah view per klik

$tanggal_minggu_lalu = date('Y-m-d', strtotime('-7 days'));

if ($check_result->num_rows > 0) {
    // Update jumlah view
    $update_query = "UPDATE view SET jumlah_view = jumlah_view + ? WHERE id_buku = ? AND DATE(waktu_update) = CURDATE()";
    $update_stmt = $koneksi->prepare($update_query);
    $update_stmt->bind_param('ii', $view_count, $buku_id);
    $update_stmt->execute();
    $update_stmt->close();
} else {
    // Insert new entry
    $insert_query = "INSERT INTO view (id_buku, jumlah_view, waktu_update) VALUES (?, ?, NOW())";
    $insert_stmt = $koneksi->prepare($insert_query);
    $insert_stmt->bind_param('ii', $buku_id, $view_count);
    $insert_stmt->execute();
    $insert_stmt->close();
}

$check_stmt->close();
 
if ($buku_id > 0) {

    // Ambil informasi buku
    $query_buku = "SELECT judul_buku, sampul, deskripsi FROM buku WHERE id_buku = ?";
    $stmt_buku = $koneksi->prepare($query_buku);
    if (!$stmt_buku) {
        die('Query prepare failed: ' . $koneksi->error);
    }
    $stmt_buku->bind_param("i", $buku_id);
    $stmt_buku->execute();
    $result_buku = $stmt_buku->get_result();
    if ($result_buku->num_rows > 0) {
        $buku = $result_buku->fetch_assoc();
    } else {
        echo "Buku tidak ditemukan.";
        exit();
    }

    // Ambil chapter-chapter yang terkait dengan buku
    $query = "SELECT * FROM chapters WHERE buku_id = ? ORDER BY chapter_number";
    $stmt = $koneksi->prepare($query);
    if (!$stmt) {
        die('Query prepare failed: ' . $koneksi->error);
    }
    $stmt->bind_param("i", $buku_id);
    $stmt->execute();
    $result = $stmt->get_result();

    //genre
    $query_genre_buku = "SELECT g.id_genre, g.nama_genre FROM genre g
    JOIN buku_genre bg ON g.id_genre = bg.id_genre WHERE bg.id_buku = ?";
    $stmt_genre_buku = $koneksi->prepare($query_genre_buku);
    if (!$stmt_genre_buku) {
        die('Query prepare failed: ' . $koneksi->error);
    }
    $stmt_genre_buku->bind_param("i", $buku_id);
    $stmt_genre_buku->execute();
    $result_genre_buku = $stmt_genre_buku->get_result();
    $genres = [];
    while ($genre = $result_genre_buku->fetch_assoc()) {
        $genres[] = $genre;
    }

    //pengarang
    $query_pengarang = "SELECT p.id_pengarang, p.pengarang FROM pengarang p 
    JOIN buku_pengarang bp ON p.id_pengarang = bp.id_pengarang WHERE bp.id_buku = ?";
    $stmt_pengarang = $koneksi->prepare($query_pengarang);
    if (!$stmt_pengarang) {
        die('Query prepare failed: ' . $koneksi->error);
    }
    $stmt_pengarang->bind_param("i", $buku_id);
    $stmt_pengarang->execute();
    $result_pengarang = $stmt_pengarang->get_result();
    $pengarangG = [];
    while ($pengarang = $result_pengarang->fetch_assoc()) {
        $pengarangG[] = $pengarang;
    }

    $query_buku_populer_con = "SELECT b.id_buku, b.judul_buku, b.sampul, COALESCE(SUM(v.jumlah_view), 0) AS jumlah_view
            FROM buku b
            LEFT JOIN view v ON b.id_buku = v.id_buku
            WHERE v.waktu_update >= ?
            GROUP BY b.id_buku, b.judul_buku, b.sampul
            ORDER BY jumlah_view DESC LIMIT 30";
    $stmt_buku_populer_con = $koneksi->prepare($query_buku_populer_con);
    $stmt_buku_populer_con->bind_param('s', $tanggal_minggu_lalu);
    $stmt_buku_populer_con->execute();
    $result_buku_populer_con = $stmt_buku_populer_con->get_result();
    $buku_populer_con = $result_buku_populer_con->fetch_all(MYSQLI_ASSOC);
    $stmt_buku_populer_con->close();

}else {
    header ("Location :" . BASE_URL);
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($buku['judul_buku'], ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="assets/chapter.css">
    <link rel="stylesheet" href="assets/buku.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="conMain">
        <main class="main">
        
            <div class="book-info">
                <img src="assets/sampul_buku/<?= htmlspecialchars($buku['sampul'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($buku['judul_buku'], ENT_QUOTES, 'UTF-8'); ?>" class="book-cover">
                <div class="book-description">
                    <h3 class="judul"><?= htmlspecialchars($buku['judul_buku'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?= htmlspecialchars($buku['deskripsi'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
            
            <div class="book-genre">
                <h3>Genre:</h3>
                <?php foreach ($genres as $genre) { ?>
                    <a href="home.php?page=genre&genre_id=<?= urlencode($genre['id_genre']); ?>"> <?= htmlspecialchars($genre['nama_genre'], ENT_QUOTES, 'UTF-8'); ?>,</a>
                <?php } ?>
            </div>

            <div class="book-author">
                <h3>Pengarang:</h3>
                <?php foreach ($pengarangG as $pengarang) { ?>
                    <a href="home.php?page=pengarang&id_pengarang=<?= urlencode($pengarang['id_pengarang']); ?>"> <?= htmlspecialchars($pengarang['pengarang'], ENT_QUOTES, 'UTF-8'); ?>,</a>
                <?php } ?>
            </div>

            

        </main>
        <man class="main">
        <h3 class="title">Daftar Chapter:</h3>
            <div class="chapter-list">
                    <?php while ($chapter = $result->fetch_assoc()) { ?>
                            <a href="home.php?page=buku_tampil&file=<?= urlencode($chapter['isi_chapter']); ?>" class="chapter-card">
                                    <h5>Chapter <?= htmlspecialchars($chapter['judul_chapter'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            </a>
                    <?php } ?>
            </div>
            <div class="button-container">
            <?php if($is_logged_in) {
                if ($_SESSION["role"] != 'admin') { ?>
            <?php } else { ?>
            <div class="button-container">
                <a href="buku/buku_edit.php?id=<?= $buku_id; ?>" class="edit-button">Edit Buku</a>
                <a href="buku/buku_hapus.php?id=<?= $buku_id; ?>" class="delete-button" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">Hapus Buku</a>
            <?php }} ?>
            </div>
        </main>
        <div class="conMain">
        <main class="main">
    <div class="bukuMain">
        <?php 
    if ($buku_populer_con) {
        foreach ($buku_populer_con as $data_buku_populer) {
            // Query untuk mendapatkan chapter terbaru
            $result_chapter_buku_populer = mysqli_query($koneksi, "SELECT * FROM chapters WHERE buku_id = " . $data_buku_populer['id_buku'] . " ORDER BY chapter_number DESC LIMIT 1");
            $chapter_terbaru_buku_populer = mysqli_fetch_assoc($result_chapter_buku_populer);
            ?>
            <div class="buku">
                <a href="home.php?page=chapter_select&buku_id=<?= htmlspecialchars($data_buku_populer['id_buku'], ENT_QUOTES, 'UTF-8'); ?>">
                <img src="assets/sampul_buku/<?= htmlspecialchars($data_buku_populer['sampul'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($data_buku_populer['judul_buku'], ENT_QUOTES, 'UTF-8'); ?>" class="sampul">
                <p class="tittle"><?= htmlspecialchars($data_buku_populer['judul_buku'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php if ($chapter_terbaru_buku_populer) { ?>
                    <p class="ch">Chapter <?= htmlspecialchars($chapter_terbaru_buku_populer['judul_chapter'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="ch"><?= formatView($data_buku_populer['jumlah_view']); ?> Views</p>
                <?php } else { ?>
                    <p class="ch">Tidak ada chapter terbaru.</p>
                    <p class="ch">View tidak Valid</p>
                <?php } ?>
                </a>
            </div>
            <?php
        }
    } else {
        echo "Tidak ada data buku.";
    }
    ?>
    </div>
        </main>
</div>
    </div>
</body>
</html>
