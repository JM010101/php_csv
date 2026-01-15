<?php
/**
 * Setup script to create the first admin user
 * Run this once to create your initial admin account
 * Delete this file after setup for security
 */

require_once 'config.php';
require_once 'functions.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = strtolower(trim($_POST['userid'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $name = trim($_POST['name'] ?? '');
    
    if (empty($userid) || empty($password) || empty($name)) {
        $message = 'All fields are required';
        $messageType = 'error';
    } elseif (!filter_var($userid, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address';
        $messageType = 'error';
    } elseif (strlen($password) != 4 || !ctype_digit($password)) {
        $message = 'Password must be exactly 4 digits';
        $messageType = 'error';
    } elseif (getUser($userid)) {
        $message = 'User already exists';
        $messageType = 'error';
    } else {
        addUser($userid, $password, $name, ROLE_ADMIN, '', '', '', '', '');
        $message = 'Admin user created successfully! You can now login.';
        $messageType = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Create Admin User</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Time Clock Setup</h1>
            <h2>Create Admin User</h2>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($messageType == 'success'): ?>
                <p><a href="/" class="btn btn-primary">Go to Login</a></p>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="userid">Admin Email:</label>
                        <input type="email" id="userid" name="userid" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password (4 digits):</label>
                        <input type="text" id="password" name="password" maxlength="4" pattern="[0-9]{4}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Create Admin User</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
