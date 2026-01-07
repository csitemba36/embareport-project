<?php
require_once('../config/connect_db.php');

$user_id = $_COOKIE['fullname'] ?? ''; // ganti sesuai sumber user id

if (!$user_id) {
    echo json_encode(['error' => 'User tidak ditemukan']);
    exit;
}

$sql = "SELECT COUNT(*) as total FROM app_rating WHERE user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();
$mysqli->close();

echo json_encode(['hasRated' => $total > 0]);



?>
