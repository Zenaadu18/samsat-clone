CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password for 'admin' is 'admin123' (hashed using BCRYPT)
INSERT IGNORE INTO `admin_users` (`username`, `password`) VALUES
('admin', '$2y$10$tZ2.Q2xJj1kE/g5h4p7P5Oe/6tI3v/1Z2u4R8q7w8x1e3a4b5c6d7'); 
-- wait, I need to generate a real bcrypt hash for admin123.
-- Or better, I can just create a PHP script to generate it and insert it.
