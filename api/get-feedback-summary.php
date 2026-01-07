<?php
// Memanggil file koneksi
require_once('../config/connect_db.php'); // koneksi MySQL

$sql = "SELECT 
          AVG(rating) AS rata_rating
        FROM app_rating";
$result = $mysqli->query($sql);
$data = $result->fetch_assoc();

echo json_encode([
  'rata_rating' => round($data['rata_rating'], 2)
]);
?>