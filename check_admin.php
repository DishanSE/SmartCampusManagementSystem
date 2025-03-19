<?php
// Include database connection
require_once 'config.php';

// Check if admin exists
$email = 'admin@lms.com';
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "Admin user exists with ID: " . $user['user_id'] . "<br>";
    echo "Current role: " . $user['role'] . "<br>";
    
    // Update admin password
    $newPassword = password_hash('password', PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $updateStmt->bind_param("ss", $newPassword, $email);
    
    if ($updateStmt->execute()) {
        echo "Admin password updated successfully.<br>";
        echo "You can now log in with:<br>";
        echo "Email: admin@lms.com<br>";
        echo "Password: password";
    } else {
        echo "Error updating password: " . $conn->error;
    }
    
    $updateStmt->close();
} else {
    // Create new admin
    $password = password_hash('password', PASSWORD_DEFAULT);
    $role = 'admin';
    $firstName = 'System';
    $lastName = 'Administrator';
    
    $insertStmt = $conn->prepare("INSERT INTO users (email, password, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
    $insertStmt->bind_param("sssss", $email, $password, $role, $firstName, $lastName);
    
    if ($insertStmt->execute()) {
        echo "New admin created successfully.<br>";
        echo "You can now log in with:<br>";
        echo "Email: admin@lms.com<br>";
        echo "Password: password";
    } else {
        echo "Error creating admin: " . $conn->error;
    }
    
    $insertStmt->close();
}

$stmt->close();
$conn->close();
?>