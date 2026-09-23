<?php
require_once 'db.php';
$res = $conn->query("SHOW COLUMNS FROM konfigurasi");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
