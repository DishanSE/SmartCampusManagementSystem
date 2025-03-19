<?php
// Include config file
require_once '../config.php';

// Start session
session_start();

// Check if user is logged in and has student role
requireRole('student');

// Define variables and initialize with empty values
$firstName = $lastName = $currentPassword = $newPassword = $confirmPassword = "";
$firstName_err = $lastName_err = $currentPassword_err = $newPassword_err = $confirmPassword_err = "";
$success_message = $error_message = "";

// Get user data
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, last_name, email, profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    $firstName = $user['first_name'];
    $lastName = $user['last_name'];
    $email = $user['email'];
    $profilePicture = $user['profile_picture'];
} else {
    // Redirect to login page if user not found
    header("location: ../logout.php");
    exit;
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check which form was submitted
    if (isset($_POST['update_profile'])) {
        // Update profile information
        
        // Validate first name
        if (empty(trim($_POST["first_name"]))) {
            $firstName_err = "Please enter your first name.";
        } else {
            $firstName = trim($_POST["first_name"]);
        }
        
        // Validate last name
        if (empty(trim($_POST["last_name"]))) {
            $lastName_err = "Please enter your last name.";
        } else {
            $lastName = trim($_POST["last_name"]);
        }
        
        // Check if there are no errors before updating the database
        if (empty($firstName_err) && empty($lastName_err)) {
            // Prepare an update statement
            $sql = "UPDATE users SET first_name = ?, last_name = ? WHERE user_id = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("ssi", $param_firstName, $param_lastName, $param_userId);
                
                // Set parameters
                $param_firstName = $firstName;
                $param_lastName = $lastName;
                $param_userId = $userId;
                
                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Update session variables
                    $_SESSION['first_name'] = $firstName;
                    $_SESSION['last_name'] = $lastName;
                    
                    $success_message = "Profile updated successfully.";
                } else {
                    $error_message = "Something went wrong. Please try again later.";
                }
                
                // Close statement
                $stmt->close();
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Change password
        
        // Validate current password
        if (empty(trim($_POST["current_password"]))) {
            $currentPassword_err = "Please enter your current password.";
        } else {
            $currentPassword = trim($_POST["current_password"]);
            
            // Check if the current password is correct
            $sql = "SELECT password FROM users WHERE user_id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $userId);
                if ($stmt->execute()) {
                    $stmt->store_result();
                    if ($stmt->num_rows == 1) {
                        $stmt->bind_result($hashed_password);
                        if ($stmt->fetch()) {
                            if (!password_verify($currentPassword, $hashed_password)) {
                                $currentPassword_err = "The current password is not correct.";
                            }
                        }
                    }
                }
                $stmt->close();
            }
        }
        
        // Validate new password
        if (empty(trim($_POST["new_password"]))) {
            $newPassword_err = "Please enter a new password.";     
        } elseif (strlen(trim($_POST["new_password"])) < 6) {
            $newPassword_err = "Password must have at least 6 characters.";
        } else {
            $newPassword = trim($_POST["new_password"]);
        }
        
        // Validate confirm password
        if (empty(trim($_POST["confirm_password"]))) {
            $confirmPassword_err = "Please confirm the password.";     
        } else {
            $confirmPassword = trim($_POST["confirm_password"]);
            if (empty($newPassword_err) && ($newPassword != $confirmPassword)) {
                $confirmPassword_err = "Passwords did not match.";
            }
        }
        
        // Check if there are no errors before updating the database
        if (empty($currentPassword_err) && empty($newPassword_err) && empty($confirmPassword_err)) {
            // Prepare an update statement
            $sql = "UPDATE users SET password = ? WHERE user_id = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("si", $param_password, $param_userId);
                
                // Set parameters
                $param_password = password_hash($newPassword, PASSWORD_DEFAULT);
                $param_userId = $userId;
                
                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    $success_message = "Password changed successfully.";
                    
                    // Clear the password fields
                    $currentPassword = $newPassword = $confirmPassword = "";
                } else {
                    $error_message = "Something went wrong. Please try again later.";
                }
                
                // Close statement
                $stmt->close();
            }
        }
    } elseif (isset($_POST['upload_photo'])) {
        // Handle profile picture upload
        if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
            $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
            $filename = $_FILES["profile_picture"]["name"];
            $filetype = $_FILES["profile_picture"]["type"];
            $filesize = $_FILES["profile_picture"]["size"];
            
            // Verify file extension
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (!array_key_exists($ext, $allowed)) {
                $error_message = "Error: Please select a valid file format.";
            }
            
            // Verify file size - 5MB maximum
            $maxsize = 5 * 1024 * 1024;
            if ($filesize > $maxsize) {
                $error_message = "Error: File size is larger than the allowed limit (5MB).";
            }
            
            // Verify MIME type of the file
            if (in_array($filetype, $allowed)) {
                // Check if file exists before uploading
                $newFilename = uniqid() . "." . $ext;
                $targetDir = "../uploads/profiles/";
                
                // Create directory if it doesn't exist
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                
                $targetFile = $targetDir . $newFilename;
                
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
                    // Update profile picture in database
                    $sql = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("si", $targetFile, $userId);
                        if ($stmt->execute()) {
                            $success_message = "Profile picture updated successfully.";
                            $profilePicture = $targetFile;
                        } else {
                            $error_message = "Error updating profile picture in database.";
                        }
                        $stmt->close();
                    }
                } else {
                    $error_message = "Error uploading file.";
                }
            } else {
                $error_message = "Error: There was a problem uploading your file. Please try again.";
            }
        } else {
            $error_message = "Error: " . $_FILES["profile_picture"]["error"];
        }
    }
}

