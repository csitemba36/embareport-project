<?php
require_once('../../config/connect_db.php');

// === Pilih environment: staging atau production ===
$status = "staging"; // ubah ke "staging production" kalau mau staging

// Ambil API detail dari tabel yo_api_details
$sql = "SELECT endpoint, apikey, source, origin 
        FROM yo_api_details 
        WHERE status = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
$api = $result->fetch_assoc();

if (!$api) {
    die("API details untuk status '$status' tidak ditemukan");
}

$apikey   = $api['apikey'];
$source   = $api['source'];
$origin   = $api['origin'];