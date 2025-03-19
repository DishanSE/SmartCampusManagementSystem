<?php
// Include config file
require_once '../config.php';

// Start session
session_start();

// Check if user is logged in and has student role
requireRole('student');

// Page title
$pageTitle = "Student Dashboard";

// Get student's enrolled courses
$stmt = $conn->prepare("
    SELECT c.*, u.first_name, u.last_name 
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    JOIN users u ON c.lecturer_id = u.user_id
    WHERE e.student_id = ? AND e.status = 'active'
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();

// Get upcoming assignments
$assignmentStmt = $conn->prepare("
    SELECT a.*, lm.title, c.title as course_title
    FROM assignments a
    JOIN learning_materials lm ON a.material_id = lm.material_id
    JOIN courses c ON lm.course_id = c.course_id
    JOIN enrollments e ON c.course_id = e.course_id
    WHERE e.student_id = ? AND a.deadline > NOW()
    ORDER BY a.deadline ASC
    LIMIT 5
");
$assignmentStmt->bind_param("i", $_SESSION['user_id']);
$assignmentStmt->execute();
$assignments = $assignmentStmt->get_result();
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
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .course-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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
        
        .lecturer-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .lecturer-info span {
            margin-left: 10px;
            font-weight: 500;
        }
        
        .card-actions {
            display: flex;
            justify-content: center;
        }
        
        .card-actions a {
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            color: white;
            font-size: 14px;
            background-color: #3498db;
        }
        
        .sidebar-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .sidebar-card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
        }
        
        .assignment-item {
            padding: 10px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .assignment-item:last-child {
            border-bottom: none;
        }
        
        .assignment-item h4 {
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .assignment-item p {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .assignment-deadline {
            font-size: 14px;
            color: #e74c3c;
        }
        
        .no-courses {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            grid-column: span 2;
        }
        
        .no-courses h3 {
            margin-bottom: 20px;
            color: #7f8c8d;
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
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>LMS Student</h3>
                <p><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="postEvent.php">Events</a></li>
                <li><a href="my_courses.php">My Courses</a></li>
                <li><a href="assignments.php">Assignments</a></li>
                <li><a href="materials.php">Learning Materials</a></li>
                <li><a href="grades.php">Grades</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2>Welcome, <?php echo $_SESSION['first_name']; ?>!</h2>
            </div>
            
            <?php if ($courses->num_rows > 0): ?>
                <div class="dashboard-grid">
                    <div>
                        <h3>Your Courses</h3>
                        <div class="course-cards">
                            <?php while ($course = $courses->fetch_assoc()): ?>
                                <div class="course-card">
                                    <h3><?php echo $course['title']; ?></h3>
                                    <p><?php echo $course['course_code']; ?></p>
                                    
                                    <div class="lecturer-info">
                                        <span>Lecturer: <?php echo $course['first_name'] . ' ' . $course['last_name']; ?></span>
                                    </div>
                                    
                                    <div class="card-actions">
                                        <a href="view_course.php?id=<?php echo $course['course_id']; ?>">View Course</a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    
                    <div>
                        <div class="sidebar-card">
                            <h3>Upcoming Assignments</h3>
                            
                            <?php if ($assignments->num_rows > 0): ?>
                                <?php while ($assignment = $assignments->fetch_assoc()): ?>
                                    <div class="assignment-item">
                                        <h4><?php echo $assignment['title']; ?></h4>
                                        <p><?php echo $assignment['course_title']; ?></p>
                                        <div class="assignment-deadline">
                                            Due: <?php echo date('M j, Y, g:i a', strtotime($assignment['deadline'])); ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No upcoming assignments.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-courses">
                    <h3>You are not enrolled in any courses yet.</h3>
                    <p>Please contact your administrator for course enrollment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>