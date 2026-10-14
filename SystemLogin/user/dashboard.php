<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch current user details
$stmt = $pdo->prepare('SELECT id, username, fName, lName, role FROM users WHERE id = ?');
$stmt->execute([$userId]);
$currentUser = $stmt->fetch();

$studentId = $currentUser['id'];
$username = htmlspecialchars($currentUser['username']);
$fullname = htmlspecialchars($currentUser['fName'] . ' ' . $currentUser['lName']);

// Fetch grades for this specific user/student
$gradesStmt = $pdo->prepare('SELECT subject, grade FROM grades WHERE user_id = ?');
$gradesStmt->execute([$userId]);
$myGrades = $gradesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../dashboard.css">
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <p class="mini-label">Student Portal</p>
                <h1>Welcome, <?php echo $username; ?>!</h1>
            </div>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </header>

        <section class="profile-panel">
            <img src="../userIcon.webp" alt="Profile Photo" class="profile-image">
            <div class="profile-text">
                <p class="mini-label">Account Profile</p>
                <h2><?php echo $fullname; ?></h2>
                <span>Student ID: <strong>#<?php echo $studentId; ?></strong> | Tier: <strong>Student</strong></span>
            </div>
        </section>

        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 6px; margin-bottom: 1.5rem;">
            <p style="color: #15803d; font-size: 0.95rem;">Academic Portal: View your recorded grades and coursework metrics below.</p>
        </div>

        <div style="margin-top: 1rem;">
            <p class="mini-label" style="font-size: 0.9rem; margin-bottom: 0.5rem;">My Grades Record</p>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Subject / Course</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($myGrades) > 0): ?>
                            <?php foreach ($myGrades as $g): ?>
                                <tr>
                                    <td><?php echo $studentId; ?></td>
                                    <td><?php echo $fullname; ?></td>
                                    <td><?php echo htmlspecialchars($g['subject']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($g['grade']); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #666;">No grades posted yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>