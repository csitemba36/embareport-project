<?php
require_once "db-config.php";
$db = new db_odbc('bbg_twist');
if ($db->getConnection()) {
    echo "Koneksi sukses ke bbg_twist!";
}
?>