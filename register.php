<?php
require_once 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fName = trim($_POST['fName']);
    $lName = trim($_POST['lName']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Username or email already taken.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            // Default role is set to 'user'
            $insert = $pdo->prepare('INSERT INTO users (fName, lName, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?, "user")');
            if ($insert->execute([$fName, $lName, $username, $email, $phone, $hashedPassword])) {
                header('Location: login.php?registered=success');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="container">
        <div class="register-form">
            <h2>Register</h2>
            <?php if (!empty($error)): ?>
                <p style="color: red; text-align: center; margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="fName">First Name:</label>
                    <input type="text" id="fName" name="fName" required>
                    <label for="lName">Last Name:</label>
                    <input type="text" id="lName" name="lName" required>
                </div>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    <label for="phone">Phone Number:</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    <label for="confirm-password">Confirm Password:</label>
                    <input type="password" id="confirm-password" name="confirm-password" required>
                </div>
                <button type="submit" id="btn">Register</button>
            </form>
        </div>
    </div>
</body>
</html>