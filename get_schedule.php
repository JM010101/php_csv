<?php
require_once 'functions.php';
requireRole([ROLE_ADMIN]);

$userid = $_GET['userid'] ?? '';
if ($userid) {
    $schedule = getUserSchedule($userid);
    header('Content-Type: application/json');
    echo json_encode($schedule);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'User ID required']);
}
?>
