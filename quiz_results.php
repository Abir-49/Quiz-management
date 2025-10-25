<?php
require_once 'config.php';
require_once 'functions.php';

requireInstructor();

$instructor_id = getUserId();
$quiz_id = intval($_GET['id'] ?? 0);

$quiz_stats = getQuizResults($quiz_id, $instructor_id);
if (!$quiz_stats) {
    header("Location: instructor_dashboard.php?error=invalid_quiz");
    exit();
}

$participants = getQuizParticipants($quiz_id);
$absent_students = getAbsentStudents($quiz_id, $instructor_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - <?php echo htmlspecialchars($quiz_stats['q_name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>Quiz Management System</h1>
            <div class="nav-links">
                <a href="instructor_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Quiz Results: <?php echo htmlspecialchars($quiz_stats['q_name']); ?></h2>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $quiz_stats['total_participants']; ?></h3>
                <p>Total Participants</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($absent_students); ?></h3>
                <p>Absent Students</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($quiz_stats['avg_percentage'] ?? 0, 2); ?>%</h3>
                <p>Average Percentage</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $quiz_stats['highest_marks'] ?? 0; ?> / <?php echo $quiz_stats['total_marks']; ?></h3>
                <p>Highest Score</p>
            </div>
        </div>

        <!-- Participants Results -->
        <div class="section">
            <h3>Participants (<?php echo count($participants); ?>)</h3>
            <?php if (count($participants) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Name</th>
                        <th>Roll</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $rank => $participant): ?>
                    <tr>
                        <td><?php echo $rank + 1; ?></td>
                        <td><?php echo htmlspecialchars($participant['name']); ?></td>
                        <td><?php echo htmlspecialchars($participant['roll']); ?></td>
                        <td><?php echo $participant['marks']; ?> / <?php echo $participant['total_marks']; ?></td>
                        <td>
                            <span class="badge <?php echo $participant['percentage'] >= 80 ? 'badge-success' : ($participant['percentage'] >= 60 ? 'badge-warning' : 'badge-danger'); ?>">
                                <?php echo number_format($participant['percentage'], 2); ?>%
                            </span>
                        </td>
                        <td><?php echo formatDate($participant['submitted_at']); ?></td>
                        <td>
                            <a href="view_answer_script.php?quiz_id=<?php echo $quiz_id; ?>&student_id=<?php echo $participant['id']; ?>" class="btn btn-sm btn-info">View Answer Script</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="empty-message">No participants yet.</p>
            <?php endif; ?>
        </div>

        <!-- Absent Students -->
        <?php if (count($absent_students) > 0): ?>
        <div class="section">
            <h3>Absent Students (<?php echo count($absent_students); ?>)</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Roll</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($absent_students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td><?php echo htmlspecialchars($student['roll']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>