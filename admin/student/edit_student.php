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

// Define variables and initialize with empty values
$email = $firstName = $lastName = "";
$email_err = $firstName_err = $lastName_err = $password_err = "";
$success_message = "";

// Get student data
$stmt = $conn->prepare("SELECT email, first_name, last_name FROM users WHERE user_id = ? AND role = 'student'");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    // Student not found
    header("location: students.php");
    exit;
}

$student = $result->fetch_assoc();
$email = $student['email'];
$firstName = $student['first_name'];
$lastName = $student['last_name'];

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        // Check if email is changed
        if ($email != trim($_POST["email"])) {
            // Prepare a select statement to check if email exists
            $sql = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
            
            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("si", $param_email, $studentId);
                
                // Set parameters
                $param_email = trim($_POST["email"]);
                
                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Store result
                    $stmt->store_result();
                    
                    if ($stmt->num_rows > 0) {
                        $email_err = "This email is already taken.";
                    } else {
                        $email = trim($_POST["email"]);
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                $stmt->close();
            }
        } else {
            $email = trim($_POST["email"]);
        }
    }
    
    // Validate first name
    if (empty(trim($_POST["first_name"]))) {
        $firstName_err = "Please enter first name.";     
    } else {
        $firstName = trim($_POST["first_name"]);
    }
    
    // Validate last name
    if (empty(trim($_POST["last_name"]))) {
        $lastName_err = "Please enter last name.";     
    } else {
        $lastName = trim($_POST["last_name"]);
    }
    
    // Validate password (optional for update)
    $password = trim($_POST["password"]);
    if (!empty($password) && strlen($password) < 6) {
        $password_err = "Password must have at least 6 characters.";
    }
    
    // Check input errors before updating the database
    if (empty($email_err) && empty($firstName_err) && empty($lastName_err) && empty($password_err)) {
        
        // Prepare an update statement
        if (empty($password)) {
            // Update without changing password
            $sql = "UPDATE users SET email = ?, first_name = ?, last_name = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $email, $firstName, $lastName, $studentId);
        } else {
            // Update including password
            $sql = "UPDATE users SET email = ?, password = ?, first_name = ?, last_name = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("ssssi", $email, $hashed_password, $firstName, $lastName, $studentId);
        }
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $success_message = "Student updated successfully.";
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    }
}

// Page title
$pageTitle = "Edit Student";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
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
                <li><a href="register_student.php">Register Student</a></li>
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
            
            <div class="form-container">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $studentId; ?>" method="post">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                        <span class="text-danger"><?php echo $email_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <small class="text-muted">Leave blank to keep current password</small>
                        <span class="text-danger"><?php echo $password_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control <?php echo (!empty($firstName_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $firstName; ?>">
                        <span class="text-danger"><?php echo $firstName_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control <?php echo (!empty($lastName_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $lastName; ?>">
                        <span class="text-danger"><?php echo $lastName_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">Update Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>