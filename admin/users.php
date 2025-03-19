<?php
// Include config file
require_once '../config.php';

// Start session
session_start();

// Check if user is logged in and has admin role
requireRole('admin');

// Process user deletion if requested
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $userId = $_GET['id'];
    
    // Don't allow deleting the current user
    if ($userId == $_SESSION['user_id']) {
        $deleteError = "You cannot delete your own account.";
    } else {
        // Check if user exists
        $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
        $checkStmt->bind_param("i", $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows == 1) {
            // Delete user
            $deleteStmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $deleteStmt->bind_param("i", $userId);
            
            if ($deleteStmt->execute()) {
                $deleteSuccess = "User deleted successfully.";
            } else {
                $deleteError = "Error deleting user. Please try again.";
            }
            
            $deleteStmt->close();
        } else {
            $deleteError = "User not found.";
        }
        
        $checkStmt->close();
    }
}

// Get all users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Filter by role if provided
$roleFilter = "";
$params = [];
$types = "";

if (isset($_GET['role']) && in_array($_GET['role'], ['admin', 'lecturer', 'student', 'hod'])) {
    $roleFilter = "WHERE role = ?";
    $params[] = $_GET['role'];
    $types .= "s";
}

// Count total records for pagination
$countQuery = "SELECT COUNT(*) as total FROM users $roleFilter";
$countStmt = $conn->prepare($countQuery);

if (!empty($types)) {
    $countStmt->bind_param($types, ...$params);
}

$countStmt->execute();
$totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get users for current page
$query = "SELECT user_id, email, role, first_name, last_name, created_at FROM users $roleFilter ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $recordsPerPage;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Page title
$pageTitle = "Manage Users";
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
                <li><a href="users.php" class="active">Manage Users</a></li>
                <li><a href="courses.php">Manage Courses</a></li>
                <li><a href="lecturers.php">Manage Lecturers</a></li>
                <li><a href="hod/hod.php">Manage HOD</a></li>
                <li><a href="students.php">Manage Students</a></li>
                <li><a href="register_student.php">Register Student</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2><?php echo $pageTitle; ?></h2>
                <div>
                    <a href="register_student.php" class="btn">Register Student</a>
                    <!-- Add more action buttons as needed -->
                </div>
            </div>
            
            <!-- Filter options -->
            <div class="card">
                <form action="" method="get" class="filter-form">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <div>
                            <label for="role">Filter by Role:</label>
                            <select name="role" id="role" class="form-control" style="width: auto;">
                                <option value="">All Roles</option>
                                <option value="admin" <?php echo (isset($_GET['role']) && $_GET['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="lecturer" <?php echo (isset($_GET['role']) && $_GET['role'] == 'lecturer') ? 'selected' : ''; ?>>Lecturer</option>
                                <option value="student" <?php echo (isset($_GET['role']) && $_GET['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                                <option value="hod" <?php echo (isset($_GET['role']) && $_GET['role'] == 'hod') ? 'selected' : ''; ?>>HOD</option>
                            </select>
                        </div>
                        <button type="submit" class="btn">Apply Filter</button>
                        <?php if (isset($_GET['role'])): ?>
                            <a href="users.php" class="btn" style="background-color: #7f8c8d;">Clear Filter</a>
                        <?php endif; ?>
                    </div>
                </form>
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
            
            <!-- Users Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($user = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['role']; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm">Edit</a>
                                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                                <a href="users.php?action=delete&id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger delete-btn">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination" style="margin-top: 20px; text-align: center;">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo ($page - 1); ?><?php echo isset($_GET['role']) ? '&role=' . $_GET['role'] : ''; ?>" class="btn btn-sm">&laquo; Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="btn btn-sm" style="background-color: #2c3e50;"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo isset($_GET['role']) ? '&role=' . $_GET['role'] : ''; ?>" class="btn btn-sm"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo ($page + 1); ?><?php echo isset($_GET['role']) ? '&role=' . $_GET['role'] : ''; ?>" class="btn btn-sm">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>