<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../login.php');
    exit;
}

$successMsg = '';
$errorMsg = '';

// Handle Adding Grade for a Specific User ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_grade'])) {
    $targetUserId = $_POST['user_id'];
    $subject = trim($_POST['subject']);
    $grade = trim($_POST['grade']);

    if (!empty($targetUserId) && !empty($subject) && !empty($grade)) {
        $insertStmt = $pdo->prepare('INSERT INTO grades (user_id, subject, grade) VALUES (?, ?, ?)');
        if ($insertStmt->execute([$targetUserId, $subject, $grade])) {
            $successMsg = "Grade successfully added to user ID #$targetUserId!";
        } else {
            $errorMsg = "Failed to add grade.";
        }
    } else {
        $errorMsg = "All grade fields (Subject and Grade) are required.";
    }
}

$stmt = $pdo->prepare('SELECT username, fName, lName, role FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

$username = htmlspecialchars($currentUser['username']);
$fullname = htmlspecialchars($currentUser['fName'] . ' ' . $currentUser['lName']);

// Fetch ONLY standard user accounts (excluding staff and admins)
$userAccountsQuery = $pdo->query("SELECT id, fName, lName, username, email, role FROM users WHERE role = 'user' ORDER BY id ASC");
$usersOnly = $userAccountsQuery->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="../dashboard.css">
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <p class="mini-label">Staff Portal</p>
                <h1>Welcome, <?php echo $username; ?>!</h1>
            </div>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </header>

        <section class="profile-panel">
            <img src="../userIcon.webp" alt="Profile Photo" class="profile-image">
            <div class="profile-text">
                <p class="mini-label">Account Profile</p>
                <h2><?php echo $fullname; ?></h2>
                <span>Access Level: <strong>Staff</strong></span>
            </div>
        </section>

        <?php if (!empty($successMsg)): ?>
            <p style="color: green; background: #e6f4ea; padding: 8px; border-radius: 4px; margin-bottom: 1rem; text-align: center;"><?php echo htmlspecialchars($successMsg); ?></p>
        <?php endif; ?>
        <?php if (!empty($errorMsg)): ?>
            <p style="color: red; background: #fce8e6; padding: 8px; border-radius: 4px; margin-bottom: 1rem; text-align: center;"><?php echo htmlspecialchars($errorMsg); ?></p>
        <?php endif; ?>

        <div style="background: #ecfeff; border: 1px solid #a5f3fc; padding: 15px; border-radius: 6px; margin-bottom: 1.5rem;">
            <p style="color: #155e75; font-size: 0.95rem;">Staff Directory: View standard user accounts and assign individual grades securely below.</p>
        </div>

        <div style="margin-top: 1rem;">
            <p class="mini-label" style="font-size: 0.9rem; margin-bottom: 0.5rem;">Standard User Accounts Directory</p>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Assign Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($usersOnly) > 0): ?>
                            <?php foreach ($usersOnly as $acc): ?>
                                <tr>
                                    <td><?php echo $acc['id']; ?></td>
                                    <td><?php echo htmlspecialchars($acc['fName'] . ' ' . $acc['lName']); ?></td>
                                    <td><?php echo htmlspecialchars($acc['username']); ?></td>
                                    <td><strong><?php echo ucfirst($acc['role']); ?></strong></td>
                                    <td>
                                        <form action="dashboard.php" method="POST" style="display: flex; gap: 5px; align-items: center;">
                                            <input type="hidden" name="user_id" value="<?php echo $acc['id']; ?>">
                                            <input type="text" name="subject" placeholder="Subject" required style="width: 110px;">
                                            <input type="text" name="grade" placeholder="Grade" required style="width: 70px;">
                                            <button type="submit" name="add_grade">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #666;">No standard user accounts found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>