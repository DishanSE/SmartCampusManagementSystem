<?php
// Include database connection
require_once 'config.php';

// Check if database connection is working
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connection successful.<br>";

// Check if users table exists
$tableCheckResult = $conn->query("SHOW TABLES LIKE 'users'");
if ($tableCheckResult->num_rows == 0) {
    echo "The 'users' table does not exist. Creating it now.<br>";
    
    // Create users table
    $createTableSQL = "
    CREATE TABLE users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'lecturer', 'student') NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        profile_picture VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createTableSQL) === TRUE) {
        echo "Users table created successfully.<br>";
    } else {
        echo "Error creating users table: " . $conn->error . "<br>";
    }
}

// Create admin user
$email = 'admin@lms.com';
$password = password_hash('password', PASSWORD_DEFAULT);
$role = 'admin';
$firstName = 'System';
$lastName = 'Administrator';

// Check if admin exists
$checkAdminSQL = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($checkAdminSQL);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Admin user already exists. Updating password.<br>";
    
    // Update password
    $updateSQL = "UPDATE users SET password = ? WHERE email = ?";
    $updateStmt = $conn->prepare($updateSQL);
    $updateStmt->bind_param("ss", $password, $email);
    
    if ($updateStmt->execute()) {
        echo "Admin password updated successfully.<br>";
    } else {
        echo "Error updating admin password: " . $updateStmt->error . "<br>";
    }
    
    $updateStmt->close();
} else {
    echo "Admin user does not exist. Creating new admin user.<br>";
    
    // Insert admin user
    $insertSQL = "INSERT INTO users (email, password, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSQL);
    $insertStmt->bind_param("sssss", $email, $password, $role, $firstName, $lastName);
    
    if ($insertStmt->execute()) {
        echo "Admin user created successfully.<br>";
    } else {
        echo "Error creating admin user: " . $insertStmt->error . "<br>";
    }
    
    $insertStmt->close();
}

echo "<hr>";
echo "You can now log in with:<br>";
echo "Email: admin@lms.com<br>";
echo "Password: password<br>";
echo "<a href='login.php'>Go to Login Page</a>";

$stmt->close();
$conn->close();
?>