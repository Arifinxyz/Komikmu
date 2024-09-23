
<?php
require_once 'config.php';
require_once 'helper/helper.php';
require_once 'helper/viewformat.php';

// Hitung tanggal satu minggu yang lalu
$tanggal_minggu_lalu = date('Y-m-d H:i:s', strtotime('-1 week'));
$genre_id = isset($_GET['genre_id']) ? intval($_GET['genre_id']) : 0;


// Query untuk mendapatkan buku diurutkan dari yang terbaru ke yang terlama berdasarkan waktu update
$sql = "SELECT * FROM buku ORDER BY waktu_update DESC";
$query = mysqli_query($koneksi, $sql);

// Query untuk mendapatkan 3 buku yang paling banyak dilihat dalam 1 minggu terakhir
$query_populer = "SELECT b.id_buku, b.judul_buku, b.sampul, b.deskripsi, COALESCE(SUM(v.jumlah_view), 0) AS jumlah_view
            FROM buku b
            LEFT JOIN view v ON b.id_buku = v.id_buku
            WHERE v.waktu_update >= ?
            GROUP BY b.id_buku, b.judul_buku, b.sampul
            ORDER BY jumlah_view DESC
            LIMIT 3";

$stmt_populer = $koneksi->prepare($query_populer);

// Periksa apakah query berhasil diprepare
if (!$stmt_populer) {
    die("Gagal mempersiapkan query: " . $koneksi->error);
}

$stmt_populer->bind_param('s', $tanggal_minggu_lalu);
$stmt_populer->execute();
$result_populer = $stmt_populer->get_result();
$populer = $result_populer->fetch_all(MYSQLI_ASSOC);
$stmt_populer->close();


// Container buku_populer
$query_buku_populer_con = "SELECT b.id_buku, b.judul_buku, b.sampul, COALESCE(SUM(v.jumlah_view), 0) AS jumlah_view
            FROM buku b
            LEFT JOIN view v ON b.id_buku = v.id_buku
            WHERE v.waktu_update >= ?
            GROUP BY b.id_buku, b.judul_buku, b.sampul
            ORDER BY jumlah_view DESC";
$stmt_buku_populer_con = $koneksi->prepare($query_buku_populer_con);
$stmt_buku_populer_con->bind_param('s', $tanggal_minggu_lalu);
$stmt_buku_populer_con->execute();
$result_buku_populer_con = $stmt_buku_populer_con->get_result();
$buku_populer_con = $result_buku_populer_con->fetch_all(MYSQLI_ASSOC);
$stmt_buku_populer_con->close();

// Query untuk mendapatkan semua genre
$query_genre_list = "SELECT * FROM genre LIMIT 10";
$result_genre_list = mysqli_query($koneksi, $query_genre_list)

