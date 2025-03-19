<?php
// Check if the user is logged in and has admin role
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../unauthorized.php');
    exit;
}

// Get the current page filename without directory path
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3>LMS Admin</h3>
        <p><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" <?php echo ($currentPage == 'dashboard.php') ? 'class="active"' : ''; ?>>Dashboard</a></li>
        <li><a href="users.php" <?php echo ($currentPage == 'users.php') ? 'class="active"' : ''; ?>>Manage Users</a></li>
        <li><a href="courses.php" <?php echo ($currentPage == 'courses.php') ? 'class="active"' : ''; ?>>Manage Courses</a></li>
        <li><a href="lecturers.php" <?php echo ($currentPage == 'lecturers.php') ? 'class="active"' : ''; ?>>Manage Lecturers</a></li>
        <li><a href="students.php" <?php echo ($currentPage == 'students.php') ? 'class="active"' : ''; ?>>Manage Students</a></li>
        <li><a href="register_student.php" <?php echo ($currentPage == 'register_student.php') ? 'class="active"' : ''; ?>>Register Student</a></li>
        <li><a href="settings.php" <?php echo ($currentPage == 'settings.php') ? 'class="active"' : ''; ?>>Settings</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</div>