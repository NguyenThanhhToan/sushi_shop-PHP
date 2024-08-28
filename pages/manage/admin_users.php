<?php
// admin_users.php

session_start();
require_once __DIR__ . '/../../includes/db.php';

$hasAccess = isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'employee']);

if (!$hasAccess) {
    echo '<p>You do not have permission to access this page.</p>';
    exit;
}

// Xử lý yêu cầu thêm, sửa, xóa người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        try {
            $sql = 'INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)';
            $stmt = $conn->prepare($sql);
            $stmt->execute([$username, $email, $password, $role]);
            header('Location: admin_users.php');
            exit;
        } catch (PDOException $e) {
            echo "Lỗi: " . $e->getMessage();
        }
    } elseif (isset($_POST['edit_user'])) {
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];

        try {
            $sql = 'UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->execute([$username, $email, $role, $user_id]);
            header('Location: admin_users.php');
            exit;
        } catch (PDOException $e) {
            echo "Lỗi: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];

        try {
            $sql = 'DELETE FROM users WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id]);
            header('Location: admin_users.php');
            exit;
        } catch (PDOException $e) {
            echo "Lỗi: " . $e->getMessage();
        }
    }
}

try {
    $sql = 'SELECT id, username, email, role FROM users';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User List</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-top: 8%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .form-container h2 {
            margin-top: 0;
            color: #333;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
        }
        .form-container label {
            margin: 10px 0 5px;
            font-weight: bold;
        }
        .form-container input, .form-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-container button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-container button:hover {
            background-color: #45a049;
        }
        .form-container p {
            text-align: center;
            color: #f44336;
        }
        .actions-form {
            display: flex;

        }
        .actions-form button {
            margin-right: 5px;
        }
        .actions-form form {
            display: flex;
        }
    </style>
</head>
<body>
<?php include 'header_admin.php'; ?>
    <h1>User List</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($users) && !empty($users)) : ?>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td>
                            <div class="actions-form">
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="button" onclick="fillEditForm(<?php echo htmlspecialchars(json_encode($user)); ?>)">Edit</button>
                                </form>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="form-container">
        <h2>Add User</h2>
        <form method="post">
            <input type="hidden" name="add_user">
            <label for="add_username">Username:</label>
            <input type="text" id="add_username" name="username" required>
            <label for="add_email">Email:</label>
            <input type="email" id="add_email" name="email" required>
            <label for="add_password">Password:</label>
            <input type="password" id="add_password" name="password" required>
            <label for="add_role">Role:</label>
            <select id="add_role" name="role" required>
                <option value="admin">Admin</option>
                <option value="employee">Employee</option>
                <option value="customer">Customer</option>
            </select>
            <button type="submit">Add</button>
        </form>
    </div>

    <div class="form-container">
        <h2>Edit User</h2>
        <form method="post" id="editForm">
            <input type="hidden" name="edit_user">
            <input type="hidden" id="edit_user_id" name="user_id">
            <label for="edit_username">Username:</label>
            <input type="text" id="edit_username" name="username" required>
            <label for="edit_email">Email:</label>
            <input type="email" id="edit_email" name="email" required>
            <label for="edit_role">Role:</label>
            <select id="edit_role" name="role" required>
                <option value="admin">Admin</option>
                <option value="employee">Employee</option>
                <option value="customer">Customer</option>
            </select>
            <button type="submit">Edit</button>
        </form>
    </div>

    <script>
        function fillEditForm(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
        }
    </script>
</body>
</html>
