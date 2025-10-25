<?php
// functions.php - Core Business Logic Functions

require_once 'config.php';

// ==================== INSTRUCTOR FUNCTIONS ====================

function instructorRegister($name, $email, $password) {
    $db = new Database();
    
    // CHECK: Email already exists (using SELECT with WHERE)
    $check_query = "SELECT id FROM instructor WHERE email = ?";
    $result = $db->select($check_query, [$email], "s");
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // INSERT: New instructor
    $hashed_password = md5($password);
    $insert_query = "INSERT INTO instructor (name, email, password) VALUES (?, ?, ?)";
    $result = $db->execute($insert_query, [$name, $email, $hashed_password], "sss");
    
    if ($result['success']) {
        return ['success' => true, 'message' => 'Registration successful', 'id' => $result['insert_id']];
    }
    
    return ['success' => false, 'message' => 'Registration failed'];
}

function instructorLogin($email, $password) {
    $db = new Database();
    
    // SELECT with WHERE clause
    $query = "SELECT id, name, email FROM instructor WHERE email = ? AND password = ?";
    $hashed_password = md5($password);
    $result = $db->select($query, [$email, $hashed_password], "ss");
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = 'instructor';
        return ['success' => true, 'user' => $user];
    }
    
    return ['success' => false, 'message' => 'Invalid credentials'];
}

function getInstructorDashboardStats($instructor_id) {
    $db = new Database();
    
    // AGGREGATE FUNCTIONS with GROUP BY
    $query = "SELECT 
                COUNT(*) as total_quizzes,
                SUM(CASE WHEN expire_date > NOW() THEN 1 ELSE 0 END) as pending_quizzes,
                SUM(CASE WHEN expire_date <= NOW() THEN 1 ELSE 0 END) as finished_quizzes
              FROM quiz 
              WHERE t_id = ?";
    
    $result = $db->select($query, [$instructor_id], "i");
    $stats = $result->fetch_assoc();
    
    // Count approved students (JOIN)
    $student_query = "SELECT COUNT(*) as total_students 
                      FROM class 
                      WHERE t_id = ? AND status = 'approved'";
    $student_result = $db->select($student_query, [$instructor_id], "i");
    $student_count = $student_result->fetch_assoc();
    
    $stats['total_students'] = $student_count['total_students'];
    
    return $stats;
}

