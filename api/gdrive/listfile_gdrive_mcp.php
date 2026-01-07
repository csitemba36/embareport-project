<?php
error_reporting(E_ERROR | E_PARSE);

require __DIR__ . '/../../vendor/autoload.php';

// Koneksi ke database MySQL
$mysqli = new mysqli("localhost", "root", "", "db_powerone_mcp");

// Cek koneksi
if ($mysqli->connect_errno) {
    echo json_encode(["error" => "Failed to connect to MySQL: " . $mysqli->connect_error]);
    exit;
}

use Google\Client;
use Google\Service\Drive;

/* ================================
   GOOGLE CLIENT
================================ */
$client = new Client();
$client->setAuthConfig(__DIR__ . '/gdrive-sales-mcp-4b12ba4f68e4.json');
$client->addScope(Drive::DRIVE_READONLY);

$drive = new Drive($client);

/* ================================
   GOOGLE DRIVE FOLDER ID
================================ */
$folderId = '1tUwJUeT7fU-nDCj7iqqdSTSIHpH2kW7Y';

/* ================================
   AMBIL FILE SUCCESS DARI DB
================================ */
$successFiles = [];

$sql = "
    SELECT TRIM(LOWER(nama_file)) AS nama_file
    FROM sales_transaction_logs
    WHERE status = 'SUCCESS'
";

$result = $mysqli->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $successFiles[] = $row['nama_file'];
    }
}

/* ================================
   LIST FILE GOOGLE DRIVE
================================ */
$response = $drive->files->listFiles([
    'q' => "'$folderId' in parents and trashed=false",
    'fields' => 'files(name,size,modifiedTime)',
    'pageSize' => 1000
]);

$fileList = [];

foreach ($response->files as $file) {

    $fileNameNormalized = trim(strtolower($file->name));
    $isSuccess = in_array($fileNameNormalized, $successFiles);

    $fileList[] = [
        'name'       => $file->name,
        'size'       => (int) $file->size,
        'date'       => date('d/m/Y H:i', strtotime($file->modifiedTime)),
        'is_success' => $isSuccess
    ];
}

/* ================================
   OUTPUT JSON
================================ */
header('Content-Type: application/json');
echo json_encode($fileList, JSON_PRETTY_PRINT);
