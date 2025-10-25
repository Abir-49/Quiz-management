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

// Get questions
$db = new Database();
$questions_query = "SELECT * FROM questions WHERE q_id = ? ORDER BY q_no";
$questions_result = $db->select($questions_query, [$quiz_id], "i");
$questions = $questions_result->fetch_all(MYSQLI_ASSOC);

// Get participant count
$participant_query = "SELECT COUNT(*) as count FROM result WHERE q_id = ?";
$participant_result = $db->select($participant_query, [$quiz_id], "i");
$participants = $participant_result->fetch_assoc()['count'];

$is_expired = isQuizExpired($quiz['expire_date']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Quiz - <?php echo htmlspecialchars($quiz['q_name']); ?></title>
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
        <div class="quiz-header">
            <h2><?php echo htmlspecialchars($quiz['q_name']); ?></h2>
            <div class="quiz-meta">
                <p><strong>Status:</strong> 
                    <span class="badge <?php echo $is_expired ? 'badge-danger' : 'badge-success'; ?>">
                        <?php echo $is_expired ? 'Expired' : 'Active'; ?>
                    </span>
                </p>
                <p><strong>Total Questions:</strong> <?php echo count($questions); ?></p>
                <p><strong>Total Marks:</strong> <?php echo $quiz['total_marks']; ?></p>
                <p><strong>Duration:</strong> <?php echo $quiz['duration']; ?> minutes</p>
                <p><strong>Expires At:</strong> <?php echo formatDate($quiz['expire_date']); ?></p>
                <p><strong>Participants:</strong> <?php echo $participants; ?> student<?php echo $participants != 1 ? 's' : ''; ?></p>
                <?php if (!empty($quiz['description'])): ?>
                <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="action-buttons">
            <a href="edit_quiz.php?id=<?php echo $quiz_id; ?>" class="btn btn-info">Edit Quiz</a>
            <?php if ($is_expired): ?>
                <a href="quiz_results.php?id=<?php echo $quiz_id; ?>" class="btn btn-primary">View Results</a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-secondary">Print Quiz</button>
        </div>

        <!-- Questions Display -->
        <div class="section">
            <h3>Quiz Questions</h3>
            
            <?php if (count($questions) > 0): ?>
                <?php foreach ($questions as $question): ?>
                <div class="question-card">
                    <div class="question-header">
                        <h4>Question <?php echo $question['q_no']; ?></h4>
                        <span class="marks-badge"><?php echo $question['marks']; ?> mark<?php echo $question['marks'] > 1 ? 's' : ''; ?></span>
                    </div>
                    
                    <p class="question-text"><?php echo htmlspecialchars($question['question']); ?></p>
                    
                    <div class="options-display">
                        <div class="option-item <?php echo $question['ans'] == 'A' ? 'correct-answer' : ''; ?>">
                            <strong>A.</strong> <?php echo htmlspecialchars($question['A']); ?>
                            <?php if ($question['ans'] == 'A'): ?>
                                <span class="badge badge-success">✓ Correct Answer</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="option-item <?php echo $question['ans'] == 'B' ? 'correct-answer' : ''; ?>">
                            <strong>B.</strong> <?php echo htmlspecialchars($question['B']); ?>
                            <?php if ($question['ans'] == 'B'): ?>
                                <span class="badge badge-success">✓ Correct Answer</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="option-item <?php echo $question['ans'] == 'C' ? 'correct-answer' : ''; ?>">
                            <strong>C.</strong> <?php echo htmlspecialchars($question['C']); ?>
                            <?php if ($question['ans'] == 'C'): ?>
                                <span class="badge badge-success">✓ Correct Answer</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="option-item <?php echo $question['ans'] == 'D' ? 'correct-answer' : ''; ?>">
                            <strong>D.</strong> <?php echo htmlspecialchars($question['D']); ?>
                            <?php if ($question['ans'] == 'D'): ?>
                                <span class="badge badge-success">✓ Correct Answer</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-message">No questions added to this quiz yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .quiz-meta {
            background: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .quiz-meta p {
            margin-bottom: 10px;
            color: #4a5568;
        }
        
        .quiz-meta strong {
            color: #2d3748;
        }
        
        .options-display {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 15px;
        }
        
        .option-item {
            padding: 12px 15px;
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .option-item.correct-answer {
            background: #c6f6d5;
            border-color: #48bb78;
        }
        
        @media print {
            .navbar, .action-buttons, .btn {
                display: none;
            }
        }
    </style>
</body>
</html>