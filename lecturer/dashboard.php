<?php
// Include config file
require_once '../config.php';

// Start session
session_start();

// Check if user is logged in and has lecturer role
requireRole('lecturer');

// Page title
$pageTitle = "Lecturer Dashboard";

// Get lecturer's courses
$stmt = $conn->prepare("SELECT * FROM courses WHERE lecturer_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #34495e;
            text-align: center;
        }
        
        .sidebar-header h3 {
            margin-bottom: 5px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 30px 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: block;
            color: #ecf0f1;
            text-decoration: none;
            padding: 12px 20px;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: #3498db;
            color: white;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }
        
        .course-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .course-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
        }
        
        .course-card h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .course-card p {
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        
        .student-count {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .student-count span {
            margin-left: 10px;
            font-weight: 500;
        }
        
        .card-actions {
            display: flex;
            justify-content: space-between;
        }
        
        .card-actions a {
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            color: white;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: #3498db;
        }
        
        .btn-secondary {
            background-color: #2ecc71;
        }
        
        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .no-courses {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
        }
        
        .no-courses h3 {
            margin-bottom: 20px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>LMS Lecturer</h3>
                <p><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="my_courses.php">My Courses</a></li>
                <li><a href="materials.php">Learning Materials</a></li>
                <li><a href="assignments.php">Assignments</a></li>
                <li><a href="students.php">My Students</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2><?php echo $pageTitle; ?></h2>
            </div>
            
            <?php if ($courses->num_rows > 0): ?>
                <div class="course-cards">
                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <?php
                        // Get number of students enrolled in this course
                        $studentQuery = "SELECT COUNT(*) as count FROM enrollments WHERE course_id = ?";
                        $studentStmt = $conn->prepare($studentQuery);
                        $studentStmt->bind_param("i", $course['course_id']);
                        $studentStmt->execute();
                        $studentResult = $studentStmt->get_result();
                        $studentCount = $studentResult->fetch_assoc()['count'];
                        ?>
                        
                        <div class="course-card">
                            <h3><?php echo $course['title']; ?></h3>
                            <p><?php echo $course['course_code']; ?></p>
                            
                            <div class="student-count">
                                <span><?php echo $studentCount; ?> Students Enrolled</span>
                            </div>
                            
                            <div class="card-actions">
                                <a href="view_course.php?id=<?php echo $course['course_id']; ?>" class="btn-primary">Manage Course</a>
                                <a href="materials.php?course_id=<?php echo $course['course_id']; ?>" class="btn-secondary">Materials</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-courses">
                    <h3>You haven't been assigned any courses yet.</h3>
                    <p>Please contact the administrator if you believe this is an error.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>