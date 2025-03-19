<?php
// Include config file
require_once '../config.php';

// Start session
session_start();

// Check if user is logged in and has admin role
requireRole('admin');

// Define variables and initialize with empty values
$courseCode = $title = $description = $lecturerId = "";
$courseCode_err = $title_err = $description_err = $lecturerId_err = "";
$success_message = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate course code
    if (empty(trim($_POST["course_code"]))) {
        $courseCode_err = "Please enter a course code.";
    } else {
        // Prepare a select statement
        $sql = "SELECT course_id FROM courses WHERE course_code = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_courseCode);
            
            // Set parameters
            $param_courseCode = trim($_POST["course_code"]);
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $courseCode_err = "This course code is already taken.";
                } else {
                    $courseCode = trim($_POST["course_code"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
    
    // Validate title
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";     
    } else {
        $title = trim($_POST["title"]);
    }
    
    // Validate description (optional)
    $description = trim($_POST["description"]);
    
    // Validate lecturer
    if (empty(trim($_POST["lecturer_id"]))) {
        $lecturerId_err = "Please select a lecturer.";     
    } else {
        $lecturerId = trim($_POST["lecturer_id"]);
        
        // Check if lecturer exists and is actually a lecturer
        $checkLecturerSql = "SELECT user_id FROM users WHERE user_id = ? AND role = 'lecturer'";
        if ($checkStmt = $conn->prepare($checkLecturerSql)) {
            $checkStmt->bind_param("i", $lecturerId);
            if ($checkStmt->execute()) {
                $checkStmt->store_result();
                if ($checkStmt->num_rows != 1) {
                    $lecturerId_err = "Invalid lecturer selected.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $checkStmt->close();
        }
    }
    
    // Check input errors before inserting in database
    if (empty($courseCode_err) && empty($title_err) && empty($lecturerId_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO courses (course_code, title, description, lecturer_id) VALUES (?, ?, ?, ?)";
         
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssi", $param_courseCode, $param_title, $param_description, $param_lecturerId);
            
            // Set parameters
            $param_courseCode = $courseCode;
            $param_title = $title;
            $param_description = $description;
            $param_lecturerId = $lecturerId;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Set success message
                $success_message = "Course created successfully.";
                
                // Clear form data
                $courseCode = $title = $description = $lecturerId = "";
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
}

// Get all lecturers for dropdown
$lecturersQuery = "SELECT user_id, first_name, last_name, email FROM users WHERE role = 'lecturer' ORDER BY first_name, last_name";
$lecturersResult = $conn->query($lecturersQuery);

// Page title
$pageTitle = "Create Course";
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
        <!-- Include sidebar -->
        <?php include_once '../includes/sidebar-admin.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2><?php echo $pageTitle; ?></h2>
                <a href="courses.php" class="btn">Back to Courses</a>
            </div>
            
            <div class="form-container">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="validate-form">
                    <div class="form-group">
                        <label>Course Code</label>
                        <input type="text" name="course_code" class="form-control <?php echo (!empty($courseCode_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $courseCode; ?>">
                        <span class="text-danger"><?php echo $courseCode_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
                        <span class="text-danger"><?php echo $title_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control rich-text-editor"><?php echo $description; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Lecturer</label>
                        <select name="lecturer_id" class="form-control <?php echo (!empty($lecturerId_err)) ? 'is-invalid' : ''; ?>">
                            <option value="">Select Lecturer</option>
                            <?php while ($lecturer = $lecturersResult->fetch_assoc()): ?>
                                <option value="<?php echo $lecturer['user_id']; ?>" <?php echo ($lecturer['user_id'] == $lecturerId) ? 'selected' : ''; ?>>
                                    <?php echo $lecturer['first_name'] . ' ' . $lecturer['last_name'] . ' (' . $lecturer['email'] . ')'; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <span class="text-danger"><?php echo $lecturerId_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">Create Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>