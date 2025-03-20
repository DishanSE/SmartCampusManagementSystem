<?php
// Include config file
require_once '../config.php';

// Start session
session_start();

// Check if user is logged in and has admin role
requireRole('admin');

// Page title
$pageTitle = "Admin Dashboard";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCMS - <?php echo $pageTitle; ?></title>
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
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .recent-activity {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .recent-activity h3 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .activity-item:last-child {
            border-bottom: none;
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
                <h3>SCMS Admin</h3>
                <p><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="page.php">Manage Event</a></li>
                <li><a href="resource.php">Manage Resource</a></li>
                <li><a href="users.php">Manage Users</a></li>
                <li><a href="courses.php">Manage Courses</a></li>
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
                <a href="../scheduling/admin/login.php" class="btn">Scheduling</a>
                <a href="https://teams.microsoft.com/" target="_blank" class="btn">Chat</a>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-container">
                <?php
                // Get total students count
                $studentsQuery = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
                $studentsResult = $conn->query($studentsQuery);
                $studentsCount = $studentsResult->fetch_assoc()['total'];
                
                // Get total lecturers count
                $lecturersQuery = "SELECT COUNT(*) as total FROM users WHERE role = 'lecturer'";
                $lecturersResult = $conn->query($lecturersQuery);
                $lecturersCount = $lecturersResult->fetch_assoc()['total'];
                
                // Get total courses count
                $coursesQuery = "SELECT COUNT(*) as total FROM courses";
                $coursesResult = $conn->query($coursesQuery);
                $coursesCount = $coursesResult->fetch_assoc()['total'];
                
                // Get total materials count
                $materialsQuery = "SELECT COUNT(*) as total FROM learning_materials";
                $materialsResult = $conn->query($materialsQuery);
                $materialsCount = $materialsResult->fetch_assoc()['total'];
                ?>
                
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <p><?php echo $studentsCount; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Lecturers</h3>
                    <p><?php echo $lecturersCount; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Courses</h3>
                    <p><?php echo $coursesCount; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Learning Materials</h3>
                    <p><?php echo $materialsCount; ?></p>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="recent-activity">
                <h3>Recent Activity</h3>
                
                <?php
                // Get recent enrollments
                $recentActivityQuery = "
                    SELECT e.enrollment_date, u.first_name, u.last_name, c.title
                    FROM enrollments e
                    JOIN users u ON e.student_id = u.user_id
                    JOIN courses c ON e.course_id = c.course_id
                    ORDER BY e.enrollment_date DESC
                    LIMIT 5
                ";
                $recentActivityResult = $conn->query($recentActivityQuery);
                
                if ($recentActivityResult->num_rows > 0) {
                    while ($activity = $recentActivityResult->fetch_assoc()) {
                        echo '<div class="activity-item">';
                        echo '<p><strong>' . $activity['first_name'] . ' ' . $activity['last_name'] . '</strong> enrolled in <strong>' . $activity['title'] . '</strong></p>';
                        echo '<small>' . date('F j, Y, g:i a', strtotime($activity['enrollment_date'])) . '</small>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="activity-item">';
                    echo '<p>No recent activity</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>