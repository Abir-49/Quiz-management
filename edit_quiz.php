<?php
require_once 'config.php';
require_once 'functions.php';

requireInstructor();

$instructor_id = getUserId();
$quiz_id = intval($_GET['id'] ?? 0);

// Get quiz details and verify ownership
$quiz = getQuizDetails($quiz_id);

if (!$quiz || $quiz['t_id'] != $instructor_id) {
    header("Location: instructor_dashboard.php?error=invalid_quiz");
    exit();
}

// Get existing questions
$db = new Database();
$questions_query = "SELECT * FROM questions WHERE q_id = ? ORDER BY q_no";
$questions_result = $db->select($questions_query, [$quiz_id], "i");
$existing_questions = $questions_result->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_name = sanitizeInput($_POST['quiz_name']);
    $expire_date = $_POST['expire_date'];
    $duration = intval($_POST['duration']);
    $description = sanitizeInput($_POST['description']);
    
    // Update quiz details
    $result = updateQuiz($quiz_id, $instructor_id, $quiz_name, $expire_date, $duration, $description);
    
    if ($result['success']) {
        $success = "Quiz updated successfully!";
        // Refresh quiz data
        $quiz = getQuizDetails($quiz_id);
    } else {
        $error = "Failed to update quiz.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - <?php echo htmlspecialchars($quiz['q_name']); ?></title>
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
        <h2>Edit Quiz: <?php echo htmlspecialchars($quiz['q_name']); ?></h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-section">
                <h3>Quiz Details</h3>
                
                <div class="form-group">
                    <label>Quiz Name *</label>
                    <input type="text" name="quiz_name" class="form-control" value="<?php echo htmlspecialchars($quiz['q_name']); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Expire Date & Time *</label>
                        <input type="datetime-local" name="expire_date" class="form-control" 
                               value="<?php echo date('Y-m-d\TH:i', strtotime($quiz['expire_date'])); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Duration (minutes) *</label>
                        <input type="number" name="duration" class="form-control" min="1" value="<?php echo $quiz['duration']; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($quiz['description'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Quiz Details</button>
                <a href="view_quiz.php?id=<?php echo $quiz_id; ?>" class="btn btn-info">View Quiz</a>
                <a href="instructor_dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

        <!-- Existing Questions -->
        <div class="form-section">
            <h3>Questions (<?php echo count($existing_questions); ?>)</h3>
            <p class="text-muted">To modify questions, please delete the quiz and create a new one.</p>
            
            <?php if (count($existing_questions) > 0): ?>
                <div class="questions-list">
                    <?php foreach ($existing_questions as $question): ?>
                    <div class="question-card">
                        <div class="question-header">
                            <h4>Question <?php echo $question['q_no']; ?> (<?php echo $question['marks']; ?> mark<?php echo $question['marks'] > 1 ? 's' : ''; ?>)</h4>
                        </div>
                        
                        <p class="question-text"><?php echo htmlspecialchars($question['question']); ?></p>
                        
                        <div class="options-display">
                            <div class="option-item <?php echo $question['ans'] == 'A' ? 'correct-answer' : ''; ?>">
                                <strong>A.</strong> <?php echo htmlspecialchars($question['A']); ?>
                                <?php if ($question['ans'] == 'A'): ?><span class="badge badge-success">Correct</span><?php endif; ?>
                            </div>
                            <div class="option-item <?php echo $question['ans'] == 'B' ? 'correct-answer' : ''; ?>">
                                <strong>B.</strong> <?php echo htmlspecialchars($question['B']); ?>
                                <?php if ($question['ans'] == 'B'): ?><span class="badge badge-success">Correct</span><?php endif; ?>
                            </div>
                            <div class="option-item <?php echo $question['ans'] == 'C' ? 'correct-answer' : ''; ?>">
                                <strong>C.</strong> <?php echo htmlspecialchars($question['C']); ?>
                                <?php if ($question['ans'] == 'C'): ?><span class="badge badge-success">Correct</span><?php endif; ?>
                            </div>
                            <div class="option-item <?php echo $question['ans'] == 'D' ? 'correct-answer' : ''; ?>">
                                <strong>D.</strong> <?php echo htmlspecialchars($question['D']); ?>
                                <?php if ($question['ans'] == 'D'): ?><span class="badge badge-success">Correct</span><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-message">No questions added yet.</p>
            <?php endif; ?>
        </div>

        <!-- Delete Quiz -->
        <div class="form-section">
            <h3 style="color: #e53e3e;">Danger Zone</h3>
            <p>Once you delete a quiz, there is no going back. This will also delete all associated questions, answers, and results.</p>
            <form method="POST" action="delete_quiz.php" onsubmit="return confirm('Are you sure you want to delete this quiz? This action cannot be undone!');">
                <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                <input type="hidden" name="delete_quiz" value="1">
                <button type="submit" class="btn btn-danger">Delete This Quiz</button>
            </form>
        </div>
    </div>

    <style>
        .questions-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .options-display {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 15px;
        }
        
        .option-item {
            padding: 10px 15px;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
        }
        
        .option-item.correct-answer {
            background: #c6f6d5;
            border-color: #48bb78;
        }
    </style>
</body>
</html>