?>
<!-- html -->
<link rel="stylesheet" href="assets/buku.css">
<link rel="stylesheet" href="assets/profile.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="tigapopuler">
  <div class="slider-container">
    <?php foreach ($populer as $buku): ?>
    <div class="slide">
      <a href="home.php?page=chapter_select&buku_id=<?= htmlspecialchars($buku['id_buku'], ENT_QUOTES, 'UTF-8'); ?>">
      <img src="assets/sampul_buku/<?= htmlspecialchars($buku['sampul'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($buku['judul_buku'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="gradasi"></div>
        <div class="text-overlay">
          <h3 class="judul-slide"><?= htmlspecialchars($buku['judul_buku'], ENT_QUOTES, 'UTF-8') ?></h3>
          <p class="p"><?= htmlspecialchars($buku['deskripsi'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>     
     </a>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Buku Terbaru -->
<div class="wadahBukuCon">
    <h4>Terbaru</h4>
    <div class="bukuCon">
    <?php 
    if ($query) {
        $num_rows = mysqli_num_rows($query);
        if ($num_rows > 0) {
            while ($data = mysqli_fetch_assoc($query)) {
                // Query untuk mendapatkan chapter terbaru
                $result_chapter = mysqli_query($koneksi, "SELECT * FROM chapters WHERE buku_id = " . $data['id_buku'] . " ORDER BY chapter_number DESC LIMIT 1");
                $chapter_terbaru = mysqli_fetch_assoc($result_chapter);
                ?>
                <div class="buku">
                    <a href="home.php?page=chapter_select&buku_id=<?= htmlspecialchars($data['id_buku'], ENT_QUOTES, 'UTF-8'); ?>">
                    <img src="assets/sampul_buku/<?= htmlspecialchars($data['sampul'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($data['judul_buku'], ENT_QUOTES, 'UTF-8'); ?>" class="sampul">
                    <p class="tittle"><?= htmlspecialchars($data['judul_buku'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php if ($chapter_terbaru) { ?>
                        <p class="ch">Chapter <?= htmlspecialchars($chapter_terbaru['judul_chapter'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php } else { ?>
                        <p class="ch">Tidak ada chapter terbaru.</p>
                    <?php } ?>
                    </a>
                </div>
                <?php
            }
        } else {
            echo "Tidak ada data buku.";
        }
    } else {
        echo "Terjadi kesalahan dalam mengambil data: " . mysqli_error($koneksi);
    }
    ?>
    </div>
</div>

<!-- Buku Populer -->
<div class="wadahBukuCon">
    <h4>Populer</h4>
    <div class="bukuCon">
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
</div>
<div class="wadahBukuCon">
    <h4>Genre:</h4>
</div>
<?php
while ($genre = mysqli_fetch_assoc($result_genre_list)): ?>
<div class="wadahBukuCon">
<a href="home.php?page=genre&genre_id=<?= urlencode($genre['id_genre']); ?>">

    <h4><?= htmlspecialchars($genre['nama_genre'], ENT_QUOTES, 'UTF-8' );?></h4> 
    </a>

    <div class="bukuCon">
        <?php 
        $query_buku_genre = "SELECT b.id_buku, judul_buku, sampul FROM buku b JOIN buku_genre bg ON b.id_buku = bg.id_buku WHERE bg.id_genre = ?";
        $stmt_buku_genre = $koneksi->prepare($query_buku_genre);
        $stmt_buku_genre->bind_param('i', $genre['id_genre']);
        $stmt_buku_genre->execute();
        $result_buku_genre = $stmt_buku_genre->get_result();

        if ($result_buku_genre->num_rows > 0) {
            while ($data_buku_genre = $result_buku_genre->fetch_assoc()) {
               $result_chapter_genre = mysqli_query($koneksi, "SELECT * FROM chapters WHERE buku_id = " . $data_buku_genre['id_buku'] . " ORDER BY chapter_number DESC LIMIT 1 ");
               $chapter_terbaru_genre = mysqli_fetch_assoc($result_chapter_genre);
        ?>
        <div class="buku">
            <a href="home.php?page=chapter&buku_id=<?= htmlspecialchars($data_buku_genre['id_buku'], ENT_QUOTES, 'UTF-8');  ?>">
                <img src="assets/sampul_buku/<?= htmlspecialchars($data_buku_genre['sampul'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($data_buku_genre['judul_buku'], ENT_QUOTES, 'UTF-8'); ?>" class="sampul">
                <p class="tittle"><?= htmlspecialchars($data_buku_genre['judul_buku'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php if ($chapter_terbaru_genre) {?>
                    <p class="ch"><?= htmlspecialchars($chapter_terbaru_genre['judul_chapter'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php } else { ?>
                    <p class="ch">Tidak ada chapter terbaru.</p>
                <?php } ?>
            </a>
        </div>
        <?php }}else {
            echo "<p>Tidak Ada Buku di Genre Ini</p>";
        } ?>

    </div>
</div>
</div>
<?php endwhile; ?> 
<script>
const sliderContainer = document.querySelector('.slider-container');
const slides = document.querySelectorAll('.slide');
let currentSlide = 0;

function nextSlide() {
    slides[currentSlide].classList.remove('active');
    currentSlide = (currentSlide + 1) % slides.length;
    slides[currentSlide].classList.add('active');
    sliderContainer.style.transform = `translateX(-${currentSlide * 100}vw)`; // Menggeser sliderContainer
}

setInterval(nextSlide, 5000); // Ganti slide setiap 5 detik

// initialize the first slide
slides[0].classList.add('active');
</script>
