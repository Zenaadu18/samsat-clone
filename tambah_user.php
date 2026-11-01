<?php
require 'db.php';

$username = 'sena';
$password = '050503';

// Mengacak password menggunakan standar keamanan PHP (Bcrypt)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Menyimpan ke database
$stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");

if ($stmt) {
    $stmt->bind_param("ss", $username, $hashed_password);
    
    if ($stmt->execute()) {
        echo "<h3>✅ User '$username' berhasil ditambahkan!</h3>";
        echo "<p>Password asli: $password</p>";
        echo "<p>Hasil Hash yang masuk database: <br> <code>$hashed_password</code></p>";
        echo "<br><a href='admin_config.php'>Kembali ke Panel Admin</a>";
    } else {
        echo "Gagal menambahkan user: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Gagal menyiapkan query.";
}
?>