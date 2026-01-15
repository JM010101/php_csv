<?php
require_once 'functions.php';
requireRole([ROLE_ADMIN]);

$userid = $_GET['userid'] ?? '';
if ($userid) {
    $user = getUser($userid);
    if ($user) {
        header('Content-Type: application/json');
        echo json_encode($user);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'User ID required']);
}
?>
