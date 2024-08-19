<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    try {
        // Kiểm tra mật khẩu hiện tại
        $sql = 'SELECT password FROM users WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($currentPassword, $user['password'])) {
            if ($newPassword === $confirmPassword) {
                if (!empty($newPassword)) {
                    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $sql = 'UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$username, $email, $newPasswordHash, $userId]);
                } else {
                    $sql = 'UPDATE users SET username = ?, email = ? WHERE id = ?';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$username, $email, $userId]);
                }
                header('Location: profiles.php');
                exit;
            } else {
                $error = 'New password and confirmation do not match.';
            }
        } else {
            $error = 'Current password is incorrect.';
        }
    } catch (PDOException $e) {
        echo "Lỗi: " . $e->getMessage();
    }
}

$sql = 'SELECT * FROM users WHERE id = ?';
$stmt = $conn->prepare($sql);
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/edit_profile.css">
    <title>Edit Profile</title>
</head>
<body>
    <?php include '../templates/header.php'; ?>
    <h1>Edit Your Profile</h1>
    <div class="container">
        <form method="post">
            <input type="hidden" name="update_user">
            
            <?php if (isset($error)) : ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>
            
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" placeholder="Leave blank if no change">
            
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Leave blank if no change">
            
            <button type="submit">Update</button>
        </form>
    </div>
</body>
</html>
