<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "afghan_antique_store";

$antique_conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($antique_conn->connect_error) {
    die("Database connection failed: " . $antique_conn->connect_error);
}

$antique_conn->set_charset("utf8mb4");

echo "Antique Store Database Connected Successfully!";
?>