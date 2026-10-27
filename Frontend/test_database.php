<?php

require_once __DIR__ . '/config/database.php';

try {
    $result = $pdo->query("SELECT DATABASE()")->fetchColumn();

    echo "<h2>Database Connection Successful!</h2>";
    echo "<p>Connected database: " . htmlspecialchars($result) . "</p>";

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>Tables in your database:</h3><ul>";

    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }

    echo "</ul>";

} catch (PDOException $e) {
    echo "Database query failed. Check your PHP and database configuration.";
}
