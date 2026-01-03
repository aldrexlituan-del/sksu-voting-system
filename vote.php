<?php
include '../auth.php';
include '../db.php';

if ($_SESSION['role'] !== 'student') die("Access Denied");

$student_id = $_SESSION['user_id'];

if (!isset($_POST['vote'])) {
    header("Location: dashboard.php");
    exit();
}

foreach ($_POST['vote'] as $position_id => $candidate_id) {

    $check = $conn->query("
        SELECT 1 FROM votes 
        WHERE student_id=$student_id AND position_id=$position_id
    ");

    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("
            INSERT INTO votes (student_id, position_id, candidate_id)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iii", $student_id, $position_id, $candidate_id);
        $stmt->execute();
    }
}

header("Location: dashboard.php");
exit();
