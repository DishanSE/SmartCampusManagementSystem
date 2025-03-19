<?php
// Check if the user is logged in and has lecturer role
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'lecturer') {
    header('Location: ../unauthorized.php');
    exit;
}

// Get the current page filename without directory path
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3>LMS Lecturer</h3>
        <p><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" <?php echo ($currentPage == 'dashboard.php') ? 'class="active"' : ''; ?>>Dashboard</a></li>
        <li><a href="my_courses.php" <?php echo ($currentPage == 'my_courses.php') ? 'class="active"' : ''; ?>>My Courses</a></li>
        <li><a href="materials.php" <?php echo ($currentPage == 'materials.php') ? 'class="active"' : ''; ?>>Learning Materials</a></li>
        <li><a href="assignments.php" <?php echo ($currentPage == 'assignments.php') ? 'class="active"' : ''; ?>>Assignments</a></li>
        <li><a href="students.php" <?php echo ($currentPage == 'students.php') ? 'class="active"' : ''; ?>>My Students</a></li>
        <li><a href="profile.php" <?php echo ($currentPage == 'profile.php') ? 'class="active"' : ''; ?>>Profile</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</div>