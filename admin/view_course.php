<?php
// Include config file
require_once '../config.php';

// Start session
session_start();

// Check if user is logged in and has admin role
requireRole('admin');

// Check if course ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: courses.php");
    exit;
}

$courseId = $_GET['id'];

// Get course data
$stmt = $conn->prepare("
    SELECT c.*, u.first_name, u.last_name, u.email
    FROM courses c
    LEFT JOIN users u ON c.lecturer_id = u.user_id
    WHERE c.course_id = ?
");
$stmt->bind_param("i", $courseId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    // Course not found
    header("location: courses.php");
    exit;
}

$course = $result->fetch_assoc();

// Get enrolled students
$studentsStmt = $conn->prepare("
    SELECT u.user_id, u.first_name, u.last_name, u.email, e.enrollment_date, e.status
    FROM enrollments e
    JOIN users u ON e.student_id = u.user_id
    WHERE e.course_id = ?
    ORDER BY e.enrollment_date DESC
");
$studentsStmt->bind_param("i", $courseId);
$studentsStmt->execute();
$studentsResult = $studentsStmt->get_result();

// Get learning materials
$materialsStmt = $conn->prepare("
    SELECT material_id, title, material_type, created_at
    FROM learning_materials
    WHERE course_id = ?
    ORDER BY created_at DESC
");
$materialsStmt->bind_param("i", $courseId);
$materialsStmt->execute();
$materialsResult = $materialsStmt->get_result();

// Page title
$pageTitle = "View Course: " . $course['title'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .course-header {
            background-color: #3498db;
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .course-header h2 {
            margin-bottom: 10px;
        }
        
        .course-code {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .lecturer-info {
            margin-top: 15px;
            display: flex;
            align-items: center;
        }
        
        .lecturer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .course-details, .course-sidebar {
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
        
        .description {
            line-height: 1.6;
            color: #333;
            margin-bottom: 30px;
        }
        
        .material-item {
            padding: 15px;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .material-item:last-child {
            border-bottom: none;
        }
        
        .material-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .material-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: white;
        }
        
        .material-type.lesson {
            background-color: #3498db;
        }
        
        .material-type.assignment {
            background-color: #e74c3c;
        }
        
        .material-type.resource {
            background-color: #2ecc71;
        }
        
        .material-date {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .student-item {
            padding: 15px;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .student-item:last-child {
            border-bottom: none;
        }
        
        .student-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .student-email {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .enrollment-date {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .enrollment-status {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 4px;
            color: white;
            font-size: 12px;
            margin-top: 5px;
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
        
        .no-items {
            text-align: center;
            padding: 15px;
            color: #7f8c8d;
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">Manage Users</a></li>
                <li><a href="courses.php" class="active">Manage Courses</a></li>
                <li><a href="lecturer/lecturers.php">Manage Lecturers</a></li>
                <li><a href="hod/hod.php">Manage HOD</a></li>
                <li><a href="student/students.php">Manage Students</a></li>
                <li><a href="students/register_student.php">Register Student</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <a href="courses.php" class="btn" style="background-color: #7f8c8d;">Back to Courses</a>
                <div>
                    <a href="edit_course.php?id=<?php echo $courseId; ?>" class="btn">Edit Course</a>
                    <a href="add_material.php?course_id=<?php echo $courseId; ?>" class="btn" style="background-color: #2ecc71;">Add Material</a>
                </div>
            </div>
            
            <div class="course-header">
                <span class="course-code"><?php echo $course['course_code']; ?></span>
                <h2><?php echo $course['title']; ?></h2>
                
                <?php if ($course['lecturer_id']): ?>
                    <div class="lecturer-info">
                        <div class="lecturer-avatar">
                            <?php echo strtoupper(substr($course['first_name'], 0, 1) . substr($course['last_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <div><?php echo $course['first_name'] . ' ' . $course['last_name']; ?></div>
                            <div style="font-size: 14px; opacity: 0.8;"><?php echo $course['email']; ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="lecturer-info">
                        <div style="opacity: 0.8;">No lecturer assigned</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="content-grid">
                <div class="course-details">
                    <div class="section-title">
                        <h3>Course Description</h3>
                    </div>
                    
                    <div class="description">
                        <?php 
                        if (!empty($course['description'])) {
                            echo nl2br($course['description']);
                        } else {
                            echo '<p class="no-items">No description provided.</p>';
                        }
                        ?>
                    </div>
                    
                    <div class="section-title">
                        <h3>Learning Materials</h3>
                        <a href="add_material.php?course_id=<?php echo $courseId; ?>" class="btn btn-sm">Add Material</a>
                    </div>
                    
                    <?php if ($materialsResult->num_rows > 0): ?>
                        <?php while ($material = $materialsResult->fetch_assoc()): ?>
                            <div class="material-item">
                                <div class="material-header">
                                    <span class="material-type <?php echo $material['material_type']; ?>">
                                        <?php echo ucfirst($material['material_type']); ?>
                                    </span>
                                    <span class="material-date">
                                        <?php echo date('M j, Y', strtotime($material['created_at'])); ?>
                                    </span>
                                </div>
                                <h4><?php echo $material['title']; ?></h4>
                                <div style="margin-top: 10px;">
                                    <a href="view_material.php?id=<?php echo $material['material_id']; ?>" class="btn btn-sm">View</a>
                                    <a href="edit_material.php?id=<?php echo $material['material_id']; ?>" class="btn btn-sm">Edit</a>
                                    <a href="delete_material.php?id=<?php echo $material['material_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this material?');">Delete</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-items">No learning materials available for this course.</p>
                    <?php endif; ?>
                </div>
                
                <div class="course-sidebar">
                    <div class="section-title">
                        <h3>Enrolled Students</h3>
                        <a href="enroll_students.php?course_id=<?php echo $courseId; ?>" class="btn btn-sm">Enroll Students</a>
                    </div>
                    
                    <?php if ($studentsResult->num_rows > 0): ?>
                        <?php while ($student = $studentsResult->fetch_assoc()): ?>
                            <div class="student-item">
                                <div class="student-header">
                                    <h4><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></h4>
                                </div>
                                <div class="student-email"><?php echo $student['email']; ?></div>
                                <div class="enrollment-date">Enrolled: <?php echo date('M j, Y', strtotime($student['enrollment_date'])); ?></div>
                                
                                <?php 
                                $statusClass = '';
                                switch ($student['status']) {
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
                                
                                <span class="enrollment-status <?php echo $statusClass; ?>"><?php echo ucfirst($student['status']); ?></span>
                                
                                <div style="margin-top: 10px;">
                                    <a href="view_student.php?id=<?php echo $student['user_id']; ?>" class="btn btn-sm">View Student</a>
                                    <a href="update_enrollment.php?course_id=<?php echo $courseId; ?>&student_id=<?php echo $student['user_id']; ?>" class="btn btn-sm">Update Status</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-items">No students enrolled in this course.</p>
                    <?php endif; ?>
                    
                    <div class="section-title" style="margin-top: 30px;">
                        <h3>Course Details</h3>
                    </div>
                    
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 8px 0; color: #7f8c8d;">Created</td>
                            <td style="padding: 8px 0;"><?php echo date('F j, Y', strtotime($course['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #7f8c8d;">Last Updated</td>
                            <td style="padding: 8px 0;"><?php echo date('F j, Y', strtotime($course['updated_at'])); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #7f8c8d;">Students</td>
                            <td style="padding: 8px 0;"><?php echo $studentsResult->num_rows; ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #7f8c8d;">Materials</td>
                            <td style="padding: 8px 0;"><?php echo $materialsResult->num_rows; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>