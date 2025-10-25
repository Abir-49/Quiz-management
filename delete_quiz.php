<?php
require_once 'config.php';
require_once 'functions.php';

requireInstructor();

$instructor_id = getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_quiz'])) {
    $quiz_id = intval($_POST['quiz_id']);
    
    // Delete quiz (CASCADE will handle related records)
    $result = deleteQuiz($quiz_id, $instructor_id);
    
    if ($result['success'] && $result['affected_rows'] > 0) {
        header("Location: instructor_dashboard.php?msg=quiz_deleted");
        exit();
    } else {
        header("Location: instructor_dashboard.php?error=delete_failed");
        exit();
    }
} else {
    header("Location: instructor_dashboard.php");
    exit();
}
?>