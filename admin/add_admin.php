<?php
// Database connection
require_once '../config.php';

// Create admin user
$email = 'r@lms.com';
$password = password_hash('password', PASSWORD_DEFAULT);
$role = 'admin';
$firstName = 'Admin';
$lastName = 'User';

// Check if user already exists
$checkStmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    echo "User with email " . $email . " already exists.";
} else {
    // Insert new admin user
    $stmt = $conn->prepare("INSERT INTO users (email, password, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $password, $role, $firstName, $lastName);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully with email: " . $email;
    } else {
        echo "Error creating admin user: " . $stmt->error;
    }
    $stmt->close();
}

$checkStmt->close();
$conn->close();
?>