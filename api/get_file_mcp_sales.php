<?php
$folderPath = "../Document_MCP"; 
$files = glob($folderPath . "/*.csv");

$fileList = [];

foreach ($files as $file) {
    $fileList[] = [
        "name" => basename($file),
        "size" => filesize($file),
        "date" => date("d/m/Y H:i", filemtime($file))
    ];
}

// kirim sebagai JSON ke frontend
header('Content-Type: application/json');
echo json_encode($fileList);
?>
