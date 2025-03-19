<?php
// Turn on error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Echo a test message to see if basic PHP is working
echo "PHP is working!<br>";

// Try to include the database configuration
echo "Attempting to include config.php...<br>";
include_once 'config.php';

// Check if database connection worked
if (isset($conn)) {
    echo "Database connection variable exists.<br>";
    
    if ($conn->connect_error) {
        echo "Database connection error: " . $conn->connect_error . "<br>";
    } else {
        echo "Database connection successful!<br>";
        
        // Create a simple admin user directly
        $email = 'admin@lms.com';
        $hashedPassword = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // 'password'
        $role = 'admin';
        $firstName = 'System';
        $lastName = 'Admin';
        
        $sql = "INSERT INTO users (email, password, role, first_name, last_name) 
                VALUES ('$email', '$hashedPassword', '$role', '$firstName', '$lastName')
                ON DUPLICATE KEY UPDATE password='$hashedPassword'";
                
        if ($conn->query($sql) === TRUE) {
            echo "Admin user created or updated successfully.<br>";
            echo "You can now log in with:<br>";
            echo "Email: admin@lms.com<br>";
            echo "Password: password<br>";
        } else {
            echo "Error with admin user: " . $conn->error . "<br>";
        }
    }
} else {
    echo "Failed to connect to database. Check your config.php file.<br>";
}
?>