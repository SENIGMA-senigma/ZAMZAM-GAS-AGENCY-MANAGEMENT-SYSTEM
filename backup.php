<?php
/**
 * ZAMZAM GAS AGENCY - AUTO BACKUP SYSTEM
 * This script backs up your Database and Files to your Desktop
 */

require_once 'config.php';

// 1. Setup Backup Directory (Points to your Windows Desktop)
$userProfile = getenv('USERPROFILE');
$backupDir = $userProfile . "\\Desktop\\Zamzam_Backups\\";

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$timestamp = date('Y-m-d_H-i-s');
$dbBackupFile = $backupDir . "db_backup_$timestamp.sql";
$zipFile = $backupDir . "Full_System_Backup_$timestamp.zip";

/** STEP 1: DATABASE EXPORT **/
$tables = array();
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

$sqlScript = "-- Zamzam Gas Database Backup \n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
foreach ($tables as $table) {
    $query = "SHOW CREATE TABLE $table";
    $res = $conn->query($query);
    $row = $res->fetch_row();
    $sqlScript .= "\n\n" . $row[1] . ";\n\n";

    $query = "SELECT * FROM $table";
    $res = $conn->query($query);
    while ($row = $res->fetch_assoc()) {
        $keys = array_keys($row);
        $values = array_values($row);
        $sqlScript .= "INSERT INTO $table (" . implode(", ", $keys) . ") VALUES ('" . implode("', '", array_map('addslashes', $values)) . "');\n";
    }
}

file_put_contents($dbBackupFile, $sqlScript);

/** STEP 2: FILE COMPRESSION (ZIP) **/
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__), RecursiveIteratorIterator::LEAVES_ONLY);

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen(__DIR__) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
    // Add the fresh DB backup into the zip too
    $zip->addFile($dbBackupFile, "database_snapshot.sql");
    $zip->close();
    
    // Cleanup temporary SQL file
    unlink($dbBackupFile);

    echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
    echo "<h1 style='color:#2ecc71;'>✅ Backup Successful!</h1>";
    echo "<p>Your files and database are safe in: <br><strong>$zipFile</strong></p>";
    echo "<a href='manager_dashboard.php' style='color:#2c3e50; font-weight:bold;'>Return to Dashboard</a>";
    echo "</div>";
} else {
    echo "Backup failed. Ensure XAMPP has permission to write to Desktop.";
}
?>