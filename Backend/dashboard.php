<?php
session_start();

if (!isset($_SESSION["customer_id"])) {
    header("Location: login.php");
    exit;
}

$name = $_SESSION["customer_name"] ?? "Customer";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard</title>
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($name) ?>!</h1>

    <p>You are successfully logged in.</p>

    <a href="logout.php">Logout</a>
</body>
</html>