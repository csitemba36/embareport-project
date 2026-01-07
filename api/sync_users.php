<?php
// Koneksi ke SQL Server via ODBC
$dsn = "dbmaserp_user";        // Sesuaikan dengan DSN ODBC Anda
$sqlsrv_user = "db";      // Username SQL Server
$sqlsrv_pass = "3mb4Sejati";      // Password SQL Server

$conn = odbc_connect($dsn, $sqlsrv_user, $sqlsrv_pass);
if (!$conn) {
    die(json_encode(["error" => "Koneksi SQL Server gagal: " . odbc_errormsg()]));
}

// Koneksi ke MySQL
$mysqli = new mysqli("localhost", "root", "", "db_powerone");
if ($mysqli->connect_errno) {
    die(json_encode(["error" => "Koneksi MySQL gagal: " . $mysqli->connect_error]));
}

// Koneksi ke SQL Server pakai koneksi ODBC ($conn dari connect_db.php)
$sql = "SELECT a.UserName, a.KodePassword, a.FullName, a.Email, a.AllKodeMerk, a.UserRoleCode, b.WarehouseAllowed
        FROM Users a,
            UserRoles b
        WHERE a.UserRoleCode = b.UserRoleCode";

$rs = odbc_exec($conn, $sql);

if (!$rs) {
    die(json_encode(["error" => "Gagal eksekusi query SQL Server: " . odbc_errormsg()]));
}

$inserted = 0;
$updated = 0;

while ($row = odbc_fetch_array($rs)) {
    $username    = $row['UserName'];
    $password    = $row['KodePassword'];
    $fullname    = $row['FullName'];
    $email       = $row['Email'];
    $akses_merk  = $row['AllKodeMerk'];
    $role_code   = $row['UserRoleCode'];
    $gudang_allowed = $row['WarehouseAllowed'];
 

    // Cek apakah user sudah ada di MySQL
    $cek = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        // Update
        $update = $mysqli->prepare("UPDATE users SET password=?, fullname=?, email=?, akses_merk=?, user_role_code=?, akses_gudang=? WHERE username=?");
        $update->bind_param("sssssss", $password, $fullname, $email, $akses_merk, $role_code, $gudang_allowed, $username);
        $update->execute();
        $updated++;
        $update->close();
    } else {
        // Insert
        $insert = $mysqli->prepare("INSERT INTO users (username, password, fullname, email, akses_merk, user_role_code, akses_gudang) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("sssssss", $username, $password, $fullname, $email, $akses_merk, $role_code, $gudang_allowed);
        $insert->execute();
        $inserted++;
        $insert->close();
    }
    $cek->close();
}

// Tutup koneksi ODBC SQL Server
odbc_close($conn);

// Tampilkan hasil
echo "Sinkronisasi selesai! Inserted: $inserted | Updated: $updated'";
?>
