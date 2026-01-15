<?php
require_once 'functions.php';

// Auto-create default admin if no users exist
$allUsers = getAllUsers();
if (empty($allUsers)) {
    $defaultEmail = 'admin@timeclock.local';
    $defaultPassword = '1234';
    $defaultName = 'Administrator';
    addUser($defaultEmail, $defaultPassword, $defaultName, ROLE_ADMIN, '', '', '', '', '');
    $defaultCredentials = [
        'email' => $defaultEmail,
        'password' => $defaultPassword
    ];
} else {
    $defaultCredentials = null;
}

// If already logged in, redirect to appropriate page
if (isLoggedIn()) {
    if ($_SESSION['role'] == ROLE_ADMIN) {
        header('Location: /admin.php');
    } elseif ($_SESSION['role'] == ROLE_MANAGER) {
        header('Location: /manager.php');
    } else {
        header('Location: /employee.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = strtolower(trim($_POST['userid'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    
    if (empty($userid) || empty($password)) {
        $error = 'Please enter both user ID and password';
    } else {
        $user = getUser($userid);
        
        if ($user && $user['password'] == $password) {
            // Validate IP for Manager and Employee
            if (!validateIP($userid)) {
                $error = 'Access denied: You are not authorized to access from this IP address';
            } else {
                $_SESSION['userid'] = $user['userid'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                if ($user['role'] == ROLE_ADMIN) {
                    header('Location: /admin.php');
                } elseif ($user['role'] == ROLE_MANAGER) {
                    header('Location: /manager.php');
                } else {
                    header('Location: /employee.php');
                }
                exit;
            }
        } else {
            $error = 'Invalid user ID or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Clock - Login</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Time Clock System</h1>
            <h2>Login</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($defaultCredentials): ?>
                <div class="message success" style="margin-bottom: 20px;">
                    <h3 style="margin-bottom: 10px;">Default Admin Credentials Created</h3>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($defaultCredentials['email']); ?></p>
                    <p><strong>Password:</strong> <?php echo htmlspecialchars($defaultCredentials['password']); ?></p>
                    <p style="font-size: 0.9em; margin-top: 10px; color: #666;">Please login with these credentials and change the password for security.</p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="userid">User ID (Email):</label>
                    <input type="email" id="userid" name="userid" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password (4 digits):</label>
                    <input type="password" id="password" name="password" maxlength="4" pattern="[0-9]{4}" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
