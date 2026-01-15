<?php
// Simple test to see if admin page can load
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Page</h1>";
echo "<p>If you see this, PHP is working.</p>";

require_once 'functions.php';
echo "<p>Functions loaded.</p>";

if (isLoggedIn()) {
    echo "<p>You are logged in as: " . htmlspecialchars($_SESSION['name'] ?? 'Unknown') . "</p>";
    echo "<p>Your role is: " . htmlspecialchars($_SESSION['role'] ?? 'Unknown') . "</p>";
} else {
    echo "<p>You are NOT logged in.</p>";
    echo "<p><a href='/'>Go to login</a></p>";
}

echo "<p><a href='/admin.php'>Try admin.php</a></p>";
?>
