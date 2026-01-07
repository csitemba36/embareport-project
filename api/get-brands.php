<?php
require_once('../config/connect_db.php');

// ================= AMBIL COOKIE =================
$allowedMerk = isset($_COOKIE['aksesmerk']) ? trim($_COOKIE['aksesmerk']) : '';

// ================= BASE QUERY =================
$sql = "
    SELECT 
        a.id,
        a.kodemerk,
        a.namamerk,
        a.kodemerkyo,
        a.kodemerkma,
        a.updatetime,
        b.brand_name
    FROM m_brands a
    JOIN yo_brand b ON a.id = b.id
";

// ================= FILTER MERK DARI COOKIE =================
$where = [];
$params = [];
$types  = "";

if (!empty($allowedMerk) && strtoupper($allowedMerk) !== 'ALL MERK') {

    // contoh: A;B;C
    $merkList = array_filter(array_map('trim', explode(';', $allowedMerk)));

    if (!empty($merkList)) {
        $placeholders = implode(',', array_fill(0, count($merkList), '?'));
        $where[] = "a.kodemerk IN ($placeholders)";

        foreach ($merkList as $m) {
            $params[] = $m;
            $types .= "s";
        }
    }
}

// ================= GABUNG WHERE =================
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// ================= PREPARE & EXECUTE =================
$stmt = $mysqli->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// ================= AMBIL DATA =================
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// ================= OUTPUT JSON =================
header('Content-Type: application/json');
echo json_encode([
    "data" => $data
]);

$stmt->close();
$mysqli->close();