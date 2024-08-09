<?php
// admin_users.php
session_start();
require_once __DIR__ . '/../../includes/db.php';

$hasAccess = isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'employee']);

if (!$hasAccess) {
    // Hiển thị thông báo lỗi
    echo '<p>You do not have permission to access this page.</p>';
    exit;
}

// Xử lý yêu cầu thêm, sửa, xóa người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        // Thêm người dùng mới
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
        // Sửa thông tin người dùng
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
        // Xóa người dùng
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
    // Truy vấn để lấy tất cả người dùng
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
        .form-container {
            margin: 20px 0;
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
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="button" onclick="fillEditForm(<?php echo htmlspecialchars(json_encode($user)); ?>)">Edit</button>
                                <button type="submit" name="delete_user">Delete</button>
                            </form>
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
