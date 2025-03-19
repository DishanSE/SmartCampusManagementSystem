<?php
// Include config file
require_once '../../config.php';

// Start session
session_start();

// Check if user is logged in and has admin role
requireRole('admin');

// Check if lecturer ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: lecturers.php");
    exit;
}

$lecturerId = $_GET['id'];

// Get lecturer data
$stmt = $conn->prepare("
    SELECT user_id, email, first_name, last_name, created_at, profile_picture 
    FROM users 
    WHERE user_id = ? AND role = 'lecturer'
");
$stmt->bind_param("i", $lecturerId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    // Lecturer not found
    header("location: lecturers.php");
    exit;
}

$lecturer = $result->fetch_assoc();

// Get courses taught by this lecturer
$coursesStmt = $conn->prepare("
    SELECT course_id, course_code, title, created_at 
    FROM courses 
    WHERE lecturer_id = ? 
    ORDER BY created_at DESC
");
$coursesStmt->bind_param("i", $lecturerId);
$coursesStmt->execute();
$coursesResult = $coursesStmt->get_result();

// Page title
$pageTitle = "View Lecturer";
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
                <li><a href="lecturers.php" class="active">Manage Lecturers</a></li>
                <li><a href="../hod/hod.php">Manage HODs</a></li>
                <li><a href="../students.php">Manage Students</a></li>
                <li><a href="../register_student.php">Register Student</a></li>
                <li><a href="../settings.php">Settings</a></li>
                <li><a href="../../logout.php">Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2><?php echo $pageTitle; ?></h2>
                <a href="lecturers.php" class="btn">Back to Lecturers</a>
            </div>
            
            <div class="profile-container">
                <div class="profile-sidebar">
                    <img src="<?php echo !empty($lecturer['profile_picture']) ? $lecturer['profile_picture'] : '../assets/images/default-profile.png'; ?>" alt="Profile Picture" class="profile-picture">
                    
                    <div class="profile-details">
                        <h3><?php echo $lecturer['first_name'] . ' ' . $lecturer['last_name']; ?></h3>
                        <p><?php echo $lecturer['email']; ?></p>
                        <p><strong>Lecturer</strong></p>
                        <p>Joined: <?php echo date('F j, Y', strtotime($lecturer['created_at'])); ?></p>
                    </div>
                    
                    <div class="profile-action">
                        <a href="edit_lecturer.php?id=<?php echo $lecturer['user_id']; ?>" class="btn">Edit Lecturer</a>
                    </div>
                </div>
                
                <div class="profile-main">
                    <h3 class="section-title">Courses</h3>
                    
                    <?php if ($coursesResult->num_rows > 0): ?>
                        <?php while ($course = $coursesResult->fetch_assoc()): ?>
                            <div class="course-item">
                                <div class="course-header">
                                    <span class="course-code"><?php echo $course['course_code']; ?></span>
                                    <span class="course-date">Created: <?php echo date('M j, Y', strtotime($course['created_at'])); ?></span>
                                </div>
                                <h4><?php echo $course['title']; ?></h4>
                                <div style="margin-top: 10px;">
                                    <a href="view_course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm" style="background-color: #2ecc71;">View Course</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-courses">
                            <p>This lecturer is not assigned to any courses yet.</p>
                            <a href="create_course.php" class="btn" style="margin-top: 10px;">Create New Course</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>