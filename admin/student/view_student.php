<?php
// Include config file
require_once '../../config.php';

// Start session
session_start();

// Check if user is logged in and has admin role
requireRole('admin');

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: students.php");
    exit;
}

$studentId = $_GET['id'];

// Get student data
$stmt = $conn->prepare("
    SELECT user_id, email, first_name, last_name, created_at, profile_picture 
    FROM users 
    WHERE user_id = ? AND role = 'student'
");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    // Student not found
    header("location: students.php");
    exit;
}

$student = $result->fetch_assoc();

// Get courses enrolled by this student
$coursesStmt = $conn->prepare("
    SELECT c.course_id, c.course_code, c.title, e.enrollment_date, e.status,
           u.first_name, u.last_name
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    JOIN users u ON c.lecturer_id = u.user_id
    WHERE e.student_id = ? 
    ORDER BY e.enrollment_date DESC
");
$coursesStmt->bind_param("i", $studentId);
$coursesStmt->execute();
$coursesResult = $coursesStmt->get_result();

// Page title
$pageTitle = "View Student";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .profile-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }
        
        .profile-sidebar {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            border: 4px solid #f1f1f1;
        }
        
        .profile-details {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .profile-details h3 {
            margin-bottom: 5px;
        }
        
        .profile-details p {
            color: #7f8c8d;
        }
        
        .profile-action {
            margin-top: 20px;
        }
        
        .profile-main {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .section-title {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f1f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-title h3 {
            margin-bottom: 0;
        }
        
        .course-item {
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            background-color: #f8f9fa;
            transition: transform 0.3s;
        }
        
        .course-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }
        
        .course-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .course-code {
            font-weight: bold;
            color: #3498db;
        }
        
        .course-date {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .course-lecturer {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .course-status {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 4px;
            color: white;
            font-size: 12px;
            margin-top: 10px;
        }
        
        .status-active {
            background-color: #2ecc71;
        }
        
        .status-completed {
            background-color: #3498db;
        }
        
        .status-dropped {
            background-color: #e74c3c;
        }
        
        .no-courses {
            padding: 20px;
            text-align: center;
            color: #7f8c8d;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>LMS Admin</h3>
                <p><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php">Dashboard</a></li>
                <li><a href="../users.php">Manage Users</a></li>
                <li><a href="../courses.php">Manage Courses</a></li>
                <li><a href="../lecturer/lecturers.php" >Manage Lecturers</a></li>
                <li><a href="../hod/hod.php">Manage HODs</a></li>
                <li><a href="students.php" class="active">Manage Students</a></li>
                <li><a href="register_student.php" >Register Student</a></li>
                <li><a href="../settings.php">Settings</a></li>
                <li><a href="../../logout.php">Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2><?php echo $pageTitle; ?></h2>
                <a href="students.php" class="btn">Back to Students</a>
            </div>
            
            <div class="profile-container">
                <div class="profile-sidebar">
                    <img src="<?php echo !empty($student['profile_picture']) ? $student['profile_picture'] : '../assets/images/default-profile.png'; ?>" alt="Profile Picture" class="profile-picture">
                    
                    <div class="profile-details">
                        <h3><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></h3>
                        <p><?php echo $student['email']; ?></p>
                        <p><strong>Student</strong></p>
                        <p>Joined: <?php echo date('F j, Y', strtotime($student['created_at'])); ?></p>
                    </div>
                    
                    <div class="profile-action">
                        <a href="edit_student.php?id=<?php echo $student['user_id']; ?>" class="btn">Edit Student</a>
                    </div>
                </div>
                
                <div class="profile-main">
                    <div class="section-title">
                        <h3>Enrolled Courses</h3>
                        <a href="enroll_student.php?id=<?php echo $student['user_id']; ?>" class="btn btn-sm">Enroll in Course</a>
                    </div>
                    
                    <?php if ($coursesResult->num_rows > 0): ?>
                        <?php while ($course = $coursesResult->fetch_assoc()): ?>
                            <div class="course-item">
                                <div class="course-header">
                                    <span class="course-code"><?php echo $course['course_code']; ?></span>
                                    <span class="course-date">Enrolled: <?php echo date('M j, Y', strtotime($course['enrollment_date'])); ?></span>
                                </div>
                                <h4><?php echo $course['title']; ?></h4>
                                <p class="course-lecturer">Lecturer: <?php echo $course['first_name'] . ' ' . $course['last_name']; ?></p>
                                
                                <?php 
                                $statusClass = '';
                                switch ($course['status']) {
                                    case 'active':
                                        $statusClass = 'status-active';
                                        break;
                                    case 'completed':
                                        $statusClass = 'status-completed';
                                        break;
                                    case 'dropped':
                                        $statusClass = 'status-dropped';
                                        break;
                                }
                                ?>
                                
                                <span class="course-status <?php echo $statusClass; ?>"><?php echo ucfirst($course['status']); ?></span>
                                
                                <div style="margin-top: 10px;">
                                    <a href="view_course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm" style="background-color: #2ecc71;">View Course</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-courses">
                            <p>This student is not enrolled in any courses yet.</p>
                            <a href="enroll_student.php?id=<?php echo $student['user_id']; ?>" class="btn" style="margin-top: 10px;">Enroll in Course</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>