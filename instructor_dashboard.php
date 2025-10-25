<?php
require_once 'config.php';
require_once 'functions.php';

requireInstructor();

$instructor_id = getUserId();

// Get dashboard statistics
$stats = getInstructorDashboardStats($instructor_id);
$pending_quizzes = getPendingQuizzes($instructor_id);
$finished_quizzes = getFinishedQuizzes($instructor_id);
$classroom_students = getClassroomStudents($instructor_id);
$pending_requests = getPendingClassRequests($instructor_id);

// Handle class request approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_student'])) {
        $student_id = intval($_POST['student_id']);
        $result = approveClassRequest($instructor_id, $student_id);
        if ($result['success']) {
            header("Location: instructor_dashboard.php?msg=approved");
            exit();
        }
    }
    
    if (isset($_POST['reject_student'])) {
        $student_id = intval($_POST['student_id']);
        $result = rejectClassRequest($instructor_id, $student_id);
        if ($result['success']) {
            header("Location: instructor_dashboard.php?msg=rejected");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - Quiz Management</title>
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
        <h2>Instructor Dashboard</h2>
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?php 
                if ($_GET['msg'] === 'approved') echo 'Student approved successfully!';
                if ($_GET['msg'] === 'rejected') echo 'Request rejected!';
                if ($_GET['msg'] === 'quiz_created') echo 'Quiz created successfully!';
                ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['total_quizzes']; ?></h3>
                <p>Total Quizzes</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['pending_quizzes']; ?></h3>
                <p>Pending Quizzes</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['finished_quizzes']; ?></h3>
                <p>Finished Quizzes</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_students']; ?></h3>
                <p>Total Students</p>
            </div>
        </div>

        <!-- Create Quiz Button -->
        <div class="action-buttons">
            <a href="create_quiz.php" class="btn btn-primary">Create New Quiz</a>
        </div>

        <!-- Pending Class Requests -->
        <?php if (count($pending_requests) > 0): ?>
        <div class="section">
            <h3>Student Join Requests (<?php echo count($pending_requests); ?>)</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Roll</th>
                        <th>Email</th>
                        <th>Requested At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_requests as $request): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['name']); ?></td>
                        <td><?php echo htmlspecialchars($request['roll']); ?></td>
                        <td><?php echo htmlspecialchars($request['email']); ?></td>
                        <td><?php echo formatDate($request['requested_at']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="student_id" value="<?php echo $request['id']; ?>">
                                <input type="hidden" name="approve_student" value="1">
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="student_id" value="<?php echo $request['id']; ?>">
                                <input type="hidden" name="reject_student" value="1">
                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Pending Quizzes -->
        <div class="section">
            <h3>Pending Quizzes (<?php echo count($pending_quizzes); ?>)</h3>
            <?php if (count($pending_quizzes) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Quiz Name</th>
                        <th>Questions</th>
                        <th>Total Marks</th>
                        <th>Duration</th>
                        <th>Expires At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_quizzes as $quiz): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['q_name']); ?></td>
                        <td><?php echo $quiz['question_count']; ?></td>
                        <td><?php echo $quiz['total_marks']; ?></td>
                        <td><?php echo $quiz['duration']; ?> min</td>
                        <td><?php echo formatDate($quiz['expire_date']); ?></td>
                        <td>
                            <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                            <a href="view_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="empty-message">No pending quizzes. <a href="create_quiz.php">Create one now!</a></p>
            <?php endif; ?>
        </div>

        <!-- Finished Quizzes -->
        <div class="section">
            <h3>Finished Quizzes (<?php echo count($finished_quizzes); ?>)</h3>
            <?php if (count($finished_quizzes) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Quiz Name</th>
                        <th>Total Marks</th>
                        <th>Participants</th>
                        <th>Expired At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($finished_quizzes as $quiz): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['q_name']); ?></td>
                        <td><?php echo $quiz['total_marks']; ?></td>
                        <td><?php echo $quiz['participants']; ?></td>
                        <td><?php echo formatDate($quiz['expire_date']); ?></td>
                        <td>
                            <a href="quiz_results.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-primary">View Results</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="empty-message">No finished quizzes yet.</p>
            <?php endif; ?>
        </div>

        <!-- Classroom Students -->
        <div class="section">
            <h3>Classroom Students (<?php echo count($classroom_students); ?>)</h3>
            <?php if (count($classroom_students) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Roll</th>
                        <th>Email</th>
                        <th>Joined At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classroom_students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td><?php echo htmlspecialchars($student['roll']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo formatDate($student['approved_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="empty-message">No students in classroom yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>