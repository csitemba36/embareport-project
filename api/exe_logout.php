<?php
session_start();

// Hapus semua session
session_unset();
session_destroy();

// Hapus semua cookie yang digunakan
setcookie("id", "", time() - 3600, "/");
setcookie("username", "", time() - 3600, "/");
setcookie("email", "", time() - 3600, "/");
setcookie("fullname", "", time() - 3600, "/");
setcookie("aksesmerk", "", time() - 3600, "/");
setcookie("userrolecode", "", time() - 3600, "/");
setcookie("aksesgudang", "", time() - 3600, "/");


echo json_encode(["status" => "success"]);
exit;
?>
