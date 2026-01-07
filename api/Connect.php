<?php
// Koneksi ke database MySQL
$mysqli = new mysqli("localhost", "root", "", "db_retail_unity");
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}