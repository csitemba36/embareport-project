<?php
require_once('../config/connect_db.php');

$columns = ['id', 'username', 'fullname', 'email', 'akses_merk', 'user_role_code', 'has_access'];

// Paging
$limit = intval($_POST['length']);
$start = intval($_POST['start']);

// Ordering
$order_col_index = intval($_POST['order'][0]['column']);
$order_col = $columns[$order_col_index] ?? 'id';
$order_dir = $_POST['order'][0]['dir'] === 'desc' ? 'DESC' : 'ASC';

// Searching
$search_value = $_POST['search']['value'] ?? '';
$search_sql = '';
if (!empty($search_value)) {
    $search_value = $mysqli->real_escape_string($search_value);
    $search_sql = "WHERE u.username LIKE '%$search_value%' 
                OR u.fullname LIKE '%$search_value%' 
                OR u.email LIKE '%$search_value%' 
                OR u.user_role_code LIKE '%$search_value%' 
                OR u.akses_merk LIKE '%$search_value%'";
}

// Total records
$total_query = $mysqli->query("SELECT COUNT(*) as total FROM users");
$total_data = $total_query->fetch_assoc()['total'];

// Total filtered
$filtered_query = $mysqli->query("SELECT COUNT(*) as total FROM users u $search_sql");
$total_filtered = $filtered_query->fetch_assoc()['total'];

// Fetch data + penanda pakai EXISTS
$query = "
    SELECT 
        u.*,
        CASE WHEN EXISTS (
            SELECT 1 FROM menu_access ma WHERE ma.user_id = u.id
        ) THEN 1 ELSE 0 END AS has_access
    FROM users u
    $search_sql
    ORDER BY $order_col $order_dir 
    LIMIT $start, $limit
";

$data_result = $mysqli->query($query);

$data = [];
while ($row = $data_result->fetch_assoc()) {
    $row['has_access'] = $row['has_access'] == 1 ? "✅ Ada" : "❌ Belum";
    $data[] = $row;
}

// Output JSON
echo json_encode([
    "draw" => intval($_POST['draw']),
    "recordsTotal" => intval($total_data),
    "recordsFiltered" => intval($total_filtered),
    "data" => $data
]);
