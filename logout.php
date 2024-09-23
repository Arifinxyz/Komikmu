<?php
require_once 'helper/helper.php';

session_start();
unset($_SESSION["id"]);
unset($_SESSION["role"]);
unset($_SESSION["username"]);
header("Location: " . BASE_URL);
exit();
?>