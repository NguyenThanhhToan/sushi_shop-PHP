<?php
// login.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Thêm dòng này để lưu role vào session
        header('Location: products.php');
        exit();
    } else {
        $message = 'Tên đăng nhập hoặc mật khẩu không đúng. Vui lòng thử lại.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/login.css">
    <title>Login</title>
</head>
<body>
    <form action="login.php" method="post">
    <h1>Đăng nhập</h1>
        <label for="username">Tên đăng nhập:</label>
        <input type="text" id="username" name="username" required><br><br>
        <label for="password">Mật khẩu:</label>
        <input type="password" id="password" name="password" required><br><br>
        <button type="submit">Đăng nhập</button>
        <a href="register.php"><button type="button">Đăng ký</button></a>
    </form>

    <?php if ($message): ?>
        <script>
            console.error('<?php echo $message; ?>');
        </script>
    <?php endif; ?>
</body>
</html>
