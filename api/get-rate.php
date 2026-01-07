<?php
require_once('../config/connect_db.php');

$sql = "SELECT user_id, rating, comment, created_at FROM app_rating ORDER BY created_at DESC";
$result = $mysqli->query($sql);

$ratings = [];

while ($row = $result->fetch_assoc()) {
    $ratings[] = $row;
}

$mysqli->close();

header('Content-Type: application/json');
echo json_encode($ratings);


?>
