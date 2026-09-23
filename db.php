<?php
// db.php
$host = '127.0.0.1';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$dbname = 'samsat_db';

// Create connection to MySQL (no DB selected yet, for initial setup)
$conn_init = new mysqli($host, $user, $pass);
if ($conn_init->connect_error) {
    die("Connection failed: " . $conn_init->connect_error);
}

// Ensure Database exists
$conn_init->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn_init->close();

// Now connect to the DB
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