function getPendingQuizzes($instructor_id) {
    $db = new Database();
    
    // SELECT with WHERE and JOIN
    $query = "SELECT 
                q.id,
                q.q_name,
                q.expire_date,
                q.duration,
                q.total_marks,
                COUNT(DISTINCT ques.q_no) as question_count
              FROM quiz q
              LEFT JOIN questions ques ON q.id = ques.q_id
              WHERE q.t_id = ? AND q.expire_date > NOW()
              GROUP BY q.id
              ORDER BY q.expire_date ASC";
    
    $result = $db->select($query, [$instructor_id], "i");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getFinishedQuizzes($instructor_id) {
    $db = new Database();
    
    // Using VIEW for complex query
    $query = "SELECT 
                q.id,
                q.q_name,
                q.expire_date,
                q.total_marks,
                COUNT(DISTINCT r.s_id) as participants
              FROM quiz q
              LEFT JOIN result r ON q.id = r.q_id
              WHERE q.t_id = ? AND q.expire_date <= NOW()
              GROUP BY q.id
              ORDER BY q.expire_date DESC";
    
    $result = $db->select($query, [$instructor_id], "i");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getClassroomStudents($instructor_id) {
    $db = new Database();
    
    // INNER JOIN with WHERE
    $query = "SELECT 
                s.id,
                s.name,
                s.roll,
                s.email,
                c.approved_at
              FROM student s
              INNER JOIN class c ON s.id = c.s_id
              WHERE c.t_id = ? AND c.status = 'approved'
              ORDER BY s.name";
    
    $result = $db->select($query, [$instructor_id], "i");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getPendingClassRequests($instructor_id) {
    $db = new Database();
    
    // JOIN with WHERE clause
    $query = "SELECT 
                s.id,
                s.name,
                s.roll,
                s.email,
                c.requested_at
              FROM student s
              INNER JOIN class c ON s.id = c.s_id
              WHERE c.t_id = ? AND c.status = 'pending'
              ORDER BY c.requested_at DESC";
    
    $result = $db->select($query, [$instructor_id], "i");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function approveClassRequest($instructor_id, $student_id) {
    $db = new Database();
    
    // UPDATE with WHERE
    $query = "UPDATE class 
              SET status = 'approved', approved_at = NOW() 
              WHERE t_id = ? AND s_id = ? AND status = 'pending'";
    
    return $db->execute($query, [$instructor_id, $student_id], "ii");
}

function rejectClassRequest($instructor_id, $student_id) {
    $db = new Database();
    
    // DELETE operation
    $query = "DELETE FROM class 
              WHERE t_id = ? AND s_id = ? AND status = 'pending'";
    
    return $db->execute($query, [$instructor_id, $student_id], "ii");
}

function createQuiz($instructor_id, $quiz_name, $expire_date, $duration, $description) {
    $db = new Database();
    
    // INSERT quiz
    $query = "INSERT INTO quiz (t_id, q_name, expire_date, duration, description) 
              VALUES (?, ?, ?, ?, ?)";
    
    $result = $db->execute($query, [$instructor_id, $quiz_name, $expire_date, $duration, $description], "issss");
    
    return $result;
}

function addQuestion($quiz_id, $q_no, $question, $optionA, $optionB, $optionC, $optionD, $answer, $marks) {
    $db = new Database();
    
    // INSERT question - Fixed parameter count
    $query = "INSERT INTO questions (q_id, q_no, question, A, B, C, D, ans, marks) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $result = $db->execute($query, [$quiz_id, $q_no, $question, $optionA, $optionB, $optionC, $optionD, $answer, $marks], "iissssssi");
    
    // UPDATE total marks in quiz table
    if ($result['success']) {
        $update_query = "UPDATE quiz 
                        SET total_marks = (SELECT SUM(marks) FROM questions WHERE q_id = ?) 
                        WHERE id = ?";
        $db->execute($update_query, [$quiz_id, $quiz_id], "ii");
    }
    
    return $result;
}

function getQuizResults($quiz_id, $instructor_id) {
    $db = new Database();
    
    // Verify ownership
    $verify_query = "SELECT id FROM quiz WHERE id = ? AND t_id = ?";
    $verify_result = $db->select($verify_query, [$quiz_id, $instructor_id], "ii");
    
    if ($verify_result->num_rows === 0) {
        return null;
    }
    
    // AGGREGATE with GROUP BY and HAVING
    $query = "SELECT 
                q.q_name,
                q.total_marks,
                COUNT(DISTINCT r.s_id) as total_participants,
                AVG(r.percentage) as avg_percentage,
                MAX(r.marks) as highest_marks,
                MIN(r.marks) as lowest_marks
              FROM quiz q
              LEFT JOIN result r ON q.id = r.q_id
              WHERE q.id = ?
              GROUP BY q.id, q.q_name, q.total_marks";
    
    $result = $db->select($query, [$quiz_id], "i");
    return $result->fetch_assoc();
}

function getQuizParticipants($quiz_id) {
    $db = new Database();
    
    // JOIN multiple tables
    $query = "SELECT 
                s.id,
                s.name,
                s.roll,
                s.email,
                r.marks,
                r.total_marks,
                r.percentage,
                r.submitted_at
              FROM result r
              INNER JOIN student s ON r.s_id = s.id
              WHERE r.q_id = ?
              ORDER BY r.marks DESC";
    
    $result = $db->select($query, [$quiz_id], "i");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAbsentStudents($quiz_id, $instructor_id) {
    $db = new Database();
    
    // SUBQUERY - NOT IN
    $query = "SELECT s.id, s.name, s.roll, s.email
              FROM student s
              INNER JOIN class c ON s.id = c.s_id
              INNER JOIN quiz q ON c.t_id = q.t_id
              WHERE q.id = ? 
              AND c.status = 'approved'
              AND s.id NOT IN (
                  SELECT s_id FROM result WHERE q_id = ?
              )
              ORDER BY s.name";
    
    $result = $db->select($query, [$quiz_id, $quiz_id], "ii");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getStudentAnswerScript($quiz_id, $student_id) {
    $db = new Database();
    
    // Complex JOIN with CASE statement
    $query = "SELECT 
                ques.q_no,
                ques.question,
                ques.A,
                ques.B,
                ques.C,
                ques.D,
                ques.ans as correct_ans,
                sa.ans as student_ans,
                ques.marks,
                CASE 
                    WHEN sa.ans = ques.ans THEN ques.marks 
                    ELSE 0 
                END as marks_obtained
              FROM questions ques
              LEFT JOIN stud_ans sa ON ques.q_id = sa.q_id AND ques.q_no = sa.q_no AND sa.s_id = ?
              WHERE ques.q_id = ?
              ORDER BY ques.q_no";
    
    $result = $db->select($query, [$student_id, $quiz_id], "ii");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// ==================== STUDENT FUNCTIONS ====================

function studentRegister($roll, $name, $email, $password, $department = null) {
    $db = new Database();
    
    // CHECK: Email or roll already exists (LIKE with pattern matching)
    $check_query = "SELECT id FROM student WHERE email = ? OR roll = ?";
    $result = $db->select($check_query, [$email, $roll], "ss");
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Email or Roll already registered'];
    }
    
    // INSERT new student
    $hashed_password = md5($password);
    $insert_query = "INSERT INTO student (roll, name, email, password, department) VALUES (?, ?, ?, ?, ?)";
    $result = $db->execute($insert_query, [$roll, $name, $email, $hashed_password, $department], "sssss");
    
    if ($result['success']) {
        return ['success' => true, 'message' => 'Registration successful', 'id' => $result['insert_id']];
    }
    
    return ['success' => false, 'message' => 'Registration failed'];
}

function studentLogin($email, $password) {
    $db = new Database();
    
    // SELECT with WHERE
    $query = "SELECT id, roll, name, email FROM student WHERE email = ? AND password = ?";
    $hashed_password = md5($password);
    $result = $db->select($query, [$email, $hashed_password], "ss");
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = 'student';
        return ['success' => true, 'user' => $user];
    }
    
    return ['success' => false, 'message' => 'Invalid credentials'];
}

function getStudentDashboardStats($student_id) {
    $db = new Database();
    
    // AGGREGATE FUNCTIONS
    $query = "SELECT 
                COUNT(*) as total_quizzes_taken,
                AVG(percentage) as avg_percentage,
                SUM(marks) as total_marks_earned
              FROM result 
              WHERE s_id = ?";
    
    $result = $db->select($query, [$student_id], "i");
    $stats = $result->fetch_assoc();
    
    // Count missed quizzes (SUBQUERY)
    $missed_query = "SELECT COUNT(*) as total_missed
                     FROM quiz q
                     INNER JOIN class c ON q.t_id = c.t_id
                     WHERE c.s_id = ? 
                     AND c.status = 'approved'
                     AND q.expire_date <= NOW()
                     AND q.id NOT IN (SELECT q_id FROM result WHERE s_id = ?)";
    
    $missed_result = $db->select($missed_query, [$student_id, $student_id], "ii");
    $missed = $missed_result->fetch_assoc();
    $stats['total_missed'] = $missed['total_missed'];
    
    return $stats;
}

function getStudentPendingQuizzes($student_id) {
    $db = new Database();
    
    // Complex JOIN with SUBQUERY
    $query = "SELECT 
                q.id,
                q.q_name,
                q.expire_date,
                q.duration,
                q.total_marks,
                i.name as instructor_name,
                COUNT(DISTINCT ques.q_no) as question_count
              FROM quiz q
              INNER JOIN instructor i ON q.t_id = i.id
              INNER JOIN class c ON i.id = c.t_id
              LEFT JOIN questions ques ON q.id = ques.q_id
              WHERE c.s_id = ? 
              AND c.status = 'approved'
              AND q.expire_date > NOW()
              AND q.id NOT IN (SELECT q_id FROM result WHERE s_id = ?)
              GROUP BY q.id
              ORDER BY q.expire_date ASC";
    
    $result = $db->select($query, [$student_id, $student_id], "ii");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getStudentTakenQuizzes($student_id) {
    $db = new Database();
    
    // JOIN with WHERE
    $query = "SELECT 
                q.id,
                q.q_name,
                q.expire_date,
                q.total_marks,
                i.name as instructor_name,
                r.marks,
                r.percentage,
                r.submitted_at
              FROM result r
              INNER JOIN quiz q ON r.q_id = q.id
              INNER JOIN instructor i ON q.t_id = i.id
              WHERE r.s_id = ?
              ORDER BY r.submitted_at DESC";
    
    $result = $db->select($query, [$student_id], "i");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getStudentFinishedQuizzes($student_id) {
    $db = new Database();
    
    // Get all expired quizzes from subscribed instructors
    $query = "SELECT 
                q.id,
                q.q_name,
                q.expire_date,
                q.total_marks,
                i.name as instructor_name,
                COUNT(DISTINCT r.s_id) as total_participants,
                AVG(r.percentage) as avg_percentage
              FROM quiz q
              INNER JOIN instructor i ON q.t_id = i.id
              INNER JOIN class c ON i.id = c.t_id
              LEFT JOIN result r ON q.id = r.q_id
              WHERE c.s_id = ? 
              AND c.status = 'approved'
              AND q.expire_date <= NOW()
              GROUP BY q.id
              ORDER BY q.expire_date DESC";
    
    $result = $db->select($query, [$student_id], "i");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getStudentSubscribedInstructors($student_id) {
    $db = new Database();
    
    // INNER JOIN
    $query = "SELECT 
                i.id,
                i.name,
                i.email,
                c.approved_at
              FROM instructor i
              INNER JOIN class c ON i.id = c.t_id
              WHERE c.s_id = ? AND c.status = 'approved'
              ORDER BY i.name";
    
    $result = $db->select($query, [$student_id], "i");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function searchInstructors($search_term) {
    $db = new Database();
    
    // Check if search term is numeric (searching by ID)
    if (is_numeric($search_term)) {
        $query = "SELECT id, name, email 
                  FROM instructor 
                  WHERE id = ?
                  ORDER BY name
                  LIMIT 20";
        $result = $db->select($query, [intval($search_term)], "i");
    } else {
        // LIKE pattern matching with REGEXP for name or email
        $query = "SELECT id, name, email 
                  FROM instructor 
                  WHERE name LIKE ? OR email LIKE ?
                  ORDER BY name
                  LIMIT 20";
        
        $search_pattern = "%$search_term%";
        $result = $db->select($query, [$search_pattern, $search_pattern], "ss");
    }
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function sendClassJoinRequest($student_id, $instructor_id) {
    $db = new Database();
    
    // Check if instructor exists
    $check_instructor = "SELECT id FROM instructor WHERE id = ?";
    $instructor_result = $db->select($check_instructor, [$instructor_id], "i");
    
    if ($instructor_result->num_rows === 0) {
        return ['success' => false, 'message' => 'Instructor not found'];
    }
    
    // Check if already exists
    $check_query = "SELECT * FROM class WHERE t_id = ? AND s_id = ?";
    $check_result = $db->select($check_query, [$instructor_id, $student_id], "ii");
    
    if ($check_result->num_rows > 0) {
        $existing = $check_result->fetch_assoc();
        if ($existing['status'] === 'approved') {
            return ['success' => false, 'message' => 'You are already enrolled in this class'];
        } elseif ($existing['status'] === 'pending') {
            return ['success' => false, 'message' => 'Your request is already pending approval'];
        } else {
            return ['success' => false, 'message' => 'Your previous request was rejected'];
        }
    }
    
    // INSERT class join request
    $query = "INSERT INTO class (t_id, s_id, status) VALUES (?, ?, 'pending')";
    $result = $db->execute($query, [$instructor_id, $student_id], "ii");
    
    if ($result['success']) {
        return ['success' => true, 'message' => 'Request sent successfully'];
    }
    
    return ['success' => false, 'message' => 'Failed to send request'];
}

function getQuizQuestions($quiz_id, $student_id) {
    $db = new Database();
    
    // Verify student has access and quiz is not expired
    $verify_query = "SELECT q.* 
                     FROM quiz q
                     INNER JOIN class c ON q.t_id = c.t_id
                     WHERE q.id = ? 
                     AND c.s_id = ? 
                     AND c.status = 'approved'
                     AND q.expire_date > NOW()
                     AND q.id NOT IN (SELECT q_id FROM result WHERE s_id = ?)";
    
    $verify_result = $db->select($verify_query, [$quiz_id, $student_id, $student_id], "iii");
    
    if ($verify_result->num_rows === 0) {
        return null;
    }
    
    // Get questions
    $query = "SELECT q_id, q_no, question, A, B, C, D, marks 
              FROM questions 
              WHERE q_id = ? 
              ORDER BY q_no";
    
    $result = $db->select($query, [$quiz_id], "i");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function submitQuizAnswers($quiz_id, $student_id, $answers) {
    $db = new Database();
    
    try {
        $db->beginTransaction();
        
        // INSERT student answers
        foreach ($answers as $q_no => $answer) {
            $query = "INSERT INTO stud_ans (s_id, q_id, q_no, ans) 
                      VALUES (?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE ans = ?";
            $db->execute($query, [$student_id, $quiz_id, $q_no, $answer, $answer], "iiiss");
        }
        
        // Calculate result using AGGREGATE and CASE
        $calc_query = "SELECT 
                        SUM(CASE WHEN sa.ans = ques.ans THEN ques.marks ELSE 0 END) as marks,
                        SUM(ques.marks) as total_marks
                       FROM questions ques
                       LEFT JOIN stud_ans sa ON ques.q_id = sa.q_id AND ques.q_no = sa.q_no AND sa.s_id = ?
                       WHERE ques.q_id = ?";
        
        $calc_result = $db->select($calc_query, [$student_id, $quiz_id], "ii");
        $calc_data = $calc_result->fetch_assoc();
        
        $marks = $calc_data['marks'] ?? 0;
        $total_marks = $calc_data['total_marks'];
        $percentage = calculatePercentage($marks, $total_marks);
        
        // INSERT result
        $result_query = "INSERT INTO result (q_id, s_id, marks, total_marks, percentage) 
                        VALUES (?, ?, ?, ?, ?)";
        $db->execute($result_query, [$quiz_id, $student_id, $marks, $total_marks, $percentage], "iiidi");
        
        $db->commit();
        
        return ['success' => true, 'marks' => $marks, 'total_marks' => $total_marks, 'percentage' => $percentage];
        
    } catch (Exception $e) {
        $db->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function canViewAnswerScript($quiz_id, $student_id) {
    $db = new Database();
    
    // Check if quiz is expired
    $query = "SELECT expire_date FROM quiz WHERE id = ?";
    $result = $db->select($query, [$quiz_id], "i");
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $quiz = $result->fetch_assoc();
    return isQuizExpired($quiz['expire_date']);
}

// ==================== COMMON FUNCTIONS ====================

function getQuizDetails($quiz_id) {
    $db = new Database();
    
    // NATURAL JOIN simulation
    $query = "SELECT 
                q.*,
                i.name as instructor_name,
                i.email as instructor_email,
                COUNT(DISTINCT ques.q_no) as total_questions
              FROM quiz q
              INNER JOIN instructor i ON q.t_id = i.id
              LEFT JOIN questions ques ON q.id = ques.q_id
              WHERE q.id = ?
              GROUP BY q.id";
    
    $result = $db->select($query, [$quiz_id], "i");
    return $result->fetch_assoc();
}

function deleteQuiz($quiz_id, $instructor_id) {
    $db = new Database();
    
    // Verify ownership then DELETE (CASCADE will handle related records)
    $query = "DELETE FROM quiz WHERE id = ? AND t_id = ?";
    return $db->execute($query, [$quiz_id, $instructor_id], "ii");
}

function updateQuiz($quiz_id, $instructor_id, $q_name, $expire_date, $duration, $description) {
    $db = new Database();
    
    // UPDATE with multiple fields
    $query = "UPDATE quiz 
              SET q_name = ?, expire_date = ?, duration = ?, description = ?
              WHERE id = ? AND t_id = ?";
    
    return $db->execute($query, [$q_name, $expire_date, $duration, $description, $quiz_id, $instructor_id], "sssiii");
}
?>