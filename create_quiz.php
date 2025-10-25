<!-- create_quiz.php -->
<?php
require_once 'config.php';
require_once 'functions.php';

requireInstructor();

$instructor_id = getUserId();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_name = sanitizeInput($_POST['quiz_name']);
    $expire_date = $_POST['expire_date'];
    $duration = intval($_POST['duration']);
    $description = sanitizeInput($_POST['description']);
    $questions = $_POST['questions'] ?? [];
    
    if (count($questions) === 0) {
        $error = "Please add at least one question!";
    } else {
        $db = new Database();
        
        try {
            $db->beginTransaction();
            
            // Create quiz
            $quiz_result = createQuiz($instructor_id, $quiz_name, $expire_date, $duration, $description);
            $quiz_id = $quiz_result['insert_id'];
            
            // Add questions
            $q_no = 1;
            foreach ($questions as $question) {
                addQuestion(
                    $quiz_id,
                    $q_no,
                    $question['question'],
                    $question['A'],
                    $question['B'],
                    $question['C'],
                    $question['D'],
                    $question['ans'],
                    intval($question['marks'])
                );
                $q_no++;
            }
            
            $db->commit();
            header("Location: instructor_dashboard.php?msg=quiz_created");
            exit();
            
        } catch (Exception $e) {
            $db->rollback();
            $error = "Error creating quiz: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz - Quiz Management System</title>
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
        <h2>Create New Quiz</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" id="quiz-form">
            <div class="form-section">
                <h3>Quiz Details</h3>
                
                <div class="form-group">
                    <label>Quiz Name *</label>
                    <input type="text" name="quiz_name" class="form-control" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Expire Date & Time *</label>
                        <input type="datetime-local" name="expire_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Duration (minutes) *</label>
                        <input type="number" name="duration" class="form-control" min="1" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Questions</h3>
                <div id="questions-container"></div>
                <button type="button" class="btn btn-success" onclick="addQuestion()">Add Question</button>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Quiz</button>
                <a href="instructor_dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        let questionCount = 0;
        
        function addQuestion() {
            questionCount++;
            const container = document.getElementById('questions-container');
            const questionHtml = `
                <div class="question-card" id="question-${questionCount}">
                    <div class="question-header">
                        <h4>Question ${questionCount}</h4>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeQuestion(${questionCount})">Remove</button>
                    </div>
                    
                    <div class="form-group">
                        <label>Question Text *</label>
                        <textarea name="questions[${questionCount}][question]" class="form-control" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Option A *</label>
                        <input type="text" name="questions[${questionCount}][A]" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Option B *</label>
                        <input type="text" name="questions[${questionCount}][B]" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Option C *</label>
                        <input type="text" name="questions[${questionCount}][C]" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Option D *</label>
                        <input type="text" name="questions[${questionCount}][D]" class="form-control" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Correct Answer *</label>
                            <select name="questions[${questionCount}][ans]" class="form-control" required>
                                <option value="">Select Answer</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Marks *</label>
                            <input type="number" name="questions[${questionCount}][marks]" class="form-control" value="1" min="1" required>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', questionHtml);
        }
        
        function removeQuestion(id) {
            const element = document.getElementById(`question-${id}`);
            element.remove();
        }
        
        // Add first question by default
        addQuestion();
    </script>
</body>
</html>