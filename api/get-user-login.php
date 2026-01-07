<?php
header('Content-Type: application/json');

require_once('../config/connect_db.php');

// Query untuk ambil user yang login hari ini
$sql = "SELECT fullname, 
			   DATE_FORMAT(MAX(logintime), '%H:%i') AS login_time
		FROM sys_login_emba_report
		WHERE DATE(logintime) = CURDATE() 
			  AND status = 1
			  AND fullname IS NOT NULL
		GROUP BY fullname
		ORDER BY logintime
		";

$result = $mysqli->query($sql);

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);

$mysqli->close();
?>
