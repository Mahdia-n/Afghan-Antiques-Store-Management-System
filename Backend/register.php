<?php
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name === "" || $email === "" || $password === "") {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } else {
        try {
            $check = $pdo->prepare(
                "SELECT id FROM customers WHERE email = ?"
            );
            $check->execute([$email]);

            if ($check->fetch()) {
                $message = "This email is already registered.";
            } else {
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = $pdo->prepare(
                    "INSERT INTO customers (name, email, password)
                     VALUES (?, ?, ?)"
                );
                $stmt->execute([
                    $name,
                    $email,
                    $hashedPassword
                ]);

                $message = "Registration successful!";
            }
        } catch (PDOException $e) {
            $message = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
</head>
<body>
    <h1>Create Account</h1>

    <p><?= htmlspecialchars($message) ?></p>

    <form method="POST" action="">
        <label for="name">Full Name</label><br>
        <input type="text" id="name" name="name" required>
        <br><br>

        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" required>
        <br><br>

        <label for="password">Password</label><br>
        <input type="password" id="password" name="password"
               minlength="8" required>
        <br><br>

        <button type="submit">Register</button>
    </form>
</body>
</html>