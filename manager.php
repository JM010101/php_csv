<?php
require_once 'functions.php';
requireRole([ROLE_MANAGER]);

$clockedInEmployees = getClockedInEmployees();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager - Clocked In Employees</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="manager-box">
            <h1>Manager Dashboard</h1>
            <h2>Workers Currently Clocked In</h2>
            
            <div class="header-actions">
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Clock In Time</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clockedInEmployees)): ?>
                            <tr>
                                <td colspan="3" class="no-data">No employees currently clocked in</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clockedInEmployees as $employee): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['clockin_time']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="auto-refresh">
                <p>Page auto-refreshes every 30 seconds</p>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
