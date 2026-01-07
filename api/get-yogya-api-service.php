<?php

require_once('../config/connect_db.php');
header('Content-Type: application/json');

// Query data
$sql = "SELECT id, status, endpoint, apikey, source, origin FROM yo_api_details ORDER BY id ASC";
$result = $mysqli->query($sql);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $result->free();
}

$mysqli->close();

// Output JSON
echo json_encode($data);
