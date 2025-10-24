<?php
require_once 'config.php';
require_once 'functions.php';

if (isLoggedIn()) {
    if (isInstructor()) {
        header("Location: instructor_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];
    
    // Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } else {
        if ($user_type === 'instructor') {
            $result = instructorRegister($name, $email, $password);
        } else {
            $roll = sanitizeInput($_POST['roll']);
            $department = sanitizeInput($_POST['department'] ?? '');
            $result = studentRegister($roll, $name, $email, $password, $department);
        }
        
        if ($result['success']) {
            $success = $result['message'] . " You can now login.";
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Quiz Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function toggleStudentFields() {
            const userType = document.querySelector('select[name="user_type"]').value;
            const studentFields = document.getElementById('student-fields');
            studentFields.style.display = userType === 'student' ? 'block' : 'none';
            
            const rollField = document.querySelector('input[name="roll"]');
            if (rollField) {
                rollField.required = userType === 'student';
            }
        }
    </script>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Quiz Management System</h1>
            <h2>Register</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>User Type</label>
                    <select name="user_type" required class="form-control" onchange="toggleStudentFields()">
                        <option value="student">Student</option>
                        <option value="instructor">Instructor</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div id="student-fields">
                    <div class="form-group">
                        <label>Roll Number</label>
                        <input type="text" name="roll" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Department (Optional)</label>
                        <input type="text" name="department" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
            
            <p class="auth-link">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>