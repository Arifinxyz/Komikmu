<?php
require_once '../config.php';
require_once '../helper/helper.php';

// Ambil ID buku dari parameter URL
$buku_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($buku_id > 0) {
    // Ambil detail buku
    $buku_query = "SELECT * FROM buku WHERE id_buku = ?";
    $buku_stmt = $koneksi->prepare($buku_query);
    $buku_stmt->bind_param("i", $buku_id);
    $buku_stmt->execute();
    $buku_result = $buku_stmt->get_result();
    $buku = $buku_result->fetch_assoc();
    $buku_stmt->close();

    // Tangani pengiriman formulir
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['update_buku'])) {
            $judul = $_POST['judul'];
            $deskripsi = $_POST['deskripsi'];

            // Update detail buku
            $update_query = "UPDATE buku SET judul_buku = ?, deskripsi = ? WHERE id_buku = ?";
            $update_stmt = $koneksi->prepare($update_query);
            $update_stmt->bind_param("ssi", $judul, $deskripsi, $buku_id);

            if ($update_stmt->execute()) {
                echo "<p class='success'>Buku berhasil diperbarui.</p>";
            } else {
                echo "<p class='error'>Gagal memperbarui buku: " . $koneksi->error . "</p>";
            }
            $update_stmt->close();
        }

        if (isset($_POST['add_chapter'])) {
            $judul_chapter = $_POST['judul_chapter'];
            $chapter_number = $_POST['chapter_number'];
            $isi_chapter = $_FILES['isi_chapter']['name'];

            // Upload file PDF
            if ($_FILES['isi_chapter']['error'] == UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['isi_chapter']['tmp_name'];
                $file_path = "../assets/isi/" . basename($isi_chapter);

                if (move_uploaded_file($tmp_name, $file_path)) {
                    // Tambah chapter ke database
                    $add_chapter_query = "INSERT INTO chapters (buku_id, judul_chapter, chapter_number, isi_chapter) VALUES (?, ?, ?, ?)";
                    $add_chapter_stmt = $koneksi->prepare($add_chapter_query);
                    $add_chapter_stmt->bind_param("isis", $buku_id, $judul_chapter, $chapter_number, $isi_chapter);

                    if ($add_chapter_stmt->execute()) {
                        echo "<p class='success'>Chapter berhasil ditambahkan.</p>";
                    } else {
                        echo "<p class='error'>Gagal menambahkan chapter: " . $koneksi->error . "</p>";
                    }
                    $add_chapter_stmt->close();
                } else {
                    echo "<p class='error'>Gagal mengunggah file.</p>";
                }
            } else {
                echo "<p class='error'>Gagal mengunggah file.</p>";
            }
        }

        if (isset($_POST['delete_chapter'])) {
            $chapter_id = $_POST['chapter_id'];

            // Hapus chapter dari database
            $delete_chapter_query = "DELETE FROM chapters WHERE id_chapter = ?";
            $delete_chapter_stmt = $koneksi->prepare($delete_chapter_query);
            $delete_chapter_stmt->bind_param("i", $chapter_id);

            if ($delete_chapter_stmt->execute()) {
                echo "<p class='success'>Chapter berhasil dihapus.</p>";
            } else {
                echo "<p class='error'>Gagal menghapus chapter: " . $koneksi->error . "</p>";
            }
            $delete_chapter_stmt->close();
        }
    }

    // Ambil daftar chapter untuk ditampilkan
    $chapters_query = "SELECT * FROM chapters WHERE buku_id = ? ORDER BY chapter_number";
    $chapters_stmt = $koneksi->prepare($chapters_query);
    $chapters_stmt->bind_param("i", $buku_id);
    $chapters_stmt->execute();
    $chapters_result = $chapters_stmt->get_result();
    $chapters_stmt->close();
} else {
    echo "ID buku tidak valid.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku</title>
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
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"], textarea, input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .button-container {
            margin: 20px 0;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        ul li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <main class="contaner-main">
        <h1>Edit Buku</h1>

        <!-- Form untuk mengedit buku -->
        <form action="buku_edit.php?id=<?= $buku_id; ?>" method="POST">
            <label for="judul">Judul Buku</label>
            <input class="coninput" type="text" name="judul" id="judul" value="<?= htmlspecialchars($buku['judul_buku'], ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="deskripsi">Deskripsi</label>
            <textarea class="coninput" name="deskripsi" id="deskripsi" required><?= htmlspecialchars($buku['deskripsi'], ENT_QUOTES, 'UTF-8'); ?></textarea>

            <button type="submit" name="update_buku">Update Buku</button>
        </form>

        <!-- Form untuk menambah chapter -->
        <form action="buku_edit.php?id=<?= $buku_id; ?>" method="POST" enctype="multipart/form-data">
            <h2>Tambah Chapter</h2>
            <label for="judul_chapter">Judul Chapter</label>
            <input class="coninput" type="text" name="judul_chapter" id="judul_chapter" required>

            <label for="chapter_number">Nomor Chapter</label>
            <input class="coninput" type="number" name="chapter_number" id="chapter_number" required>

            <label class="coninput" for="isi_chapter">Isi Chapter (PDF)</label>
            <input class="coninput" type="file" name="isi_chapter" id="isi_chapter" accept="application/pdf" required>

            <button type="submit" name="add_chapter">Tambah Chapter</button>
            <a href=""></a>
        </form>

        <!-- Daftar chapter dengan opsi hapus -->
        <h2>Daftar Chapter</h2>
        <?php if ($chapters_result->num_rows > 0) { ?>
            <ul>
                <?php while ($chapter = $chapters_result->fetch_assoc()) { ?>
                    <li>
                        Chapter <?= htmlspecialchars($chapter['chapter_number'], ENT_QUOTES, 'UTF-8'); ?>: <?= htmlspecialchars($chapter['judul_chapter'], ENT_QUOTES, 'UTF-8'); ?>
                        <form action="buku_edit.php?id=<?= $buku_id; ?>" method="POST" style="display: inline;">
                            <input class="coninput" type="hidden" name="chapter_id" value="<?= $chapter['id_chapter']; ?>">
                            <button type="submit" name="delete_chapter" onclick="return confirm('Apakah Anda yakin ingin menghapus chapter ini?');">Hapus Chapter</button>
                        </form>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <p>Tidak ada chapter yang ditemukan.</p>
        <?php } ?>

        <a href="../home.php"  class="btn btn-primary">keluar</a>
    </main>
</body>
</html>
