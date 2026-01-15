<?php
// Configuration file
session_start();

// Determine if running on Vercel (serverless environment)
$isVercel = isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']);
$dataDir = $isVercel ? '/tmp/timeclock' : 'data';

// CSV file paths
define('DATA_DIR', $dataDir);
define('USERS_CSV', $dataDir . '/users.csv');
define('TIMECLOCK_CSV', $dataDir . '/timeclock.csv');

// Session timeout (120 seconds = 2 minutes)
define('SESSION_TIMEOUT', 120);

// User roles
define('ROLE_ADMIN', 'Admin');
define('ROLE_MANAGER', 'Manager');
define('ROLE_EMPLOYEE', 'Employee');

// Create data directory if it doesn't exist
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0777, true);
}

// Create schedules subdirectory
$schedulesDir = $dataDir . '/schedules';
if (!file_exists($schedulesDir)) {
    mkdir($schedulesDir, 0777, true);
}

// Initialize CSV files with headers if they don't exist
if (!file_exists(USERS_CSV)) {
    $fp = fopen(USERS_CSV, 'w');
    fputcsv($fp, ['userid', 'password', 'name', 'role', 'ip1', 'ip2', 'ip3', 'ip4', 'ip5']);
    fclose($fp);
}

if (!file_exists(TIMECLOCK_CSV)) {
    $fp = fopen(TIMECLOCK_CSV, 'w');
    fputcsv($fp, ['userid', 'name', 'date', 'clockin_time', 'clockout_time', 'hours']);
    fclose($fp);
}
?>
