<?php
require_once 'config.php';

// Get current user's IP address
function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// Check if user is logged in
function isLoggedIn() {
    // Session should already be started by config.php
    // Don't start it here to avoid "headers already sent" errors
    return isset($_SESSION['userid']) && isset($_SESSION['role']);
}

// Check session timeout for Admin and Manager
function checkSessionTimeout() {
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    // Check if session has timed out
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        if (isset($_SESSION['role']) && ($_SESSION['role'] == ROLE_ADMIN || $_SESSION['role'] == ROLE_MANAGER)) {
            // Clear session but don't redirect here (let requireRole handle it)
            session_unset();
            session_destroy();
            return false;
        }
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

// Validate IP address for Manager and Employee
function validateIP($userid) {
    $user = getUser($userid);
    if (!$user) return false;
    
    // Admin can access from any IP
    if ($user['role'] == ROLE_ADMIN) {
        return true;
    }
    
    // Manager and Employee must be from allowed IPs
    $currentIP = getClientIP();
    $allowedIPs = [$user['ip1'], $user['ip2'], $user['ip3'], $user['ip4'], $user['ip5']];
    
    return in_array($currentIP, $allowedIPs);
}

// Get user from CSV
function getUser($userid) {
    if (!file_exists(USERS_CSV)) return null;
    
    $fp = fopen(USERS_CSV, 'r');
    fgetcsv($fp); // Skip header
    
    while (($row = fgetcsv($fp)) !== FALSE) {
        if (strtolower($row[0]) == strtolower($userid)) {
            fclose($fp);
            return [
                'userid' => $row[0],
                'password' => $row[1],
                'name' => $row[2],
                'role' => $row[3],
                'ip1' => $row[4] ?? '',
                'ip2' => $row[5] ?? '',
                'ip3' => $row[6] ?? '',
                'ip4' => $row[7] ?? '',
                'ip5' => $row[8] ?? ''
            ];
        }
    }
    fclose($fp);
    return null;
}

// Get all users
function getAllUsers() {
    if (!file_exists(USERS_CSV)) {
        // Initialize file if it doesn't exist
        $fp = fopen(USERS_CSV, 'w');
        fputcsv($fp, ['userid', 'password', 'name', 'role', 'ip1', 'ip2', 'ip3', 'ip4', 'ip5']);
        fclose($fp);
        return [];
    }
    
    $users = [];
    $fp = @fopen(USERS_CSV, 'r');
    if ($fp === false) {
        return [];
    }
    
    // Skip header
    $header = fgetcsv($fp);
    if ($header === false) {
        fclose($fp);
        return [];
    }
    
    while (($row = fgetcsv($fp)) !== FALSE) {
        if (count($row) >= 4) {
            $users[] = [
                'userid' => $row[0] ?? '',
                'password' => $row[1] ?? '',
                'name' => $row[2] ?? '',
                'role' => $row[3] ?? '',
                'ip1' => $row[4] ?? '',
                'ip2' => $row[5] ?? '',
                'ip3' => $row[6] ?? '',
                'ip4' => $row[7] ?? '',
                'ip5' => $row[8] ?? ''
            ];
        }
    }
    fclose($fp);
    return $users;
}

// Add user
function addUser($userid, $password, $name, $role, $ip1 = '', $ip2 = '', $ip3 = '', $ip4 = '', $ip5 = '') {
    $userid = strtolower($userid);
    $fp = fopen(USERS_CSV, 'a');
    fputcsv($fp, [$userid, $password, $name, $role, $ip1, $ip2, $ip3, $ip4, $ip5]);
    fclose($fp);
    
    // Create schedule file for user
    createUserSchedule($userid);
}

// Update user
function updateUser($oldUserid, $userid, $password, $name, $role, $ip1 = '', $ip2 = '', $ip3 = '', $ip4 = '', $ip5 = '') {
    $oldUserid = strtolower($oldUserid);
    $userid = strtolower($userid);
    
    $users = getAllUsers();
    $fp = fopen(USERS_CSV, 'w');
    fputcsv($fp, ['userid', 'password', 'name', 'role', 'ip1', 'ip2', 'ip3', 'ip4', 'ip5']);
    
    foreach ($users as $user) {
        if (strtolower($user['userid']) == $oldUserid) {
            fputcsv($fp, [$userid, $password, $name, $role, $ip1, $ip2, $ip3, $ip4, $ip5]);
        } else {
            fputcsv($fp, [$user['userid'], $user['password'], $user['name'], $user['role'], 
                         $user['ip1'] ?? '', $user['ip2'] ?? '', $user['ip3'] ?? '', 
                         $user['ip4'] ?? '', $user['ip5'] ?? '']);
        }
    }
    fclose($fp);
    
    // Update schedule file if userid changed
    if ($oldUserid != $userid) {
        $oldScheduleFile = DATA_DIR . "/schedules/{$oldUserid}.csv";
        $newScheduleFile = DATA_DIR . "/schedules/{$userid}.csv";
        if (file_exists($oldScheduleFile)) {
            rename($oldScheduleFile, $newScheduleFile);
        }
    }
}

// Delete user
function deleteUser($userid) {
    $userid = strtolower($userid);
    
    $users = getAllUsers();
    $fp = fopen(USERS_CSV, 'w');
    fputcsv($fp, ['userid', 'password', 'name', 'role', 'ip1', 'ip2', 'ip3', 'ip4', 'ip5']);
    
    foreach ($users as $user) {
        if (strtolower($user['userid']) != $userid) {
            fputcsv($fp, [$user['userid'], $user['password'], $user['name'], $user['role'], 
                         $user['ip1'] ?? '', $user['ip2'] ?? '', $user['ip3'] ?? '', 
                         $user['ip4'] ?? '', $user['ip5'] ?? '']);
        }
    }
    fclose($fp);
    
    // Delete user's time records
    deleteUserTimeRecords($userid);
    
    // Delete schedule file
    $scheduleFile = DATA_DIR . "/schedules/{$userid}.csv";
    if (file_exists($scheduleFile)) {
        unlink($scheduleFile);
    }
}

// Create user schedule file
function createUserSchedule($userid) {
    $schedulesDir = DATA_DIR . '/schedules';
    if (!file_exists($schedulesDir)) {
        mkdir($schedulesDir, 0777, true);
    }
    
    $scheduleFile = $schedulesDir . "/{$userid}.csv";
    if (!file_exists($scheduleFile)) {
        $fp = fopen($scheduleFile, 'w');
        fputcsv($fp, ['day', 'clockin_from', 'clockin_to', 'clockout']);
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($days as $day) {
            fputcsv($fp, [$day, '08:00', '08:30', '17:00']);
        }
        fclose($fp);
    }
}

// Get user schedule
function getUserSchedule($userid) {
    $userid = strtolower($userid);
    $scheduleFile = DATA_DIR . "/schedules/{$userid}.csv";
    
    if (!file_exists($scheduleFile)) {
        createUserSchedule($userid);
    }
    
    $schedule = [];
    $fp = fopen($scheduleFile, 'r');
    fgetcsv($fp); // Skip header
    
    while (($row = fgetcsv($fp)) !== FALSE) {
        if (count($row) >= 4) {
            $schedule[$row[0]] = [
                'clockin_from' => $row[1],
                'clockin_to' => $row[2],
                'clockout' => $row[3]
            ];
        }
    }
    fclose($fp);
    return $schedule;
}

// Update user schedule
function updateUserSchedule($userid, $schedule) {
    $userid = strtolower($userid);
    $scheduleFile = DATA_DIR . "/schedules/{$userid}.csv";
    
    $fp = fopen($scheduleFile, 'w');
    fputcsv($fp, ['day', 'clockin_from', 'clockin_to', 'clockout']);
    
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    foreach ($days as $day) {
        $daySchedule = $schedule[$day] ?? ['clockin_from' => '08:00', 'clockin_to' => '08:30', 'clockout' => '17:00'];
        fputcsv($fp, [$day, $daySchedule['clockin_from'], $daySchedule['clockin_to'], $daySchedule['clockout']]);
    }
    fclose($fp);
}

// Get time records
function getTimeRecords($filters = []) {
    if (!file_exists(TIMECLOCK_CSV)) {
        // Initialize if doesn't exist
        $fp = fopen(TIMECLOCK_CSV, 'w');
        fputcsv($fp, ['userid', 'name', 'date', 'clockin_time', 'clockout_time', 'hours']);
        fclose($fp);
        return [];
    }
    
    $records = [];
    $fp = fopen(TIMECLOCK_CSV, 'r');
    fgetcsv($fp); // Skip header
    
    while (($row = fgetcsv($fp)) !== FALSE) {
        if (count($row) >= 6) {
            $record = [
                'userid' => $row[0],
                'name' => $row[1],
                'date' => $row[2],
                'clockin_time' => $row[3],
                'clockout_time' => $row[4],
                'hours' => $row[5]
            ];
            
            // Apply filters
            $match = true;
            if (!empty($filters['userid']) && strtolower($record['userid']) != strtolower($filters['userid'])) $match = false;
            if (!empty($filters['name']) && stripos($record['name'], $filters['name']) === false) $match = false;
            if (!empty($filters['date']) && $record['date'] != $filters['date']) $match = false;
            if (!empty($filters['date_from']) && $record['date'] < $filters['date_from']) $match = false;
            if (!empty($filters['date_to']) && $record['date'] > $filters['date_to']) $match = false;
            
            if ($match) {
                $records[] = $record;
            }
        }
    }
    fclose($fp);
    
    return $records;
}

// Add time record
function addTimeRecord($userid, $name, $date, $clockin_time, $clockout_time = '', $hours = '') {
    $userid = strtolower($userid);
    $fp = fopen(TIMECLOCK_CSV, 'a');
    fputcsv($fp, [$userid, $name, $date, $clockin_time, $clockout_time, $hours]);
    fclose($fp);
}

// Update time record by unique identifier
function updateTimeRecord($oldUserid, $oldDate, $oldClockin, $userid, $name, $date, $clockin_time, $clockout_time, $hours) {
    $oldUserid = strtolower($oldUserid);
    $userid = strtolower($userid);
    
    if (!file_exists(TIMECLOCK_CSV)) return;
    
    $records = [];
    $fp = fopen(TIMECLOCK_CSV, 'r');
    fgetcsv($fp); // Skip header
    
    while (($row = fgetcsv($fp)) !== FALSE) {
        if (count($row) >= 6) {
            // Check if this is the record to update
            if (strtolower($row[0]) == $oldUserid && $row[2] == $oldDate && $row[3] == $oldClockin) {
                $records[] = [
                    'userid' => $userid,
                    'name' => $name,
                    'date' => $date,
                    'clockin_time' => $clockin_time,
                    'clockout_time' => $clockout_time,
                    'hours' => $hours
                ];
            } else {
                $records[] = [
                    'userid' => $row[0],
                    'name' => $row[1],
                    'date' => $row[2],
                    'clockin_time' => $row[3],
                    'clockout_time' => $row[4],
                    'hours' => $row[5]
                ];
            }
        }
    }
    fclose($fp);
    
    // Write back
    $fp = fopen(TIMECLOCK_CSV, 'w');
    fputcsv($fp, ['userid', 'name', 'date', 'clockin_time', 'clockout_time', 'hours']);
    
    foreach ($records as $record) {
        fputcsv($fp, [$record['userid'], $record['name'], $record['date'], 
                     $record['clockin_time'], $record['clockout_time'], $record['hours']]);
    }
    fclose($fp);
}

// Delete time record by unique identifier
function deleteTimeRecord($userid, $date, $clockin) {
    $userid = strtolower($userid);
    
    if (!file_exists(TIMECLOCK_CSV)) return;
    
    $records = [];
    $fp = fopen(TIMECLOCK_CSV, 'r');
    fgetcsv($fp); // Skip header
    
    while (($row = fgetcsv($fp)) !== FALSE) {
        if (count($row) >= 6) {
            // Skip the record to delete
            if (strtolower($row[0]) == $userid && $row[2] == $date && $row[3] == $clockin) {
                continue;
            }
            $records[] = [
                'userid' => $row[0],
                'name' => $row[1],
                'date' => $row[2],
                'clockin_time' => $row[3],
                'clockout_time' => $row[4],
                'hours' => $row[5]
            ];
        }
    }
    fclose($fp);
    
    // Write back
    $fp = fopen(TIMECLOCK_CSV, 'w');
    fputcsv($fp, ['userid', 'name', 'date', 'clockin_time', 'clockout_time', 'hours']);
    
    foreach ($records as $record) {
        fputcsv($fp, [$record['userid'], $record['name'], $record['date'], 
                     $record['clockin_time'], $record['clockout_time'], $record['hours']]);
    }
    fclose($fp);
}

// Delete all time records for a user
function deleteUserTimeRecords($userid) {
    $userid = strtolower($userid);
    $records = getTimeRecords();
    
    $fp = fopen(TIMECLOCK_CSV, 'w');
    fputcsv($fp, ['userid', 'name', 'date', 'clockin_time', 'clockout_time', 'hours']);
    
    foreach ($records as $record) {
        if (strtolower($record['userid']) != $userid) {
            fputcsv($fp, [$record['userid'], $record['name'], $record['date'], 
                         $record['clockin_time'], $record['clockout_time'], $record['hours']]);
        }
    }
    fclose($fp);
}

// Check if user is currently clocked in
function isClockedIn($userid) {
    $userid = strtolower($userid);
    $records = getTimeRecords(['userid' => $userid]);
    
    // Get today's date
    $today = date('Y-m-d');
    
    // Check for open records (no clockout time) from today or yesterday
    foreach ($records as $record) {
        if (empty($record['clockout_time'])) {
            // Check if it's from today or yesterday (for overnight shifts)
            $recordDate = $record['date'];
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            
            if ($recordDate == $today || $recordDate == $yesterday) {
                return true;
            }
        }
    }
    
    return false;
}

// Get current clock-in record
function getCurrentClockIn($userid) {
    $userid = strtolower($userid);
    $records = getTimeRecords(['userid' => $userid]);
    
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    foreach ($records as $record) {
        if (empty($record['clockout_time'])) {
            if ($record['date'] == $today || $record['date'] == $yesterday) {
                return $record;
            }
        }
    }
    
    return null;
}

// Check if user can clock in (within window)
function canClockIn($userid) {
    $userid = strtolower($userid);
    $schedule = getUserSchedule($userid);
    
    $dayOfWeek = date('l'); // Monday, Tuesday, etc.
    $currentTime = date('H:i');
    
    if (!isset($schedule[$dayOfWeek])) {
        return ['allowed' => false, 'message' => 'No schedule found for today'];
    }
    
    $daySchedule = $schedule[$dayOfWeek];
    $clockinFrom = $daySchedule['clockin_from'];
    $clockinTo = $daySchedule['clockin_to'];
    
    if ($currentTime < $clockinFrom || $currentTime > $clockinTo) {
        return ['allowed' => false, 'message' => 'You are late and cannot work today. Only your manager can override this.'];
    }
    
    return ['allowed' => true, 'message' => ''];
}

// Check if user can clock out
function canClockOut($userid) {
    $userid = strtolower($userid);
    $schedule = getUserSchedule($userid);
    
    $dayOfWeek = date('l');
    $currentTime = date('H:i');
    
    if (!isset($schedule[$dayOfWeek])) {
        return ['allowed' => false, 'message' => 'No schedule found for today'];
    }
    
    $daySchedule = $schedule[$dayOfWeek];
    $defaultClockout = $daySchedule['clockout'];
    
    if ($currentTime > $defaultClockout) {
        return ['allowed' => false, 'message' => 'Cannot clock out after default clockout time'];
    }
    
    return ['allowed' => true, 'message' => ''];
}

// Calculate hours between two times (handles overnight)
function calculateHours($date1, $time1, $date2, $time2) {
    $datetime1 = new DateTime("{$date1} {$time1}");
    $datetime2 = new DateTime("{$date2} {$time2}");
    
    // If time2 is earlier than time1, assume next day
    if ($datetime2 < $datetime1) {
        $datetime2->modify('+1 day');
    }
    
    $diff = $datetime1->diff($datetime2);
    $hours = $diff->h + ($diff->i / 60);
    
    return number_format($hours, 2);
}

// Get currently clocked in employees
function getClockedInEmployees() {
    $records = getTimeRecords();
    $clockedIn = [];
    
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    foreach ($records as $record) {
        if (empty($record['clockout_time'])) {
            if ($record['date'] == $today || $record['date'] == $yesterday) {
                $clockedIn[] = $record;
            }
        }
    }
    
    return $clockedIn;
}

// Logout function
function logout() {
    session_unset();
    session_destroy();
    header('Location: /');
    exit;
}

// Require specific role
function requireRole($allowedRoles) {
    // Session should already be started by config.php
    // Don't start it here to avoid "headers already sent" errors
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        header('Location: /');
        exit;
    }
    
    // Check session timeout first (may clear session)
    $sessionValid = checkSessionTimeout();
    if (!$sessionValid) {
        header('Location: /');
        exit;
    }
    
    // Check if user has required role
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
        header('Location: /');
        exit;
    }
}
?>
