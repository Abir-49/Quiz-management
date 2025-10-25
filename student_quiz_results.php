<?php
require_once 'config.php';
require_once 'functions.php';

requireStudent();

$student_id = getUserId();
$quiz_id = intval($_GET['id'] ?? 0);

// Get quiz details
$quiz_details = getQuizDetails($quiz_id);
if (!$quiz_details) {
    header("Location: student_dashboard.php?error=invalid_quiz");
    exit();
}

// Check if quiz is expired
if (!isQuizExpired($quiz_details['expire_date'])) {
    header("Location: student_dashboard.php?error=quiz_not_expired");
    exit();
}

// Get all participants for this quiz
$participants = getQuizParticipants($quiz_id);

// Check if current student took the quiz
$student_result = null;
foreach ($participants as $participant) {
    if ($participant['id'] == $student_id) {
        $student_result = $participant;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - <?php echo htmlspecialchars($quiz_details['q_name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>Quiz Management System</h1>
            <div class="nav-links">
                <a href="student_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Quiz Results: <?php echo htmlspecialchars($quiz_details['q_name']); ?></h2>
        <p><strong>Instructor:</strong> <?php echo htmlspecialchars($quiz_details['instructor_name']); ?></p>
        
        <?php if ($student_result): ?>
        <div class="student-score-card">
            <h3>Your Performance</h3>
            <p><strong>Score:</strong> <?php echo $student_result['marks']; ?> / <?php echo $student_result['total_marks']; ?></p>
            <p><strong>Percentage:</strong> 
                <span class="badge <?php echo $student_result['percentage'] >= 80 ? 'badge-success' : ($student_result['percentage'] >= 60 ? 'badge-warning' : 'badge-danger'); ?>">
                    <?php echo number_format($student_result['percentage'], 2); ?>%
                </span>
            </p>
            <a href="view_answer_script.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary">View Your Answer Script</a>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">You did not take this quiz.</div>
        <?php endif; ?>

        <!-- Overall Statistics -->
        <div class="section">
            <h3>Overall Statistics</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Name</th>
                        <th>Roll</th>
                        <th>Score</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $rank => $participant): ?>
                    <tr <?php echo ($participant['id'] == $student_id) ? 'class="highlight-row"' : ''; ?>>
                        <td><?php echo $rank + 1; ?></td>
                        <td>
                            <?php echo htmlspecialchars($participant['name']); ?>
                            <?php if ($participant['id'] == $student_id): ?>
                                <span class="badge badge-info">You</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($participant['roll']); ?></td>
                        <td><?php echo $participant['marks']; ?> / <?php echo $participant['total_marks']; ?></td>
                        <td>
                            <span class="badge <?php echo $participant['percentage'] >= 80 ? 'badge-success' : ($participant['percentage'] >= 60 ? 'badge-warning' : 'badge-danger'); ?>">
                                <?php echo number_format($participant['percentage'], 2); ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>