<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `60secondsnews` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
    echo "Database created successfully.\n";
    
    // Use the database
    $pdo->exec("USE `60secondsnews`;");
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/database.sql');
    if ($sql === false) {
        die("Could not read database.sql");
    }
    
    $pdo->exec($sql);
    echo "Tables and data imported successfully.\n";
    
    // Optional: import dummy data if it exists and tables are empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM news");
    $count = $stmt->fetchColumn();
    if ($count == 0 && file_exists(__DIR__ . '/dummy_data.sql')) {
        $dummy_sql = file_get_contents(__DIR__ . '/dummy_data.sql');
        $pdo->exec($dummy_sql);
        echo "Dummy data imported successfully.\n";
    }

} catch (PDOException $e) {
    die("Setup failed: " . $e->getMessage());
}
?>
