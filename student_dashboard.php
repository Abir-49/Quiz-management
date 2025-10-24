<?php
require_once 'config.php';
require_once 'functions.php';

requireStudent();

$student_id = getUserId();

// Get dashboard statistics
$stats = getStudentDashboardStats($student_id);
$pending_quizzes = getStudentPendingQuizzes($student_id);
$taken_quizzes = getStudentTakenQuizzes($student_id);
$finished_quizzes = getStudentFinishedQuizzes($student_id);
$subscribed_instructors = getStudentSubscribedInstructors($student_id);

// Handle search for instructors
$search_results = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = sanitizeInput($_GET['search']);
    $search_results = searchInstructors($search_term);
}

// Handle join request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_instructor'])) {
    $instructor_id = intval($_POST['instructor_id']);
    $result = sendClassJoinRequest($student_id, $instructor_id);
    if ($result['success']) {
        header("Location: student_dashboard.php?msg=request_sent");
        exit();
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Quiz Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>Quiz Management System</h1>
            <div class="nav-links">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container dashboard">
        <h2>Student Dashboard</h2>
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?php 
                if ($_GET['msg'] === 'request_sent') echo 'Join request sent successfully!';
                if ($_GET['msg'] === 'quiz_submitted') echo 'Quiz submitted successfully!';
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo number_format($stats['avg_percentage'] ?? 0, 2); ?>%</h3>
                <p>Average Score</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_quizzes_taken'] ?? 0; ?></h3>
                <p>Quizzes Taken</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_missed'] ?? 0; ?></h3>
                <p>Quizzes Missed</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($subscribed_instructors); ?></h3>
                <p>Subscribed Instructors</p>
            </div>
        </div>

        <!-- Join Instructor Section -->
        <div class="section">
            <h3>Join Instructor's Class</h3>
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search instructor by name or email..." 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" required>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            
            <?php if (!empty($search_results)): ?>
            <div class="search-results">
                <h4>Search Results:</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $instructor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($instructor['name']); ?></td>
                            <td><?php echo htmlspecialchars($instructor['email']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="instructor_id" value="<?php echo $instructor['id']; ?>">
                                    <button type="submit" name="join_instructor" class="btn btn-sm btn-primary">Send Request</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif (isset($_GET['search'])): ?>
            <p class="empty-message">No instructors found matching your search.</p>
            <?php endif; ?>
        </div>

        <!-- Subscribed Instructors -->
        <div class="section">
            <h3>My Instructors (<?php echo count($subscribed_instructors); ?>)</h3>
            <?php if (count($subscribed_instructors) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribed_instructors as $instructor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($instructor['name']); ?></td>
                        <td><?php echo htmlspecialchars($instructor['email']); ?></td>
                        <td><?php echo formatDate($instructor['approved_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="empty-message">You haven't joined any instructor's class yet. Search and send join requests above!</p>
            <?php endif; ?>
        </div>

        <!-- Pending Quizzes -->
        <div class="section">
            <h3>Pending Quizzes (<?php echo count($pending_quizzes); ?>)</h3>
            <?php if (count($pending_quizzes) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Quiz Name</th>
                        <th>Instructor</th>
                        <th>Questions</th>
                        <th>Duration</th>
                        <th>Total Marks</th>
                        <th>Expires At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_quizzes as $quiz): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['q_name']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['instructor_name']); ?></td>
                        <td><?php echo $quiz['question_count']; ?></td>
                        <td><?php echo $quiz['duration']; ?> min</td>
                        <td><?php echo $quiz['total_marks']; ?></td>
                        <td><?php echo formatDate($quiz['expire_date']); ?></td>
                        <td>
                            <a href="take_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-success">Take Quiz</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="empty-message">No pending quizzes available.</p>
            <?php endif; ?>
        </div>

        <!-- Taken Quizzes -->
        <div class="section">
            <h3>Quizzes Taken (<?php echo count($taken_quizzes); ?>)</h3>
            <?php if (count($taken_quizzes) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Quiz Name</th>
                        <th>Instructor</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($taken_quizzes as $quiz): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['q_name']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['instructor_name']); ?></td>
                        <td><?php echo $quiz['marks']; ?> / <?php echo $quiz['total_marks']; ?></td>
                        <td>
                            <span class="badge <?php echo $quiz['percentage'] >= 80 ? 'badge-success' : ($quiz['percentage'] >= 60 ? 'badge-warning' : 'badge-danger'); ?>">
                                <?php echo number_format($quiz['percentage'], 2); ?>%
                            </span>
                        </td>
                        <td><?php echo formatDate($quiz['submitted_at']); ?></td>
                        <td>
                            <?php if (isQuizExpired($quiz['expire_date'])): ?>
                                <a href="view_answer_script.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-info">View Answers</a>
                            <?php else: ?>
                                <span class="text-muted">Available after expiry</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="empty-message">You haven't taken any quiz yet.</p>
            <?php endif; ?>
        </div>

        <!-- Finished Quizzes (Expired) -->
        <div class="section">
            <h3>Finished Quizzes (<?php echo count($finished_quizzes); ?>)</h3>
            <?php if (count($finished_quizzes) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Quiz Name</th>
                        <th>Instructor</th>
                        <th>Total Marks</th>
                        <th>Participants</th>
                        <th>Average Score</th>
                        <th>Expired At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($finished_quizzes as $quiz): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['q_name']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['instructor_name']); ?></td>
                        <td><?php echo $quiz['total_marks']; ?></td>
                        <td><?php echo $quiz['total_participants']; ?></td>
                        <td><?php echo number_format($quiz['avg_percentage'] ?? 0, 2); ?>%</td>
                        <td><?php echo formatDate($quiz['expire_date']); ?></td>
                        <td>
                            <a href="student_quiz_results.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-info">View Results</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="empty-message">No finished quizzes yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>