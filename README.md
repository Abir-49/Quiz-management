# Quiz-management
# Quiz Management System

A comprehensive web-based quiz management system built with PHP, MySQL, HTML, CSS, and JavaScript. This project demonstrates all essential database concepts for database lab coursework.

## 📋 Features

### Instructor Features
- Create and manage quizzes with custom questions
- Set quiz duration and expiry dates
- View quiz results and statistics
- See participant lists and absent students
- Review individual student answer scripts
- Approve/reject student join requests
- Manage classroom students

### Student Features
- Search and join instructor classes
- Take quizzes within time limits
- View scores and performance statistics
- See answer scripts after quiz expiry
- Track pending and completed quizzes
- Compare results with classmates
- Monitor missed quizzes

## 🗄️ Database Concepts Implemented

All concepts with **practical applications**:

1. ✅ **Primary Key** - Unique identifiers for all tables
2. ✅ **Foreign Key** - Relationships with CASCADE operations
3. ✅ **Constraints** - CHECK, UNIQUE, NOT NULL validations
4. ✅ **INSERT** - User registration, quiz creation, answer submission
5. ✅ **UPDATE** - Profile updates, quiz modifications, approval status
6. ✅ **DELETE** - Remove old data, rejected requests
7. ✅ **ALTER** - Add new columns to existing tables
8. ✅ **SELECT with WHERE** - Filtered data retrieval
9. ✅ **Aggregate Functions** - COUNT, AVG, SUM, MAX, MIN
10. ✅ **GROUP BY** - Categorized statistics
11. ✅ **HAVING** - Filtered group results
12. ✅ **Subqueries** - Complex conditions, nested queries
13. ✅ **LIKE** - Pattern matching for search
14. ✅ **REGEXP** - Email validation, pattern matching
15. ✅ **Set Operations** - UNION, INTERSECT, EXCEPT simulations
16. ✅ **JOINs** - INNER, LEFT, RIGHT, NATURAL, Multiple JOINs
17. ✅ **VIEW** - Simplified recurring queries

## 🛠️ Installation

### Prerequisites
- XAMPP (or any PHP + MySQL environment)
- Web browser
- Text editor (VS Code, Sublime, etc.)

### Step-by-Step Installation

#### 1. Install XAMPP
- Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
- Install and start Apache and MySQL services

#### 2. Setup Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create new database: `quiz_management`
3. Import the SQL schema:
   - Click on `quiz_management` database
   - Go to "Import" tab
   - Select the `quiz_db_schema.sql` file
   - Click "Go"

**OR** Run the SQL commands manually:
```sql
-- Copy all SQL from 'quiz_db_schema.sql' artifact
-- Paste in phpMyAdmin SQL tab
-- Click "Go"
```

#### 3. Setup Project Files

1. Navigate to XAMPP htdocs folder:
   - Windows: `C:\xampp\htdocs\`
   - Mac: `/Applications/XAMPP/htdocs/`
   - Linux: `/opt/lampp/htdocs/`

2. Create project folder:
```bash
mkdir quiz_management
cd quiz_management
```

3. Create the following directory structure:
```
quiz_management/
├── config.php
├── functions.php
├── login.php
├── register.php
├── logout.php
├── instructor_dashboard.php
├── student_dashboard.php
├── create_quiz.php
├── take_quiz.php
├── quiz_results.php
├── view_answer_script.php
├── student_quiz_results.php
├── css/
│   └── style.css
└── js/
    └── script.js (optional)
```

4. Copy all PHP files from the artifacts into respective locations
5. Copy CSS file into `css/` folder

#### 4. Configure Database Connection

Edit `config.php` and update database credentials if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Default is empty for XAMPP
define('DB_NAME', 'quiz_management');
```

#### 5. Access the Application

1. Open web browser
2. Navigate to: `http://localhost/quiz_management/login.php`
3. Register as instructor or student
4. Start using the system!

## 📁 File Structure Explanation

### Core Files
- **config.php** - Database configuration and connection class
- **functions.php** - All business logic and database operations
- **login.php** - Login page for both user types
- **register.php** - Registration for instructors and students
- **logout.php** - Session destruction

### Instructor Files
- **instructor