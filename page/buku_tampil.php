<?php
require_once 'config.php';
require_once 'helper/helper.php';

// Pastikan parameter file ada dan valid
if (isset($_GET['file'])) {
    $file = basename($_GET['file']); // Mengambil nama file dari parameter URL dan menghindari path traversal
    $file_path = "assets/isi/" . $file; // Path file PDF

    // Cek apakah file ada dan valid
    if (file_exists($file_path) && strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) == 'pdf') {
        $pdf_url = $file_path; // URL PDF untuk diakses oleh PDF.js
    } else {
        echo "File tidak ditemukan atau format file tidak valid.";
        exit();
    }
} else {
    echo "Parameter file tidak ditemukan.";
    exit();
}

// Ambil judul buku dari parameter URL atau dari database jika diperlukan
$judul_buku = isset($_GET['judul_buku']) ? htmlspecialchars($_GET['judul_buku'], ENT_QUOTES, 'UTF-8') : 'Judul Buku';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <link rel="stylesheet" href="assets/buku.css">
    <style>
        .pdf-container {
            max-width: 100%;
            margin: 0 auto;
            overflow: hidden;
        }
        .pdf-page {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            overflow: hidden;
        }
        canvas {
            display: block;
            margin: 0 auto;
        }
        .pdfIsi {
            width: 65%;
            height: auto;
        }
        .mainCon {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="mainCon">
        <h1><?= $judul_buku; ?></h1>
        <div class="pdfIsi" id="pdf-container"></div>
    </div>
    <script>
        const url = "<?php echo $pdf_url; ?>";
        const pdfContainer = document.getElementById('pdf-container');
        const renderedPages = new Map(); // Cache untuk halaman yang sudah dirender

        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        pdfjsLib.getDocument(url).promise.then(function(pdf) {
            const totalPages = pdf.numPages;
            let currentPage = 1; // Mulai dengan halaman pertama

            function renderPage(pageNum) {
                // Jika halaman sudah ada di cache dan elemen ada di dalam DOM, cukup tampilkan
                if (renderedPages.has(pageNum)) {
                    const cachedCanvas = renderedPages.get(pageNum);
                    if (!document.body.contains(cachedCanvas)) {
                        pdfContainer.appendChild(cachedCanvas);
                    }
                    cachedCanvas.style.display = 'block'; // Tampilkan kembali dari cache
                    return;
                }

                // Render halaman baru
                pdf.getPage(pageNum).then(function(page) {
                    const viewport = page.getViewport({ scale: 1 });
                    const scale = Math.min(pdfContainer.clientWidth / viewport.width, 1);
                    const scaledViewport = page.getViewport({ scale: scale });

                    const canvas = document.createElement('canvas');
                    canvas.className = 'pdf-page';
                    const context = canvas.getContext('2d');
                    canvas.height = scaledViewport.height;
                    canvas.width = scaledViewport.width;

                    pdfContainer.appendChild(canvas);

                    const renderContext = {
                        canvasContext: context,
                        viewport: scaledViewport
                    };

                    page.render(renderContext).promise.then(function() {
                        renderedPages.set(pageNum, canvas); // Simpan elemen ke dalam cache
                        if (renderedPages.size > 15) { // Hapus halaman jika cache lebih dari 15 halaman
                            const firstKey = renderedPages.keys().next().value;
                            const firstCanvas = renderedPages.get(firstKey);
                            firstCanvas.style.display = 'none'; // Sembunyikan elemen
                            renderedPages.delete(firstKey); // Hapus dari cache
                        }
                    });
                });
            }

            // Render halaman pertama dan gunakan lazy loading
            renderPage(currentPage);

            // Lazy loading untuk halaman berikutnya
            window.addEventListener('scroll', function() {
                if (currentPage < totalPages && window.innerHeight + window.scrollY >= document.body.scrollHeight - 100) {
                    currentPage++;
                    renderPage(currentPage);
                }
            });
        });
    </script>
</body>
</html>
