<?php
require_once 'config.php';
require_once 'functions.php';

requireStudent();

$student_id = getUserId();
$quiz_id = intval($_GET['id'] ?? 0);

// Get quiz questions
$questions = getQuizQuestions($quiz_id, $student_id);

if (!$questions) {
    header("Location: student_dashboard.php?error=invalid_quiz");
    exit();
}

$quiz_details = getQuizDetails($quiz_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'answer_') === 0) {
            $q_no = intval(str_replace('answer_', '', $key));
            $answers[$q_no] = $value;
        }
    }
    
    $result = submitQuizAnswers($quiz_id, $student_id, $answers);
    
    if ($result['success']) {
        header("Location: student_dashboard.php?msg=quiz_submitted");
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
    <title>Take Quiz - <?php echo htmlspecialchars($quiz_details['q_name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>Quiz Management System</h1>
            <div class="nav-links">
                <span id="timer" class="timer"></span>
                <a href="student_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="quiz-header">
            <h2><?php echo htmlspecialchars($quiz_details['q_name']); ?></h2>
            <p><strong>Instructor:</strong> <?php echo htmlspecialchars($quiz_details['instructor_name']); ?></p>
            <p><strong>Total Marks:</strong> <?php echo $quiz_details['total_marks']; ?></p>
            <p><strong>Duration:</strong> <?php echo $quiz_details['duration']; ?> minutes</p>
            <p><strong>Total Questions:</strong> <?php echo count($questions); ?></p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" id="quiz-form">
            <?php foreach ($questions as $index => $question): ?>
            <div class="question-card">
                <h4>Question <?php echo $question['q_no']; ?> (<?php echo $question['marks']; ?> mark<?php echo $question['marks'] > 1 ? 's' : ''; ?>)</h4>
                <p class="question-text"><?php echo htmlspecialchars($question['question']); ?></p>
                
                <div class="options">
                    <label class="option">
                        <input type="radio" name="answer_<?php echo $question['q_no']; ?>" value="A" required>
                        <span>A. <?php echo htmlspecialchars($question['A']); ?></span>
                    </label>
                    
                    <label class="option">
                        <input type="radio" name="answer_<?php echo $question['q_no']; ?>" value="B" required>
                        <span>B. <?php echo htmlspecialchars($question['B']); ?></span>
                    </label>
                    
                    <label class="option">
                        <input type="radio" name="answer_<?php echo $question['q_no']; ?>" value="C" required>
                        <span>C. <?php echo htmlspecialchars($question['C']); ?></span>
                    </label>
                    
                    <label class="option">
                        <input type="radio" name="answer_<?php echo $question['q_no']; ?>" value="D" required>
                        <span>D. <?php echo htmlspecialchars($question['D']); ?></span>
                    </label>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Are you sure you want to submit? You cannot change answers after submission.')">Submit Quiz</button>
            </div>
        </form>
    </div>

    <script>
        // Timer functionality
        const duration = <?php echo $quiz_details['duration']; ?> * 60; // Convert to seconds
        let timeRemaining = duration;
        
        function updateTimer() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            document.getElementById('timer').textContent = 
                `Time Remaining: ${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeRemaining <= 0) {
                alert('Time is up! Submitting quiz...');
                document.getElementById('quiz-form').submit();
            } else if (timeRemaining <= 60) {
                document.getElementById('timer').style.color = 'red';
            }
            
            timeRemaining--;
        }
        
        // Update timer every second
        updateTimer();
        setInterval(updateTimer, 1000);
        
        // Prevent accidental page refresh
        window.addEventListener('beforeunload', function (e) {
            e.preventDefault();
            e.returnValue = '';
        });
    </script>
</body>
</html>