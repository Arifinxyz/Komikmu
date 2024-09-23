<?php
require 'config.php';
require_once 'helper/helper.php';

$process = isset($_GET["process"]) ? ($_GET["process"]) : false;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/login.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
    <main class="conLogin">
        <h2>Login</h2>
        <!-- Menampilkan pesan error jika ada -->
         <?php if($process == 'false'):?>
         <p style="color: red;">Username atau Password Salah</p>
         <?php endif;?>
        <form action="process/login_process.php" method="POST">
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        <input type="text" name="username" id="username" placeholder="username">
        <input type="password" name="password" id="password" placeholder="password">
            <button type="submit" name="login">Login</button>
        </form>
        <p>Belum punya akun? <a href="register.php">register</a></p>
    </main>
</body>
</html>
