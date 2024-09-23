<?php
require_once '../config.php';
require_once '../helper/helper.php';

session_start();

// Cek apakah user sudah login
if (empty($_SESSION["role"])) {
    header("Location: " . BASE_URL);
    exit();
}

// Proses saat formulir disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $judul_buku = trim($_POST["judul_buku"]);
    $deskripsi = trim($_POST["deskripsi"]);
    $sampul_unique_name = '';

    // Proses upload file sampul
    if (isset($_FILES["sampul"]) && $_FILES["sampul"]["error"] == UPLOAD_ERR_OK) {
        $sampul_tmp = $_FILES["sampul"]["tmp_name"];
        $sampul_name = basename($_FILES["sampul"]["name"]);
        $sampul_ext = strtolower(pathinfo($sampul_name, PATHINFO_EXTENSION));
        $allowed_sampul_ext = ["jpg", "jpeg", "png"];

        if (in_array($sampul_ext, $allowed_sampul_ext)) {
            $sampul_unique_name = 'sampul_' . uniqid() . '.' . $sampul_ext;
            $sampul_path = "../assets/sampul_buku/" . $sampul_unique_name;
            if (!move_uploaded_file($sampul_tmp, $sampul_path)) {
                echo "<p style='color: red;'>Gagal mengunggah file sampul.</p>";
                $sampul_unique_name = '';
            }
        } else {
            echo "<p style='color: red;'>Format file sampul tidak valid. Harus berupa JPG, JPEG, atau PNG.</p>";
            $sampul_unique_name = '';
        }
    }

    // Validasi input
    if (empty($judul_buku) || empty($deskripsi)) {
        echo "<p style='color: red;'>Judul Buku dan Deskripsi tidak boleh kosong.</p>";
    } else {
        // Insert buku ke database
        $query_buku = "INSERT INTO buku (sampul, judul_buku, deskripsi) VALUES (?, ?, ?)";
        $stmt_buku = $koneksi->prepare($query_buku);
        $stmt_buku->bind_param("sss", $sampul_unique_name, $judul_buku, $deskripsi);

        if ($stmt_buku->execute()) {
            $buku_id = $stmt_buku->insert_id;

            // Insert genre
            if (!empty($_POST["genre"])) {
                $query_buku_genre = "INSERT INTO buku_genre (id_buku, id_genre) VALUES (?, ?)";
                $stmt_buku_genre = $koneksi->prepare($query_buku_genre);

                foreach ($_POST["genre"] as $genre_id) {
                    $stmt_buku_genre->bind_param("ii", $buku_id, $genre_id);
                    $stmt_buku_genre->execute();
                }
            }

            // Insert pengarang
            if (!empty($_POST["pengarang"])) {
                $query_buku_pengarang = "INSERT INTO buku_pengarang (id_buku, id_pengarang) VALUES (?, ?)";
                $stmt_buku_pengarang = $koneksi->prepare($query_buku_pengarang);

                foreach ($_POST["pengarang"] as $pengarang_id) {
                    $stmt_buku_pengarang->bind_param("ii", $buku_id, $pengarang_id);
                    $stmt_buku_pengarang->execute();
                }
            }

            // Insert chapter
            if (!empty($_POST["chapter_number"]) && !empty($_POST["judul_chapter"]) && isset($_FILES["chapter_pdf"]) && $_FILES["chapter_pdf"]["error"] == UPLOAD_ERR_OK) {
                $chapter_pdf_tmp = $_FILES["chapter_pdf"]["tmp_name"];
                $chapter_pdf_name = basename($_FILES["chapter_pdf"]["name"]);
                $chapter_pdf_ext = strtolower(pathinfo($chapter_pdf_name, PATHINFO_EXTENSION));
                $allowed_chapter_pdf_ext = ["pdf"];

                if (in_array($chapter_pdf_ext, $allowed_chapter_pdf_ext)) {
                    $chapter_pdf_unique_name = 'chapter_' . uniqid() . '.' . $chapter_pdf_ext;
                    $chapter_pdf_path = "../assets/isi/" . $chapter_pdf_unique_name;

                    if (move_uploaded_file($chapter_pdf_tmp, $chapter_pdf_path)) {
                        $query_chapter = "INSERT INTO chapters (buku_id, chapter_number, judul_chapter, isi_chapter) VALUES (?, ?, ?, ?)";
                        $stmt_chapter = $koneksi->prepare($query_chapter);
if (!$stmt_chapter) {
    echo $koneksi->error;
    exit;
}
                        $stmt_chapter->bind_param("iiss", $buku_id, $_POST["chapter_number"], $_POST["judul_chapter"], $chapter_pdf_unique_name);
                        $stmt_chapter->execute();
                    } else {
                        echo "<p style='color: red;'>Gagal mengunggah file chapter.</p>";
                    }
                } else {
                    echo "<p style='color: red;'>Format file chapter tidak valid. Harus berupa PDF.</p>";
                }
            }

            echo "<p style='color: green;'>Buku berhasil ditambahkan.</p>";
        } else {
            echo "<p style='color: red;'>Gagal menambahkan buku.</p>";
        }
    }
}

