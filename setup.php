<?php
// setup.php - Imports the SQL dump via MySQL CLI
$host = '127.0.0.1';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$mysqlBin = 'C:\\xampp\\mysql\\bin\\mysql.exe';
$sqlFile = __DIR__ . DIRECTORY_SEPARATOR . 'setup.sql';

if (!file_exists($sqlFile)) {
    die("<p style='color:red'>Error: setup.sql not found. Please run the Python export script first.</p>");
}

// Build the command
$passArg = !empty($pass) ? "-p" . escapeshellarg($pass) : "";
$cmd = "\"$mysqlBin\" -u {$user} {$passArg} -h {$host} < \"$sqlFile\" 2>&1";

echo "<h1>Database Setup</h1>";
echo "<p>Importing SQL dump into MySQL. This may take a moment for large files...</p>";
flush();

exec($cmd, $output, $returnCode);

if ($returnCode === 0) {
    echo "<p style='color: green; font-size: 1.2em;'>✅ Database setup completed successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Import failed (exit code: {$returnCode}):</p>";
    echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
}

echo "<br><a href='index.php'>Go to Calculator →</a>";
?>
