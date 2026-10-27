
<?php
$host = 'localhost';
$dbname = 'your_inventory_database';
$username = 'your_mysql_username';
$password = 'your_mysql_password';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Unable to connect to the database.');
}
