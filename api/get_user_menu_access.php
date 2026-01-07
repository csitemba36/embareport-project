<?php
require_once('../config/connect_db.php');

$userId = intval($_POST['user_id']);

// Ambil semua menu
$menus = $mysqli->query("SELECT id, `group`, title FROM menu")->fetch_all(MYSQLI_ASSOC);

// Ambil menu_id yang dimiliki user
$accessQuery = $mysqli->query("SELECT menu_id FROM menu_access WHERE user_id = $userId");
$accessList = [];
while ($row = $accessQuery->fetch_assoc()) {
    $accessList[] = intval($row['menu_id']);
}

// Gabungkan data
foreach ($menus as &$menu) {
    $menu['has_access'] = in_array($menu['id'], $accessList);
}

echo json_encode(['menus' => $menus]);
