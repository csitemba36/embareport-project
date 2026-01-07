<?php
require_once('../config/connect_db.php');

// Query ambil data brand dari MySQL db_powerone
$sql = "SELECT id, brand_name, brand_code, update_time FROM yo_brand";
$result = $mysqli->query($sql);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// response JSON
header('Content-Type: application/json');
echo json_encode(["data" => $data]);

$mysqli->close();
