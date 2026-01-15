<?php
require_once 'functions.php';
requireRole([ROLE_EMPLOYEE]);

$userid = $_SESSION['userid'];
$name = $_SESSION['name'];
$user = getUser($userid);
$clockedIn = isClockedIn($userid);
$currentRecord = getCurrentClockIn($userid);
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['clockin'])) {
        if ($clockedIn) {
            $message = 'You are already clocked in. Please clock out first.';
            $messageType = 'error';
        } else {
            $canClockIn = canClockIn($userid);
            if ($canClockIn['allowed']) {
                $date = date('Y-m-d');
                $time = date('H:i');
                addTimeRecord($userid, $name, $date, $time, '', '');
                $message = 'Clocked in successfully at ' . $time;
                $messageType = 'success';
                $clockedIn = true;
                
                // Auto logout after 5 seconds
                header("refresh:5;url=logout.php");
            } else {
                $message = $canClockIn['message'];
                $messageType = 'error';
            }
        }
    } elseif (isset($_POST['clockout'])) {
        if (!$clockedIn) {
            $message = 'You are not clocked in. Please clock in first.';
            $messageType = 'error';
        } else {
            $canClockOut = canClockOut($userid);
            if ($canClockOut['allowed']) {
                $clockoutTime = date('H:i');
                $clockoutDate = date('Y-m-d');
                
                // Update the current record
                $records = getTimeRecords(['userid' => $userid]);
                foreach ($records as $index => $record) {
                    if (empty($record['clockout_time']) && 
                        ($record['date'] == $clockoutDate || $record['date'] == date('Y-m-d', strtotime('-1 day')))) {
                        $hours = calculateHours($record['date'], $record['clockin_time'], $clockoutDate, $clockoutTime);
                        updateTimeRecord($index, $record['userid'], $record['name'], $record['date'], 
                                       $record['clockin_time'], $clockoutTime, $hours);
                        break;
                    }
                }
                
                $message = 'Clocked out successfully at ' . $clockoutTime;
                $messageType = 'success';
                $clockedIn = false;
                
                // Auto logout after 5 seconds
                header("refresh:5;url=logout.php");
            } else {
                $message = $canClockOut['message'];
                $messageType = 'error';
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
    <title>Employee Time Clock</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="employee-box">
            <h1>Time Clock</h1>
            <h2>Welcome, <?php echo htmlspecialchars($name); ?></h2>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="status">
                <p><strong>Status:</strong> <?php echo $clockedIn ? 'Clocked In' : 'Clocked Out'; ?></p>
                <?php if ($clockedIn && $currentRecord): ?>
                    <p><strong>Clock In Time:</strong> <?php echo htmlspecialchars($currentRecord['clockin_time']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($currentRecord['date']); ?></p>
                <?php endif; ?>
            </div>
            
            <form method="POST" action="" class="clock-form">
                <?php if (!$clockedIn): ?>
                    <button type="submit" name="clockin" class="btn btn-success btn-large">CLOCK IN</button>
                <?php else: ?>
                    <button type="submit" name="clockout" class="btn btn-danger btn-large">CLOCK OUT</button>
                <?php endif; ?>
            </form>
            
            <div class="logout-link">
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
