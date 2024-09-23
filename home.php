<?php
require_once 'config.php';
require_once 'helper/helper.php';


session_start();

$buku = isset($_GET["buku"]) ? $_GET["buku"] : false;
$page = isset($_GET["page"]) ? $_GET["page"] : false;
$is_logged_in = isset($_SESSION['username']);

if (!$page) {
  header("Location: " . BASE_URL . "home.php?page=public");
  exit();
}

if ($is_logged_in) {
  // Kode untuk pengguna yang sudah login
} else {
  session_write_close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/buku.css">
  <link rel="stylesheet" href="assets/profile.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDZJVnvxILIeo4TYaWg2OXUxDxKCpnzVHJXYwIpsumTYdT67/hHBaAQGhKpnJvpOBYEIW/TdXzk==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://kit.fontawesome.com/b4b48d4e30.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body class="body">
<nav class="navbar bg-body-tertiary">
  <div class="container-fluid">
    <div class="wadahNav">
      <a class="navbar-brand" href="#"><img src="assets/img/logoWeb.png" alt="Logo" style="height: 40px;"></a>
      <a class="navbar-brand" href="index.php">Home</a>
      <a class="navbar-brand" href="home.php?page=buku_populer">Populer</a>
      <a class="navbar-brand" href="terbaru.php">Terbaru</a>
    </div>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header">
        <?php if ($is_logged_in): ?>
          <a href="profile.php" class="navbar-toggler">
            <h5 class="offcanvas-title" id="offcanvasNavbarLabel">
              <img src="assets/img/imgProfile/<?php echo htmlspecialchars($_SESSION['profilPic'] ?? 'guest.jpeg', ENT_QUOTES, 'UTF-8'); ?>" alt="Gambar Profil" class="profilPic2">
              <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>
            </h5>
          </a>
        <?php else: ?>
          <a href="login.php" class="navbar-toggler">
            <h5>
              <img src="assets/img/imgProfile/guest.jpeg" alt="Guest" class="profilPic2">
              Guest
            </h5>
          </a>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="populer.php">Populer</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="terbaru.php">Terbaru</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Genre
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Action</a></li>
              <li><a class="dropdown-item" href="#">Another action</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#">Something else here</a></li>
            </ul>
          </li>
        </ul>
        <form class="d-flex mt-3" role="search">
          <input class="form-control me-2" type="search" placeholder="Search.." aria-label="Search">
          <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
        <?php if (!$is_logged_in): ?>
          <form action="login.php" method="post" class="buttLog">
            <button type="submit" class="btn btn-primary">Login</button>
          </form>
        <?php else: ?>
          <form action="logout.php" method="post" class="buttLog">
            <button type="submit" class="btn btn-primary">Logout</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<main class="home">
  <?php
  $filename = "page/$page.php";

  if ($is_logged_in) {
    if (file_exists($filename)) {
      include_once($filename);
    } else {
      echo "404";
    }
  } else if(file_exists($filename)) {
      include_once($filename);
  } else {
      echo "404";
  }
  ?>
<footer>
  <div class="footer-container">
    <div class="footer-container2">
    <div class="footer-section">
      <h4>KomikMu</h4>
      <p class="footer_desk">Dibuat oleh Arifin Abdullah</p>
    </div>
    <div class="footer-section">
      <div class="wadahSosmed">
        <i class="fa-solid fa-phone"><a href="" class="sosmed">  +62-0000-00-00</a></i>
        <i class="fa-brands fa-github" ><a href="#" class="sosmed">  Arifinxyz</a></i>
        <i class="fa fa-envelope"><a href="#" class="sosmed">  emainrahasi@gmail.com</a></i>
      </div>
    </div>
    <div class="footer-section">
      <img src="assets/img/logoWebPutih.png" alt="" style="width: 150px;">
    </div>
    </div>
    <div class="footer-container3">
    <div class="footer-section">
    <a href="" class="logoSosmed"><i class="fa-brands fa-github"></i></a>
    <a href="" class="logoSosmed"><i class="fa-brands fa-whatsapp"></i></a>
    <a href="" class="logoSosmed"><i class="fa-regular fa-envelope"></i></a>
    </div>
    <div class="copyrightCon">
      <p class="copyright">&copy; 2023 All Rights Reserved.</p>
    </div>
  </div>
</div>
</footer>
</main>
</body>
</html>