// Page title
$pageTitle = "My Profile";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
        
        .profile-main {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .profile-tabs {
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
            display: flex;
        }
        
        .profile-tabs button {
            background: none;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            opacity: 0.6;
            position: relative;
        }
        
        .profile-tabs button.active {
            opacity: 1;
            font-weight: 500;
        }
        
        .profile-tabs button.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #3498db;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-content h3 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include sidebar -->
        <?php include_once '../includes/sidebar-student.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2><?php echo $pageTitle; ?></h2>
            </div>
            
            <!-- Notifications -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-container">
                <div class="profile-sidebar">
                    <img src="<?php echo !empty($profilePicture) ? $profilePicture : '../assets/images/default-profile.png'; ?>" alt="Profile Picture" class="profile-picture" id="profile-image-preview">
                    
                    <div class="profile-details">
                        <h3><?php echo $firstName . ' ' . $lastName; ?></h3>
                        <p><?php echo $email; ?></p>
                        <p>Student</p>
                    </div>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="profile_picture" class="btn" style="display: block; margin-bottom: 10px;">Change Profile Picture</label>
                            <input type="file" name="profile_picture" id="profile_picture" class="file-upload" data-preview="profile-image-preview" style="display: none;">
                        </div>
                        <button type="submit" name="upload_photo" class="btn">Upload Photo</button>
                    </form>
                </div>
                
                <div class="profile-main">
                    <div class="profile-tabs">
                        <button class="tab-btn active" data-tab="personal-info">Personal Information</button>
                        <button class="tab-btn" data-tab="change-password">Change Password</button>
                    </div>
                    
                    <div id="personal-info" class="tab-content active">
                        <h3>Personal Information</h3>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="validate-form">
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
                                <label>Email</label>
                                <input type="email" class="form-control" value="<?php echo $email; ?>" disabled>
                                <small class="text-muted">Email cannot be changed. Contact administrator for assistance.</small>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="update_profile" class="btn">Update Profile</button>
                            </div>
                        </form>
                    </div>
                    
                    <div id="change-password" class="tab-content">
                        <h3>Change Password</h3>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="validate-form">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" class="form-control <?php echo (!empty($currentPassword_err)) ? 'is-invalid' : ''; ?>">
                                <span class="text-danger"><?php echo $currentPassword_err; ?></span>
                            </div>
                            
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control <?php echo (!empty($newPassword_err)) ? 'is-invalid' : ''; ?>">
                                <span class="text-danger"><?php echo $newPassword_err; ?></span>
                            </div>
                            
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirmPassword_err)) ? 'is-invalid' : ''; ?>">
                                <span class="text-danger"><?php echo $confirmPassword_err; ?></span>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="change_password" class="btn">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const tabId = button.getAttribute('data-tab');
                    
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to current button and content
                    button.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // Handle profile picture preview
            const profilePicture = document.getElementById('profile_picture');
            const previewImage = document.getElementById('profile-image-preview');
            
            if (profilePicture && previewImage) {
                profilePicture.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.addEventListener('load', function() {
                            previewImage.src = this.result;
                        });
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>