<?php
session_start();
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $message = "Please enter your email and password.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "SELECT id, full_name, email, password, role
                 FROM Users
                 WHERE email = ?"
            );

            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user["password"])) {
                session_regenerate_id(true);

                $_SESSION["customer_id"] = $user["id"];
                $_SESSION["customer_name"] = $user["full_name"];
                $_SESSION["customer_role"] = $user["role"];

                header("Location: dashboard.php");
                exit;
            } else {
                $message = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $message = "Login failed. Please check the database settings.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>

    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>

    <form method="POST" action="">
        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" required>
        <br><br>

        <label for="password">Password</label><br>
        <input type="password" id="password" name="password" required>
        <br><br>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account?
        <a href="register.php">Register here</a>
    </p>
</body>
</html>