
<?php
try {
    $host = 'localhost';
    $dbname = 'afghan_antique_db';
    $username = 'root';
    $password = '';

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    echo "Database connection successful!";

} catch (PDOException $e) {
    echo "<h3>Actual database error:</h3>";
    echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
