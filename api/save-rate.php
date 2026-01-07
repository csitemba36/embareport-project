<?php
require_once('../config/connect_db.php');

$rating = $_POST['rating'] ?? 0;
$comment = $_POST['comment'] ?? '';
$user_id = $_COOKIE['fullname']; // Ini harus user_id yang unik, bukan fullname ya

$sql = "INSERT INTO app_rating (user_id, rating, comment, created_at) 
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            rating = VALUES(rating),
            comment = VALUES(comment),
            created_at = NOW()";

$stmt = $mysqli->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $mysqli->error);
}

$stmt->bind_param("sis", $user_id, $rating, $comment);

if ($stmt->execute()) {
    echo "Rating berhasil disimpan!";
} else {
    echo "Gagal menyimpan rating: " . $stmt->error;
}

$stmt->close();
$mysqli->close();


?>
