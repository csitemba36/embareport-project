<?php
require_once('../config/connect_db.php');

$userId = intval($_POST['user_id']);
$menuId = intval($_POST['menu_id']);
$hasAccess = intval($_POST['has_access']); // 1 = insert, 0 = delete

if ($hasAccess === 1) {
    // Cek dulu apakah data sudah ada
    $check = $mysqli->prepare("SELECT 1 FROM menu_access WHERE user_id = ? AND menu_id = ?");
    $check->bind_param("ii", $userId, $menuId);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        // Insert jika belum ada
        $insert = $mysqli->prepare("INSERT INTO menu_access (user_id, menu_id) VALUES (?, ?)");
        $insert->bind_param("ii", $userId, $menuId);
        $insert->execute();
    }

    echo json_encode(['status' => 'inserted']);
} else {
    // Delete jika uncheck
    $delete = $mysqli->prepare("DELETE FROM menu_access WHERE user_id = ? AND menu_id = ?");
    $delete->bind_param("ii", $userId, $menuId);
    $delete->execute();

    echo json_encode(['status' => 'deleted']);
}
