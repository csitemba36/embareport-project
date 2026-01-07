<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

header('Content-Type: application/json');

// ==================================
// VALIDASI INPUT
// ==================================
if (empty($_POST['filename'])) {
    echo json_encode(['status' => false, 'message' => 'Filename tidak dikirim']);
    exit;
}

$filename   = $_POST['filename'];
$userUpload = $_COOKIE['fullname'] ?? 'SYSTEM';


// ==================================
// CEK FILE SUDAH PERNAH UPLOAD (SUCCESS)
// ==================================
$mysqliCheck = new mysqli("localhost", "root", "", "db_powerone_mcp");
if ($mysqliCheck->connect_errno) {
    echo json_encode(['status' => false, 'message' => $mysqliCheck->connect_error]);
    exit;
}
$mysqliCheck->set_charset("utf8mb4");

$cekSql = "
    SELECT 1
    FROM sales_transaction_logs
    WHERE nama_file = ?
      AND status = 'SUCCESS'
    LIMIT 1
";

$cekStmt = $mysqliCheck->prepare($cekSql);
$cekStmt->bind_param("s", $filename);
$cekStmt->execute();
$cekStmt->store_result();

if ($cekStmt->num_rows > 0) {
    echo json_encode([
        'status'  => false,
        'message' => 'File ini sudah pernah diupload.'
    ]);
    $cekStmt->close();
    $mysqliCheck->close();
    exit;
}

$cekStmt->close();
$mysqliCheck->close();


// ==================================
// HELPER DATE
// ==================================
function toDate($date) {
    $d = DateTime::createFromFormat('d-m-Y', $date);
    return $d ? $d->format('Y-m-d') : null;
}

function toDateTime($date) {
    $d = DateTime::createFromFormat('d-m-Y', $date);
    return $d ? $d->format('Y-m-d 00:00:00') : null;
}


// ==================================
// GOOGLE DRIVE CLIENT
// ==================================
require __DIR__ . '/../../vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

$client = new Client();
$client->setAuthConfig(__DIR__ . '/gdrive-sales-mcp-4b12ba4f68e4.json');
$client->addScope(Drive::DRIVE_READONLY);

$drive = new Drive($client);


// ==================================
// CARI FILE DI GOOGLE DRIVE
// ==================================
$response = $drive->files->listFiles([
    'q' => "name='$filename' and trashed=false",
    'fields' => 'files(id,name)',
    'pageSize' => 1
]);

if (empty($response->files)) {
    echo json_encode(['status' => false, 'message' => 'File tidak ditemukan']);
    exit;
}

$fileId = $response->files[0]->id;


// ==================================
// DOWNLOAD FILE
// ==================================
$content  = $drive->files->get($fileId, ['alt' => 'media']);
$tempFile = sys_get_temp_dir() . '/' . uniqid() . '_' . $filename;
file_put_contents($tempFile, $content->getBody()->getContents());


// ==================================
// MYSQL
// ==================================
$mysqli = new mysqli("localhost", "root", "", "db_powerone_mcp");
if ($mysqli->connect_errno) {
    echo json_encode(['status' => false, 'message' => $mysqli->connect_error]);
    exit;
}
$mysqli->set_charset("utf8mb4");

// helper escape
$esc = fn($v) => $v === null ? "NULL" : "'" . $mysqli->real_escape_string($v) . "'";


// ==================================
// BUKA CSV
// ==================================
$handle    = fopen($tempFile, "r");
$delimiter = "|";
fgetcsv($handle, 0, $delimiter); // skip header

$inserted = 0;
$failed   = 0;

$mysqli->begin_transaction();

while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {

    if (count($row) < 28) {
        $failed++;
        continue;
    }

    $PROCESS_DATE  = toDate($row[0]);
    $TRANSACT_DATE = toDateTime($row[1]);

    $sqlInsert = "
        INSERT INTO sales_transaction_details (
            PROCESS_DATE, TRANSACT_DATE, SUPPLIER_NO, SUPPLIER_NAME,
            STORE_NO, STORE_NAME, CLASS, DEPT, `GROUP`, BRAND, WORLD,
            COLOUR, SIZE, ITEM, BARCODE, QTY, ITEM_PRICE,
            GROSS_AMT, DISC_AUTO, DISC_PROMO, DISC_EMPLOYEE,
            DISC_FREE_ITEM, DISC_STRUK, DISC_PCT, NET_AMT,
            POS_NO, TRANSACT_NO, TRANSACT_LINE_NO,
            REFERENSI_FILE, USER_UPLOAD
        ) VALUES (
            {$esc($PROCESS_DATE)},
            {$esc($TRANSACT_DATE)},
            {$esc($row[2])},
            {$esc(trim($row[3], '\"'))},
            {$esc($row[4])},
            {$esc(trim($row[5], '\"'))},
            {$esc($row[6])},
            {$esc($row[7])},
            {$esc($row[8])},
            {$esc($row[9])},
            {$esc($row[10])},
            " . ($row[11] === '-' ? "NULL" : $esc($row[11])) . ",
            " . ($row[12] === '-' ? "NULL" : $esc($row[12])) . ",
            {$esc($row[13])},
            {$esc($row[14])},
            " . (int)$row[15] . ",
            " . (float)$row[16] . ",
            " . (float)$row[17] . ",
            " . (float)$row[18] . ",
            " . (float)$row[19] . ",
            " . (float)$row[20] . ",
            " . (float)$row[21] . ",
            " . (float)$row[22] . ",
            " . (float)$row[23] . ",
            " . (float)$row[24] . ",
            {$esc($row[25])},
            {$esc($row[26])},
            " . (int)$row[27] . ",
            {$esc($filename)},
            {$esc($userUpload)}
        )
    ";

    if ($mysqli->query($sqlInsert)) {
        $inserted++;
    } else {
        $failed++;
    }
}


// ==================================
// COMMIT / ROLLBACK
// ==================================
$success = ($failed === 0);

if ($success) {
    $mysqli->commit();
} else {
    $mysqli->rollback();
}


// ==================================
// INSERT LOG
// ==================================
$logStatus = $success ? 'SUCCESS' : 'FAILED';

$logSql = "
INSERT INTO sales_transaction_logs (
    nama_file,
    status,
    inserted,
    user
) VALUES (
    {$esc($filename)},
    {$esc($logStatus)},
    {$esc((string)$inserted)},
    {$esc($userUpload)}
)
";
$mysqli->query($logSql);


// ==================================
// CLEANUP
// ==================================
fclose($handle);
unlink($tempFile);
$mysqli->close();


// ==================================
// OUTPUT
// ==================================
echo json_encode([
    'status'   => $success,
    'filename' => $filename,
    'inserted' => $inserted,
    'failed'   => $failed
], JSON_PRETTY_PRINT);
