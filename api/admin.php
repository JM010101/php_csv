<?php
require_once 'functions.php';
requireRole([ROLE_ADMIN]);

$page = $_GET['page'] ?? 'dashboard';
$message = '';
$messageType = '';

// Handle user management
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $userid = strtolower(trim($_POST['userid']));
        $password = trim($_POST['password']);
        $name = trim($_POST['name']);
        $role = $_POST['role'];
        $ip1 = trim($_POST['ip1'] ?? '');
        $ip2 = trim($_POST['ip2'] ?? '');
        $ip3 = trim($_POST['ip3'] ?? '');
        $ip4 = trim($_POST['ip4'] ?? '');
        $ip5 = trim($_POST['ip5'] ?? '');
        
        if (getUser($userid)) {
            $message = 'User ID already exists';
            $messageType = 'error';
        } else {
            addUser($userid, $password, $name, $role, $ip1, $ip2, $ip3, $ip4, $ip5);
            $message = 'User added successfully';
            $messageType = 'success';
        }
    } elseif (isset($_POST['update_user'])) {
        $oldUserid = $_POST['old_userid'];
        $userid = strtolower(trim($_POST['userid']));
        $password = trim($_POST['password']);
        $name = trim($_POST['name']);
        $role = $_POST['role'];
        $ip1 = trim($_POST['ip1'] ?? '');
        $ip2 = trim($_POST['ip2'] ?? '');
        $ip3 = trim($_POST['ip3'] ?? '');
        $ip4 = trim($_POST['ip4'] ?? '');
        $ip5 = trim($_POST['ip5'] ?? '');
        
        updateUser($oldUserid, $userid, $password, $name, $role, $ip1, $ip2, $ip3, $ip4, $ip5);
        $message = 'User updated successfully';
        $messageType = 'success';
    } elseif (isset($_POST['delete_user'])) {
        $userid = $_POST['userid'];
        deleteUser($userid);
        $message = 'User deleted successfully';
        $messageType = 'success';
    } elseif (isset($_POST['update_schedule'])) {
        $userid = $_POST['userid'];
        $schedule = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($days as $day) {
            $schedule[$day] = [
                'clockin_from' => $_POST["{$day}_from"] ?? '08:00',
                'clockin_to' => $_POST["{$day}_to"] ?? '08:30',
                'clockout' => $_POST["{$day}_out"] ?? '17:00'
            ];
        }
        updateUserSchedule($userid, $schedule);
        $message = 'Schedule updated successfully';
        $messageType = 'success';
    } elseif (isset($_POST['add_time_record'])) {
        $userid = strtolower(trim($_POST['userid']));
        $user = getUser($userid);
        if ($user) {
            $date = $_POST['date'];
            $clockin_time = $_POST['clockin_time'];
            $clockout_time = $_POST['clockout_time'] ?? '';
            $hours = '';
            if ($clockout_time) {
                $hours = calculateHours($date, $clockin_time, $date, $clockout_time);
            }
            addTimeRecord($userid, $user['name'], $date, $clockin_time, $clockout_time, $hours);
            $message = 'Time record added successfully';
            $messageType = 'success';
        }
    } elseif (isset($_POST['update_time_record'])) {
        $oldUserid = $_POST['old_userid'];
        $oldDate = $_POST['old_date'];
        $oldClockin = $_POST['old_clockin'];
        $userid = strtolower(trim($_POST['userid']));
        $name = trim($_POST['name']);
        $date = $_POST['date'];
        $clockin_time = $_POST['clockin_time'];
        $clockout_time = $_POST['clockout_time'] ?? '';
        $hours = '';
        if ($clockout_time) {
            $hours = calculateHours($date, $clockin_time, $date, $clockout_time);
        }
        updateTimeRecord($oldUserid, $oldDate, $oldClockin, $userid, $name, $date, $clockin_time, $clockout_time, $hours);
        $message = 'Time record updated successfully';
        $messageType = 'success';
    } elseif (isset($_POST['delete_time_record'])) {
        $userid = $_POST['userid'];
        $date = $_POST['date'];
        $clockin = $_POST['clockin'];
        deleteTimeRecord($userid, $date, $clockin);
        $message = 'Time record deleted successfully';
        $messageType = 'success';
    }
}

