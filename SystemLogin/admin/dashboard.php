<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $targetUserId = $_POST['user_id'];
    $newRole = $_POST['new_role'];

    if (in_array($newRole, ['user', 'staff', 'admin'])) {
        $updateStmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        if ($updateStmt->execute([$newRole, $targetUserId])) {
            $successMsg = "User role updated successfully!";
        } else {
            $errorMsg = "Failed to update user role.";
        }
    } else {
        $errorMsg = "Invalid role selected.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $targetUserId = $_POST['user_id'];
    $newPassword = $_POST['new_password'];

    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $passStmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        if ($passStmt->execute([$hashedPassword, $targetUserId])) {
            $successMsg = "User password updated successfully in the database!";
        } else {
            $errorMsg = "Failed to update user password.";
        }
    } else {
        $errorMsg = "New password cannot be empty.";
    }
}

$stmt = $pdo->prepare('SELECT username, fName, lName, role FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

$username = htmlspecialchars($currentUser['username']);
$fullname = htmlspecialchars($currentUser['fName'] . ' ' . $currentUser['lName']);

$usersStmt = $pdo->query('SELECT id, fName, lName, username, email, role FROM users ORDER BY id ASC');
$allUsers = $usersStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../dashboard.css">
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <p class="mini-label">Admin Portal</p>
                <h1>Welcome, <?php echo $username; ?>!</h1>
            </div>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </header>

        <section class="profile-panel">
            <img src="../userIcon.webp" alt="Profile Photo" class="profile-image">
            <div class="profile-text">
                <p class="mini-label">Account Profile</p>
                <h2><?php echo $fullname; ?></h2>
                <span>Access Level: <strong>Admin</strong></span>
            </div>
        </section>

        <?php if (!empty($successMsg)): ?>
            <p style="color: green; background: #e6f4ea; padding: 8px; border-radius: 4px; margin-bottom: 1rem; text-align: center;"><?php echo htmlspecialchars($successMsg); ?></p>
        <?php endif; ?>
        <?php if (!empty($errorMsg)): ?>
            <p style="color: red; background: #fce8e6; padding: 8px; border-radius: 4px; margin-bottom: 1rem; text-align: center;"><?php echo htmlspecialchars($errorMsg); ?></p>
        <?php endif; ?>

        <div style="margin-top: 1rem;">
            <p class="mini-label" style="font-size: 0.9rem; margin-bottom: 0.5rem;">Registered System Accounts Management</p>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Password Reset</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allUsers as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['fName'] . ' ' . $u['lName']); ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td>
                                    <form action="dashboard.php" method="POST" style="display: flex; gap: 5px;">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <select name="new_role">
                                            <option value="user" <?php if ($u['role'] === 'user') echo 'selected'; ?>>User</option>
                                            <option value="staff" <?php if ($u['role'] === 'staff') echo 'selected'; ?>>Staff</option>
                                            <option value="admin" <?php if ($u['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                                        </select>
                                        <button type="submit" name="update_role">Save</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="dashboard.php" method="POST" style="display: flex; gap: 5px;">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <input type="password" name="new_password" placeholder="New password" required style="width: 110px;">
                                        <button type="submit" name="update_password" style="background-color: #b91c1c;">Change</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>