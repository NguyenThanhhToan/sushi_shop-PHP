<?php
// register.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
    if ($stmt->execute([$username, $email, $password])) {
        $message = 'Registration successful.';
    } else {
        $message = 'Registration failed. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/login.css">
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>
    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    <form action="register.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Register</button>

        <a href="login.php"><button type="button">Login</button></a>
    </form>
</body>
</html>