// Get filters
$filters = [
    'userid' => $_GET['filter_userid'] ?? '',
    'name' => $_GET['filter_name'] ?? '',
    'date' => $_GET['filter_date'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

$timeRecords = getTimeRecords($filters);
$users = getAllUsers();

// Handle CSV export
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="time_records_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['User ID', 'Name', 'Date', 'Clock In', 'Clock Out', 'Hours']);
    
    foreach ($timeRecords as $record) {
        fputcsv($output, [$record['userid'], $record['name'], $record['date'], 
                         $record['clockin_time'], $record['clockout_time'], $record['hours']]);
    }
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <div class="container">
        <div class="admin-box">
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></p>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-nav">
                <a href="?page=dashboard" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">Time Records</a>
                <a href="?page=users" class="<?php echo $page == 'users' ? 'active' : ''; ?>">Users</a>
                <a href="?page=payroll" class="<?php echo $page == 'payroll' ? 'active' : ''; ?>">Payroll</a>
                <a href="/logout.php" class="logout-link">Logout</a>
            </div>
            
            <?php if ($page == 'dashboard'): ?>
                <div class="admin-section">
                    <h2>Time Records</h2>
                    
                    <div class="filters">
                        <form method="GET" action="">
                            <input type="hidden" name="page" value="dashboard">
                            <div class="filter-row">
                                <input type="text" name="filter_userid" placeholder="Filter by User ID" value="<?php echo htmlspecialchars($filters['userid']); ?>">
                                <input type="text" name="filter_name" placeholder="Search by Name" value="<?php echo htmlspecialchars($filters['name']); ?>">
                                <input type="date" name="filter_date" value="<?php echo htmlspecialchars($filters['date']); ?>">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="?page=dashboard" class="btn btn-secondary">Clear</a>
                                <a href="?page=dashboard&export=1<?php echo !empty($filters['userid']) ? '&filter_userid=' . urlencode($filters['userid']) : ''; ?><?php echo !empty($filters['name']) ? '&filter_name=' . urlencode($filters['name']) : ''; ?><?php echo !empty($filters['date']) ? '&filter_date=' . urlencode($filters['date']) : ''; ?>" class="btn btn-success">Export CSV</a>
                            </div>
                        </form>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Hours</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($timeRecords)): ?>
                                    <tr>
                                        <td colspan="7" class="no-data">No time records found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($timeRecords as $index => $record): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['userid']); ?></td>
                                            <td><?php echo htmlspecialchars($record['name']); ?></td>
                                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                                            <td><?php echo htmlspecialchars($record['clockin_time']); ?></td>
                                            <td><?php echo htmlspecialchars($record['clockout_time'] ?: 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($record['hours'] ?: 'N/A'); ?></td>
                                            <td>
                                                <button onclick="editTimeRecord('<?php echo htmlspecialchars($record['userid'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($record['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($record['date']); ?>', '<?php echo htmlspecialchars($record['clockin_time']); ?>', '<?php echo htmlspecialchars($record['clockout_time']); ?>', '<?php echo htmlspecialchars($record['hours']); ?>')" class="btn btn-small">Edit</button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this record?');">
                                                    <input type="hidden" name="delete_time_record" value="1">
                                                    <input type="hidden" name="userid" value="<?php echo htmlspecialchars($record['userid']); ?>">
                                                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($record['date']); ?>">
                                                    <input type="hidden" name="clockin" value="<?php echo htmlspecialchars($record['clockin_time']); ?>">
                                                    <button type="submit" class="btn btn-small btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <button onclick="showAddTimeRecord()" class="btn btn-primary">Add Time Record</button>
                </div>
                
            <?php elseif ($page == 'users'): ?>
                <div class="admin-section">
                    <h2>User Management</h2>
                    
                    <div class="search-box">
                        <input type="text" id="userSearch" placeholder="Search by name or user ID" onkeyup="filterUsers()">
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table" id="usersTable">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>IP Addresses</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['userid']); ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td><?php 
                                            $ips = array_filter([$user['ip1'], $user['ip2'], $user['ip3'], $user['ip4'], $user['ip5']]);
                                            echo htmlspecialchars(implode(', ', $ips));
                                        ?></td>
                                        <td>
                                            <button onclick="editUser('<?php echo htmlspecialchars($user['userid'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($user['role']); ?>', '<?php echo htmlspecialchars($user['ip1'] ?? ''); ?>', '<?php echo htmlspecialchars($user['ip2'] ?? ''); ?>', '<?php echo htmlspecialchars($user['ip3'] ?? ''); ?>', '<?php echo htmlspecialchars($user['ip4'] ?? ''); ?>', '<?php echo htmlspecialchars($user['ip5'] ?? ''); ?>')" class="btn btn-small">Edit</button>
                                            <button onclick="editSchedule('<?php echo htmlspecialchars($user['userid'], ENT_QUOTES); ?>')" class="btn btn-small">Schedule</button>
                                            <button onclick="cloneUser('<?php echo htmlspecialchars($user['userid'], ENT_QUOTES); ?>')" class="btn btn-small">Clone</button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user and all their time records?');">
                                                <input type="hidden" name="userid" value="<?php echo htmlspecialchars($user['userid']); ?>">
                                                <button type="submit" name="delete_user" class="btn btn-small btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <button onclick="showAddUser()" class="btn btn-primary">Add User</button>
                </div>
                
            <?php elseif ($page == 'payroll'): ?>
                <div class="admin-section">
                    <h2>Payroll Report</h2>
                    
                    <form method="GET" action="" class="payroll-form">
                        <input type="hidden" name="page" value="payroll">
                        <div class="filter-row">
                            <label>Date From:</label>
                            <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from']); ?>" required>
                            <label>Date To:</label>
                            <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to']); ?>" required>
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                        </div>
                    </form>
                    
                    <?php if (!empty($filters['date_from']) && !empty($filters['date_to'])): ?>
                        <?php
                        $payrollRecords = getTimeRecords(['date_from' => $filters['date_from'], 'date_to' => $filters['date_to']]);
                        // Sort by name, then by date
                        usort($payrollRecords, function($a, $b) {
                            $nameCompare = strcmp($a['name'], $b['name']);
                            if ($nameCompare != 0) return $nameCompare;
                            return strcmp($a['date'], $b['date']);
                        });
                        $totals = [];
                        foreach ($payrollRecords as $record) {
                            if (!empty($record['hours'])) {
                                if (!isset($totals[$record['name']])) {
                                    $totals[$record['name']] = 0;
                                }
                                $totals[$record['name']] += floatval($record['hours']);
                            }
                        }
                        ?>
                        
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Date</th>
                                        <th>Clock In</th>
                                        <th>Clock Out</th>
                                        <th>Hours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($payrollRecords)): ?>
                                        <tr>
                                            <td colspan="5" class="no-data">No records found for selected date range</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php 
                                        $currentName = '';
                                        foreach ($payrollRecords as $record): 
                                            if ($currentName != $record['name'] && $currentName != ''): ?>
                                                <tr class="subtotal">
                                                    <td colspan="4"><strong>Subtotal for <?php echo htmlspecialchars($currentName); ?>:</strong></td>
                                                    <td><strong><?php echo number_format($totals[$currentName] ?? 0, 2); ?> hours</strong></td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['date']); ?></td>
                                                <td><?php echo htmlspecialchars($record['clockin_time']); ?></td>
                                                <td><?php echo htmlspecialchars($record['clockout_time'] ?: 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($record['hours'] ?: '0.00'); ?></td>
                                            </tr>
                                            <?php $currentName = $record['name']; ?>
                                        <?php endforeach; ?>
                                        <?php if ($currentName != ''): ?>
                                            <tr class="subtotal">
                                                <td colspan="4"><strong>Subtotal for <?php echo htmlspecialchars($currentName); ?>:</strong></td>
                                                <td><strong><?php echo number_format($totals[$currentName] ?? 0, 2); ?> hours</strong></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modals -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addUserModal')">&times;</span>
            <h2>Add User</h2>
            <form method="POST" action="">
                <input type="hidden" name="add_user" value="1">
                <div class="form-group">
                    <label>User ID (Email):</label>
                    <input type="email" name="userid" required>
                </div>
                <div class="form-group">
                    <label>Password (4 digits):</label>
                    <input type="text" name="password" maxlength="4" pattern="[0-9]{4}" required>
                </div>
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Role:</label>
                    <select name="role" required>
                        <option value="<?php echo ROLE_ADMIN; ?>">Admin</option>
                        <option value="<?php echo ROLE_MANAGER; ?>">Manager</option>
                        <option value="<?php echo ROLE_EMPLOYEE; ?>">Employee</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>IP Address 1:</label>
                    <input type="text" name="ip1">
                </div>
                <div class="form-group">
                    <label>IP Address 2:</label>
                    <input type="text" name="ip2">
                </div>
                <div class="form-group">
                    <label>IP Address 3:</label>
                    <input type="text" name="ip3">
                </div>
                <div class="form-group">
                    <label>IP Address 4:</label>
                    <input type="text" name="ip4">
                </div>
                <div class="form-group">
                    <label>IP Address 5:</label>
                    <input type="text" name="ip5">
                </div>
                <button type="submit" class="btn btn-primary">Add User</button>
            </form>
        </div>
    </div>
    
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editUserModal')">&times;</span>
            <h2>Edit User</h2>
            <form method="POST" action="">
                <input type="hidden" name="update_user" value="1">
                <input type="hidden" name="old_userid" id="edit_old_userid">
                <div class="form-group">
                    <label>User ID (Email):</label>
                    <input type="email" name="userid" id="edit_userid" required>
                </div>
                <div class="form-group">
                    <label>Password (4 digits):</label>
                    <input type="text" name="password" id="edit_password" maxlength="4" pattern="[0-9]{4}" required>
                </div>
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Role:</label>
                    <select name="role" id="edit_role" required>
                        <option value="<?php echo ROLE_ADMIN; ?>">Admin</option>
                        <option value="<?php echo ROLE_MANAGER; ?>">Manager</option>
                        <option value="<?php echo ROLE_EMPLOYEE; ?>">Employee</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>IP Address 1:</label>
                    <input type="text" name="ip1" id="edit_ip1">
                </div>
                <div class="form-group">
                    <label>IP Address 2:</label>
                    <input type="text" name="ip2" id="edit_ip2">
                </div>
                <div class="form-group">
                    <label>IP Address 3:</label>
                    <input type="text" name="ip3" id="edit_ip3">
                </div>
                <div class="form-group">
                    <label>IP Address 4:</label>
                    <input type="text" name="ip4" id="edit_ip4">
                </div>
                <div class="form-group">
                    <label>IP Address 5:</label>
                    <input type="text" name="ip5" id="edit_ip5">
                </div>
                <button type="submit" class="btn btn-primary">Update User</button>
            </form>
        </div>
    </div>
    
    <div id="scheduleModal" class="modal">
        <div class="modal-content large">
            <span class="close" onclick="closeModal('scheduleModal')">&times;</span>
            <h2>Edit Schedule</h2>
            <form method="POST" action="">
                <input type="hidden" name="update_schedule" value="1">
                <input type="hidden" name="userid" id="schedule_userid">
                <div class="schedule-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Clock In From</th>
                                <th>Clock In To</th>
                                <th>Clock Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; ?>
                            <?php foreach ($days as $day): ?>
                                <tr>
                                    <td><?php echo $day; ?></td>
                                    <td><input type="time" name="<?php echo $day; ?>_from" id="schedule_<?php echo $day; ?>_from" required></td>
                                    <td><input type="time" name="<?php echo $day; ?>_to" id="schedule_<?php echo $day; ?>_to" required></td>
                                    <td><input type="time" name="<?php echo $day; ?>_out" id="schedule_<?php echo $day; ?>_out" required></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-primary">Update Schedule</button>
            </form>
        </div>
    </div>
    
    <div id="addTimeRecordModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addTimeRecordModal')">&times;</span>
            <h2>Add Time Record</h2>
            <form method="POST" action="">
                <input type="hidden" name="add_time_record" value="1">
                <div class="form-group">
                    <label>User ID:</label>
                    <select name="userid" required>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user['userid']); ?>"><?php echo htmlspecialchars($user['name'] . ' (' . $user['userid'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date:</label>
                    <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Clock In Time:</label>
                    <input type="time" name="clockin_time" required>
                </div>
                <div class="form-group">
                    <label>Clock Out Time (optional):</label>
                    <input type="time" name="clockout_time">
                </div>
                <button type="submit" class="btn btn-primary">Add Record</button>
            </form>
        </div>
    </div>
    
    <div id="editTimeRecordModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editTimeRecordModal')">&times;</span>
            <h2>Edit Time Record</h2>
            <form method="POST" action="">
                <input type="hidden" name="update_time_record" value="1">
                <input type="hidden" name="old_userid" id="edit_old_userid">
                <input type="hidden" name="old_date" id="edit_old_date">
                <input type="hidden" name="old_clockin" id="edit_old_clockin">
                <div class="form-group">
                    <label>User ID:</label>
                    <input type="text" name="userid" id="edit_time_userid" required>
                </div>
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" id="edit_time_name" required>
                </div>
                <div class="form-group">
                    <label>Date:</label>
                    <input type="date" name="date" id="edit_time_date" required>
                </div>
                <div class="form-group">
                    <label>Clock In Time:</label>
                    <input type="time" name="clockin_time" id="edit_time_clockin" required>
                </div>
                <div class="form-group">
                    <label>Clock Out Time:</label>
                    <input type="time" name="clockout_time" id="edit_time_clockout">
                </div>
                <button type="submit" class="btn btn-primary">Update Record</button>
            </form>
        </div>
    </div>
    
    <script>
        function showAddUser() {
            document.getElementById('addUserModal').style.display = 'block';
        }
        
        function editUser(userid, name, role, ip1, ip2, ip3, ip4, ip5) {
            document.getElementById('edit_old_userid').value = userid;
            document.getElementById('edit_userid').value = userid;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_ip1').value = ip1 || '';
            document.getElementById('edit_ip2').value = ip2 || '';
            document.getElementById('edit_ip3').value = ip3 || '';
            document.getElementById('edit_ip4').value = ip4 || '';
            document.getElementById('edit_ip5').value = ip5 || '';
            document.getElementById('editUserModal').style.display = 'block';
        }
        
        function editSchedule(userid) {
            document.getElementById('schedule_userid').value = userid;
            // Load schedule via AJAX or set defaults
            <?php
            // We'll load schedule when modal opens
            ?>
            fetch('/get_schedule.php?userid=' + encodeURIComponent(userid))
                .then(response => response.json())
                .then(data => {
                    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    days.forEach(day => {
                        if (data[day]) {
                            document.getElementById('schedule_' + day + '_from').value = data[day].clockin_from;
                            document.getElementById('schedule_' + day + '_to').value = data[day].clockin_to;
                            document.getElementById('schedule_' + day + '_out').value = data[day].clockout;
                        }
                    });
                })
                .catch(error => console.error('Error:', error));
            document.getElementById('scheduleModal').style.display = 'block';
        }
        
        function cloneUser(userid) {
            fetch('/get_user.php?userid=' + encodeURIComponent(userid))
                .then(response => response.json())
                .then(data => {
                    // Use add user modal instead
                    document.querySelector('#addUserModal input[name="userid"]').value = '';
                    document.querySelector('#addUserModal input[name="name"]').value = data.name + ' (Copy)';
                    document.querySelector('#addUserModal select[name="role"]').value = data.role;
                    document.querySelector('#addUserModal input[name="ip1"]').value = data.ip1 || '';
                    document.querySelector('#addUserModal input[name="ip2"]').value = data.ip2 || '';
                    document.querySelector('#addUserModal input[name="ip3"]').value = data.ip3 || '';
                    document.querySelector('#addUserModal input[name="ip4"]').value = data.ip4 || '';
                    document.querySelector('#addUserModal input[name="ip5"]').value = data.ip5 || '';
                    document.getElementById('addUserModal').style.display = 'block';
                    
                    // Also clone schedule
                    fetch('/get_schedule.php?userid=' + encodeURIComponent(userid))
                        .then(response => response.json())
                        .then(schedule => {
                            // Store schedule data for after user creation
                            window.clonedSchedule = schedule;
                        });
                });
        }
        
        function showAddTimeRecord() {
            document.getElementById('addTimeRecordModal').style.display = 'block';
        }
        
        function editTimeRecord(userid, name, date, clockin, clockout, hours) {
            document.getElementById('edit_time_userid').value = userid;
            document.getElementById('edit_time_name').value = name;
            document.getElementById('edit_time_date').value = date;
            document.getElementById('edit_time_clockin').value = clockin;
            document.getElementById('edit_time_clockout').value = clockout || '';
            // Store original values for update
            document.getElementById('edit_old_userid').value = userid;
            document.getElementById('edit_old_date').value = date;
            document.getElementById('edit_old_clockin').value = clockin;
            document.getElementById('editTimeRecordModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function filterUsers() {
            const input = document.getElementById('userSearch');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('usersTable');
            const tr = table.getElementsByTagName('tr');
            
            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                tr[i].style.display = found ? '' : 'none';
            }
        }
        
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let i = 0; i < modals.length; i++) {
                if (event.target == modals[i]) {
                    modals[i].style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>
