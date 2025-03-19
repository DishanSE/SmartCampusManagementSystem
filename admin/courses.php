<?php
// Include config file
require_once '../config.php';

// Start session
session_start();

// Check if user is logged in and has admin role
requireRole('admin');

// Process course deletion if requested
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $courseId = $_GET['id'];
    
    // Check if course exists
    $checkStmt = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ?");
    $checkStmt->bind_param("i", $courseId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows == 1) {
        // Delete course
        $deleteStmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
        $deleteStmt->bind_param("i", $courseId);
        
        if ($deleteStmt->execute()) {
            $deleteSuccess = "Course deleted successfully.";
        } else {
            $deleteError = "Error deleting course. Please try again.";
        }
        
        $deleteStmt->close();
    } else {
        $deleteError = "Course not found.";
    }
    
    $checkStmt->close();
}

// Get all courses with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Count total records for pagination
$countQuery = "SELECT COUNT(*) as total FROM courses";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute();
$totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get courses for current page
$query = "
    SELECT c.*, u.first_name, u.last_name 
    FROM courses c
    LEFT JOIN users u ON c.lecturer_id = u.user_id
    ORDER BY c.created_at DESC 
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $recordsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Page title
$pageTitle = "Manage Courses";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
                <li><a href="student/register_student.php">Register Student</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2><?php echo $pageTitle; ?></h2>
                <a href="create_course.php" class="btn">Create New Course</a>
            </div>
            
            <!-- Notifications -->
            <?php if (isset($deleteSuccess)): ?>
                <div class="alert alert-success">
                    <?php echo $deleteSuccess; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($deleteError)): ?>
                <div class="alert alert-danger">
                    <?php echo $deleteError; ?>
                </div>
            <?php endif; ?>
            
            <!-- Courses Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Course Code</th>
                            <th>Title</th>
                            <th>Lecturer</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($course = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $course['course_id']; ?></td>
                                    <td><?php echo $course['course_code']; ?></td>
                                    <td><?php echo $course['title']; ?></td>
                                    <td>
                                        <?php 
                                        if ($course['lecturer_id']) {
                                            echo $course['first_name'] . ' ' . $course['last_name'];
                                        } else {
                                            echo '<span style="color: #e74c3c;">No Lecturer Assigned</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($course['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm">Edit</a>
                                            <a href="view_course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm" style="background-color: #2ecc71;">View</a>
                                            <a href="courses.php?action=delete&id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-danger delete-btn" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No courses found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination" style="margin-top: 20px; text-align: center;">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo ($page - 1); ?>" class="btn btn-sm">&laquo; Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="btn btn-sm" style="background-color: #2c3e50;"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>" class="btn btn-sm"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo ($page + 1); ?>" class="btn btn-sm">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>