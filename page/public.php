<?php
require_once 'config.php';
require_once 'helper/helper.php';

if ($is_logged_in) {

// Pastikan user sudah login dan bukan admin
  if ($_SESSION["role"] == 'admin') {
      header("Location: " . BASE_URL);
      exit();
  }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home & Profile</title>
    <link rel="stylesheet" href="assets/buku.css">
    <link rel="stylesheet" href="assets/profile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.title = "Buku"; // Mengatur judul tab menggunakan JavaScript
    </script>
</head>
<body>
  <?php include_once 'buku/buku_public.php' ?>
</script>
</body>
</html>
