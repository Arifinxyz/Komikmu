<?php
require_once 'config.php'; 
require_once 'helper/helper.php';
?>

<head>
    <link rel="stylesheet" href="assets/buku.css">
</head>

<div class="bukuCon">
    <?php 
    $genre_id = isset($_GET['genre_id']) ? intval($_GET['genre_id']) : 0;

    if ($genre_id > 0) {
        $sql = "SELECT b.* FROM buku b 
                JOIN buku_genre bg ON b.id_buku = bg.id_buku 
                WHERE bg.id_genre = ?";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("i", $genre_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            $num_rows = $result->num_rows;
            if ($num_rows > 0) {
                while ($data = $result->fetch_assoc()) {
    $sampul_path = "assets/sampul_buku/" . htmlspecialchars($data['sampul'], ENT_QUOTES, 'UTF-8'); 
    $buku_id = htmlspecialchars($data['id_buku'], ENT_QUOTES, 'UTF-8');

    $result_chapter = mysqli_query($koneksi, "SELECT * FROM chapters WHERE buku_id = " . $data['id_buku'] . " ORDER BY chapter_number DESC LIMIT 1");
    $chapter_terbaru = mysqli_fetch_assoc($result_chapter);
    ?>
    <div class="buku">
        <a href="home.php?page=chapter_select&buku_id=<?= $buku_id; ?>">
            <img src="<?= $sampul_path; ?>" alt="Sampul Buku" class="sampul" />
            <p class="tittle"><?= htmlspecialchars($data['judul_buku'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php if ($chapter_terbaru) { ?>
                <p class="ch">Chapter <?= htmlspecialchars($chapter_terbaru['chapter_number'], ENT_QUOTES, 'UTF-8'); ?>: <?= htmlspecialchars($chapter_terbaru['judul_chapter'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php } else {?>
                <p class="ch">Tidak ada chapter</p>
            <?php } ?>
        </a>
    </div>
    <?php
}
            } else {
                echo "Tidak ada buku untuk genre ini.";
            }
        } else {
            echo "Terjadi kesalahan dalam mengambil data: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "ID genre tidak valid.";
    }
    ?>
</div>
