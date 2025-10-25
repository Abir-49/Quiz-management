<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();

$quiz_id = intval($_GET['quiz_id'] ?? 0);
$student_id = intval($_GET['student_id'] ?? getUserId());

// Check permissions
if (isInstructor()) {
    // Instructor can view any student's answer script
    $instructor_id = getUserId();
    $quiz_details = getQuizDetails($quiz_id);
    
    // Verify instructor owns this quiz
    if ($quiz_details['t_id'] != $instructor_id) {
        header("Location: instructor_dashboard.php?error=access_denied");
        exit();
    }
} else {
    // Student can only view their own answer script after quiz expires
    if ($student_id != getUserId()) {
        header("Location: student_dashboard.php?error=access_denied");
        exit();
    }
    
    if (!canViewAnswerScript($quiz_id, $student_id)) {
        header("Location: student_dashboard.php?error=quiz_not_expired");
        exit();
    }
}

$answer_script = getStudentAnswerScript($quiz_id, $student_id);
$quiz_details = getQuizDetails($quiz_id);

// Get student details
$db = new Database();
$student_query = "SELECT name, roll, email FROM student WHERE id = ?";
$student_result = $db->select($student_query, [$student_id], "i");
$student = $student_result->fetch_assoc();

// Calculate total
$total_marks = 0;
$obtained_marks = 0;
foreach ($answer_script as $answer) {
    $total_marks += $answer['marks'];
    $obtained_marks += $answer['marks_obtained'];
}
$percentage = calculatePercentage($obtained_marks, $total_marks);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Answer Script - <?php echo htmlspecialchars($quiz_details['q_name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>Quiz Management System</h1>
            <div class="nav-links">
                <a href="<?php echo isInstructor() ? 'quiz_results.php?id=' . $quiz_id : 'student_dashboard.php'; ?>" class="btn btn-secondary">Back</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="answer-script-header">
            <h2><?php echo htmlspecialchars($quiz_details['q_name']); ?></h2>
            <div class="student-info">
                <p><strong>Student:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
                <p><strong>Roll:</strong> <?php echo htmlspecialchars($student['roll']); ?></p>
                <p><strong>Score:</strong> <?php echo $obtained_marks; ?> / <?php echo $total_marks; ?></p>
                <p><strong>Percentage:</strong> 
                    <span class="badge <?php echo $percentage >= 80 ? 'badge-success' : ($percentage >= 60 ? 'badge-warning' : 'badge-danger'); ?>">
                        <?php echo number_format($percentage, 2); ?>%
                    </span>
                </p>
            </div>
        </div>

        <?php foreach ($answer_script as $answer): ?>
        <div class="answer-card <?php echo ($answer['student_ans'] == $answer['correct_ans']) ? 'correct' : 'incorrect'; ?>">
            <div class="answer-header">
                <h4>Question <?php echo $answer['q_no']; ?></h4>
                <span class="marks-badge">
                    <?php echo $answer['marks_obtained']; ?> / <?php echo $answer['marks']; ?> marks
                </span>
            </div>
            
            <p class="question-text"><?php echo htmlspecialchars($answer['question']); ?></p>
            
            <div class="answer-options">
                <div class="option <?php echo ($answer['correct_ans'] == 'A') ? 'correct-option' : (($answer['student_ans'] == 'A') ? 'wrong-option' : ''); ?>">
                    <strong>A.</strong> <?php echo htmlspecialchars($answer['A']); ?>
                    <?php if ($answer['correct_ans'] == 'A'): ?>
                        <span class="badge badge-success">Correct Answer</span>
                    <?php endif; ?>
                    <?php if ($answer['student_ans'] == 'A' && $answer['correct_ans'] != 'A'): ?>
                        <span class="badge badge-danger">Your Answer</span>
                    <?php endif; ?>
                </div>
                
                <div class="option <?php echo ($answer['correct_ans'] == 'B') ? 'correct-option' : (($answer['student_ans'] == 'B') ? 'wrong-option' : ''); ?>">
                    <strong>B.</strong> <?php echo htmlspecialchars($answer['B']); ?>
                    <?php if ($answer['correct_ans'] == 'B'): ?>
                        <span class="badge badge-success">Correct Answer</span>
                    <?php endif; ?>
                    <?php if ($answer['student_ans'] == 'B' && $answer['correct_ans'] != 'B'): ?>
                        <span class="badge badge-danger">Your Answer</span>
                    <?php endif; ?>
                </div>
                
                <div class="option <?php echo ($answer['correct_ans'] == 'C') ? 'correct-option' : (($answer['student_ans'] == 'C') ? 'wrong-option' : ''); ?>">
                    <strong>C.</strong> <?php echo htmlspecialchars($answer['C']); ?>
                    <?php if ($answer['correct_ans'] == 'C'): ?>
                        <span class="badge badge-success">Correct Answer</span>
                    <?php endif; ?>
                    <?php if ($answer['student_ans'] == 'C' && $answer['correct_ans'] != 'C'): ?>
                        <span class="badge badge-danger">Your Answer</span>
                    <?php endif; ?>
                </div>
                
                <div class="option <?php echo ($answer['correct_ans'] == 'D') ? 'correct-option' : (($answer['student_ans'] == 'D') ? 'wrong-option' : ''); ?>">
                    <strong>D.</strong> <?php echo htmlspecialchars($answer['D']); ?>
                    <?php if ($answer['correct_ans'] == 'D'): ?>
                        <span class="badge badge-success">Correct Answer</span>
                    <?php endif; ?>
                    <?php if ($answer['student_ans'] == 'D' && $answer['correct_ans'] != 'D'): ?>
                        <span class="badge badge-danger">Your Answer</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (empty($answer['student_ans'])): ?>
                <p class="not-answered">Not Answered</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>