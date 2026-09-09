<?php
session_start();
require_once 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Store user session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fName'] . ' ' . $user['lName'];
        $_SESSION['role'] = $user['role']; // 'user', 'staff', or 'admin'

        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <img src="userIcon.webp" alt="Logo" class="logo">
            <h2>Login</h2>
            <?php if (isset($_GET['registered']) && $_GET['registered'] == 'success'): ?>
                <p style="color: green; text-align: center; margin-bottom: 1rem;">Registration successful! Please login.</p>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <p style="color: red; text-align: center; margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username">
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                <button type="submit" id="btn">Login</button> <br>
                <a href="register.php" class="button" style="text-align:center; margin-top: 10px;">Register</a>
            </form>
        </div>
    </div>
</body>
</html>