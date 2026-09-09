<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$successMsg = '';
$errorMsg = '';

// Handle Role Update (Admin Only Action)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    // Verify session user is actually an admin before allowing updates
    $checkAdmin = $pdo->prepare('SELECT role FROM users WHERE id = ?');
    $checkAdmin->execute([$_SESSION['user_id']]);
    $currentAdmin = $checkAdmin->fetch();

    if ($currentAdmin && $currentAdmin['role'] === 'admin') {
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
    } else {
        $errorMsg = "Unauthorized action.";
    }
}

// Fetch current user details from database to ensure up-to-date role data
$stmt = $pdo->prepare('SELECT username, fName, lName, role FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$username = htmlspecialchars($currentUser['username']);
$fullname = htmlspecialchars($currentUser['fName'] . ' ' . $currentUser['lName']);
$role = $currentUser['role'];

// Update session role just in case it changed
$_SESSION['role'] = $role;

// If user is admin, fetch all registered users for management
$allUsers = [];
if ($role === 'admin') {
    $usersStmt = $pdo->query('SELECT id, fName, lName, username, email, role FROM users ORDER BY id ASC');
    $allUsers = $usersStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo ucfirst($role); ?></title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: #fff; border-radius: 8px; overflow: hidden; }
        .admin-table th, .admin-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .admin-table th { background-color: #f3f4f6; color: #111827; font-weight: bold; }
        .role-select { padding: 6px; border-radius: 4px; border: 1px solid #ccc; }
        .update-btn { padding: 6px 12px; background: #000; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .update-btn:hover { background: #333; }
        .alert-success { color: green; background: #e6f4ea; padding: 10px; border-radius: 5px; margin-bottom: 1rem; }
        .alert-error { color: red; background: #fce8e6; padding: 10px; border-radius: 5px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="dashboard-page">
        <div class="container">
            <header class="topbar">
                <div>
                    <p class="mini-label">Overview Portal</p>
                    <h1>Welcome, <?php echo $username; ?>!</h1>
                </div>
                <a href="logout.php" class="logout-btn" style="text-decoration:none; text-align:center;">Logout</a>
            </header>

            <section class="profile-panel">
                <img src="userIcon.webp" alt="Profile Photo" class="profile-image">
                <div class="profile-text">
                    <p class="mini-label">Account Profile</p>
                    <h2><?php echo $fullname; ?></h2>
                    <span>Access Level: <strong><?php echo ucfirst($role); ?></strong></span>
                </div>
            </section>

            <?php if (!empty($successMsg)): ?>
                <div class="alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMsg)): ?>
                <div class="alert-error"><?php echo htmlspecialchars($errorMsg); ?></div>
            <?php endif; ?>

            <!-- ================= UNIQUE LAYOUTS BASED ON ROLE ================= -->

            <?php if ($role === 'admin'): ?>
                <!-- ADMIN LAYOUT -->
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; padding: 20px; border-radius: 12px; margin-bottom: 1.8rem;">
                    <h3 style="margin-bottom: 10px; color: #0f172a;">👑 Administrator Management Panel</h3>
                    <p style="color: #475569; margin-bottom: 15px;">As an administrator, you can manage user accounts and modify permissions across the system database.</p>
                    
                    <h4 style="margin-top: 15px; margin-bottom: 10px;">Registered System Accounts</h4>
                    <div style="overflow-x: auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Current Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allUsers as $u): ?>
                                    <tr>
                                        <td><?php echo $u['id']; ?></td>
                                        <td><?php echo htmlspecialchars($u['fName'] . ' ' . $u['lName']); ?></td>
                                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td>
                                            <form action="dashboard.php" method="POST" style="display: flex; gap: 8px; align-items: center;">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <select name="new_role" class="role-select">
                                                    <option value="user" <?php if ($u['role'] === 'user') echo 'selected'; ?>>User</option>
                                                    <option value="staff" <?php if ($u['role'] === 'staff') echo 'selected'; ?>>Staff</option>
                                                    <option value="admin" <?php if ($u['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                                                </select>
                                        </td>
                                        <td>
                                                <button type="submit" name="update_role" class="update-btn">Update Role</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($role === 'staff'): ?>
                <!-- STAFF LAYOUT -->
                <div style="background: #ecfeff; border: 1px solid #a5f3fc; padding: 20px; border-radius: 12px; margin-bottom: 1.8rem;">
                    <h3 style="margin-bottom: 10px; color: #0e7490;">🛠️ Staff Operations Dashboard</h3>
                    <p style="color: #155e75;">Welcome to the staff portal. Here you can track internal tasks, review system metrics, and assist standard users.</p>
                </div>

                <section class="stats-grid">
                    <article class="stat-card">
                        <p>Pending Tickets</p>
                        <strong>14</strong>
                        <small>Requires review</small>
                    </article>
                    <article class="stat-card">
                        <p>Assigned Projects</p>
                        <strong>5</strong>
                        <small>Active workflow</small>
                    </article>
                    <article class="stat-card">
                        <p>Client Messages</p>
                        <strong>22</strong>
                        <small>Unread inquiries</small>
                    </article>
                </section>

            <?php else: ?>
                <!-- REGULAR USER LAYOUT -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 20px; border-radius: 12px; margin-bottom: 1.8rem;">
                    <h3 style="margin-bottom: 10px; color: #166534;">👤 User Portal Dashboard</h3>
                    <p style="color: #15803d;">Welcome back! View your personal account summary and recent activity below.</p>
                </div>

                <section class="stats-grid">
                    <article class="stat-card">
                        <p>My Activity Status</p>
                        <strong>Active</strong>
                        <small>Standard User Tier</small>
                    </article>
                    <article class="stat-card">
                        <p>Support Requests</p>
                        <strong>1</strong>
                        <small>In progress</small>
                    </article>
                    <article class="stat-card">
                        <p>Saved Items</p>
                        <strong>8</strong>
                        <small>Updated today</small>
                    </article>
                </section>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>