if (isset($_POST["pengarang_tambah"])) {
    $query_pengarang = "INSERT INTO pengarang (pengarang) VALUES (?)";
    $stmt_pengarang = $koneksi->prepare($query_pengarang);
    $stmt_pengarang->bind_param("s", $_POST["pengarang_tambah"]);
    if ($stmt_pengarang->execute()) {
        echo "<p style='color: green;'>Pengarang berhasil ditambahkan.</p>";
        // Refresh halaman untuk memperbarui daftar pengarang
        echo "<script>window.location.href = window.location.href;</script>";
    } else {
        echo "<p style='color: red;'>Gagal menambahkan pengarang.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku dan Chapter</title>
    <link rel="stylesheet" href="../assets/buku_CRUD.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Pastikan jQuery dimuat -->
    <style>
        .genre-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* Jarak antar item genre */
            margin-bottom: 15px; 
        }

        .genre-item {
            display: flex;
            align-items: center; /* Vertikal sejajar */
        }

        .genre-item input[type="checkbox"] {
            margin-right: 5px; /* Jarak antara checkbox dan nama genre */
        }
        .pengarang {
            width: 100%;
        }

        .pengarang_select {
            width: 100%;
        }

    </style>
</head>
<body>
<div class="container_main">
    <main>
        <form action="buku_tambah.php" method="POST" enctype="multipart/form-data">
            <h1>Tambah Buku dan Chapter</h1>
            <!-- Form Buku -->
            <div class="coninput">
                <label for="sampul">Sampul</label>
                <input type="file" name="sampul" id="sampul" accept="image/jpeg, image/png" class="input_file">
            </div>
            <div class="coninput">
                <label for="judul_buku" >Judul Buku</label>
                <input type="text" name="judul_buku" id="judul_buku" class="input" required>
            </div>
            <div class="coninput">
                <label for="deskripsi" >Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" class="input_desk" required></textarea>
            </div>
            <!-- Form Chapter -->
            <div class="coninput">
                <label for="chapter_number" >Nomor Chapter</label>
                <input type="number" name="chapter_number" id="chapter_number" class="input">
            </div>
            <div class="coninput">
            <label for="judul_chapter" >Judul Chapter</label>
            <input type="text" name="judul_chapter" id="judul_chapter" class="input">
            </div>
            <div class="coninput">
            <label for="chapter_pdf">File PDF Chapter</label>
            <input type="file" name="chapter_pdf" id="chapter_pdf" accept="application/pdf" class="input_file">
            </div>

            <!-- Form Genre -->
            <div class="coninput">
            <label>Genre:</label>
            <div class="genre-list">
                <!-- Daftar genre, diambil dari database -->
                <?php
                $query = "SELECT * FROM genre";
                $result = mysqli_query($koneksi, $query);
                while ($genre = mysqli_fetch_assoc($result)) {
                    echo '<div class="genre-item"><input type="checkbox" name="genre[]" value="' . $genre['id_genre'] . '"> ' . htmlspecialchars($genre['nama_genre'], ENT_QUOTES, 'UTF-8') . '</div>';
                }
                ?>
            </div>
            </div>
    
            <!-- Pencarian dan pemilihan pengarang -->
            <div class="coninput">
            <label for="pengarang">Pengarang/Author:</label>
            <div class="pengarang">
                <input type="text" id="search-pengarang" placeholder="Cari pengarang..." class="input">
                <select name="pengarang[]" id="pengarang" class="pengarang_select" multiple>
                    <?php
                    $query_pengarang = "SELECT * FROM pengarang";
                    $result_pengarang = mysqli_query($koneksi, $query_pengarang);
                    while ($pengarang = mysqli_fetch_assoc($result_pengarang)) {
                        echo "<option value='" . htmlspecialchars($pengarang['id_pengarang'], ENT_QUOTES, 'UTF-8') . "'>" . $pengarang['pengarang'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            </div>

            <button type="submit" name="submit" class="butt_simpan">Simpan</button>
        </form>
        <form action="" method="post">
        <div class="coninput">
            <label for="pengarang_tambah">Nama Pengarang Tidak Ada di Lis?</label>
            <input type="text" name="pengarang_tambah" id="pengarang_tambah" class="input">
        </div>
            <button type="submit">Tambah Pengarang</button>
        </form>
    </main>
    </div>
    <script>
        $(document).ready(function() {
            // Fungsi untuk filter pengarang berdasarkan input pencarian
            $('#search-pengarang').on('keyup', function() {
                var searchValue = $(this).val().toLowerCase();
                $('#pengarang option').each(function() {
                    var optionText = $(this).text().toLowerCase();
                    if (optionText.indexOf(searchValue) === -1) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });
        });
    </script>
</body>
</html>