<?php
require_once 'db.php';

// 1. Create table admin_users
$sql = "CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conn->query($sql);

// 2. Insert default admin
$username = 'admin';
$password = password_hash('admin123', PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT IGNORE INTO `admin_users` (`username`, `password`) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$stmt->close();

// 3. Insert new amnesty configs
$newConfigs = [
    ['amnesti_bebas_denda_pkb_berjalan', '1', 'Bebas Denda PKB Berjalan (1=Ya, 0=Tidak)'],
    ['amnesti_bebas_denda_pkb_tunggakan', '1', 'Bebas Denda PKB Tunggakan (1=Ya, 0=Tidak)'],
    ['amnesti_bebas_denda_swdkllj_berjalan', '1', 'Bebas Denda SWDKLLJ Berjalan (1=Ya, 0=Tidak)'],
    ['amnesti_bebas_denda_swdkllj_tunggakan', '1', 'Bebas Denda SWDKLLJ Tunggakan (1=Ya, 0=Tidak)']
];

$stmtConfig = $conn->prepare("INSERT IGNORE INTO `konfigurasi` (`kunci`, `nilai`, `keterangan`) VALUES (?, ?, ?)");
foreach ($newConfigs as $cfg) {
    $stmtConfig->bind_param("sss", $cfg[0], $cfg[1], $cfg[2]);
    $stmtConfig->execute();
}
$stmtConfig->close();

echo "Admin setup completed successfully.\n";
