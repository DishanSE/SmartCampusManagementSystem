<?php
// Database connection
require_once 'config.php';

// Start session
session_start();

// Check if user is already logged in, redirect based on role
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("location: admin/dashboard.php");
            exit;
        case 'lecturer':
            header("location: lecturer/dashboard.php");
            exit;
        case 'student':
            header("location: student/dashboard.php");
            exit;
        case 'hod':
            header("location: hod/page.php");
            exit;
    }
}

// Initialize variables
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Process login data when form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set response header if it's an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
    }

    // Check if email and password are provided
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode([
                'success' => false,
                'message' => 'Email and password are required'
            ]);
            exit;
        } else {
            $login_err = "Email and password are required";
        }
    } else {
        // Get email and password from POST request
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email format'
                ]);
                exit;
            } else {
                $email_err = "Invalid email format";
            }
        } else {
            try {
                // Prepare SQL statement to prevent SQL injection
                $stmt = $conn->prepare("SELECT user_id, email, password, role, first_name, last_name FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                // Check if user exists
                if ($result->num_rows === 0) {
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Invalid email or password'
                        ]);
                        exit;
                    } else {
                        $login_err = "Invalid email or password";
                    }
                } else {
                    // Fetch user data
                    $user = $result->fetch_assoc();
                    
                    // Verify password
                    if (!password_verify($password, $user['password'])) {
                        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Invalid email or password'
                            ]);
                            exit;
                        } else {
                            $login_err = "Invalid email or password";
                        }
                    } else {
                        // Set session variables
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['last_name'] = $user['last_name'];
                        $_SESSION['logged_in'] = true;
                        
                        // Determine redirect URL based on user role
                        $redirect = '';
                        switch ($user['role']) {
                            case 'admin':
                                $redirect = 'admin/dashboard.php';
                                break;
                            case 'lecturer':
                                $redirect = 'lecturer/dashboard.php';
                                break;
                            case 'student':
                                $redirect = 'student/dashboard.php';
                                break;
                            case 'hod':
                                $redirect = 'hod/page.php';
                            default:
                                $redirect = 'index.php';
                        }
                        
                        // Return success response
                        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                            echo json_encode([
                                'success' => true,
                                'message' => 'Login successful',
                                'redirect' => $redirect,
                                'user' => [
                                    'id' => $user['user_id'],
                                    'email' => $user['email'],
                                    'role' => $user['role'],
                                    'name' => $user['first_name'] . ' ' . $user['last_name']
                                ]
                            ]);
                            exit;
                        } else {
                            // Redirect directly for non-AJAX requests
                            header("location: " . $redirect);
                            exit;
                        }
                    }
                }
                
                // Close the statement
                $stmt->close();
                
            } catch (Exception $e) {
                // Return error response
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Something went wrong. Please try again later.'
                    ]);
                    exit;
                } else {
                    $login_err = "Something went wrong. Please try again later.";
                }
            }
        }
    }
    
    // Close the database connection if it's an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $conn->close();
        exit;
    }
}

// Page title
$pageTitle = "Login";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - <?php echo $pageTitle; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 400px;
            max-width: 100%;
        }
        
        .header {
            background-color: #3498db;
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .header h2 {
            margin-bottom: 10px;
        }
        
        .form-container {
            padding: 30px;
        }
        
        .form-control {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-control label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .btn {
            background-color: #3498db;
            border: none;
            color: white;
            padding: 15px;
            width: 100%;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .success-message {
            color: #2ecc71;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .back-to-home {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-to-home a {
            color: #3498db;
            text-decoration: none;
        }
        
        .back-to-home a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="header">
            <h2>Learning Management System</h2>
            <p>Sign in to access your account</p>
        </div>
        <div class="form-container">
            <?php if(!empty($login_err)): ?>
                <div class="error-message" style="margin-bottom: 15px;"><?php echo $login_err; ?></div>
            <?php endif; ?>
            
            <form id="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-control">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo $email; ?>" required>
                    <?php if(!empty($email_err)): ?>
                        <span class="error-message"><?php echo $email_err; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-control">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <?php if(!empty($password_err)): ?>
                        <span class="error-message"><?php echo $password_err; ?></span>
                    <?php endif; ?>
                </div>
                <div id="message"></div>
                <button type="submit" class="btn">Login</button>
            </form>
            
            <div class="back-to-home">
                <a href="index.php">Back to Home</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', function(e) {
            // This will now submit normally if JavaScript is disabled
            // For AJAX submission, we prevent default
            if (window.XMLHttpRequest) {
                e.preventDefault();
                
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const messageDiv = document.getElementById('message');
                
                // Basic validation
                if (!email || !password) {
                    messageDiv.innerHTML = '<p class="error-message">Please enter both email and password</p>';
                    return;
                }
                
                // AJAX request to login.php
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'login.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                
                xhr.onload = function() {
                    if (this.status === 200) {
                        try {
                            const response = JSON.parse(this.responseText);
                            
                            if (response.success) {
                                messageDiv.innerHTML = '<p class="success-message">Login successful. Redirecting...</p>';
                                
                                // Redirect based on user role
                                setTimeout(function() {
                                    window.location.href = response.redirect;
                                }, 1000);
                            } else {
                                messageDiv.innerHTML = `<p class="error-message">${response.message}</p>`;
                            }
                        } catch (e) {
                            messageDiv.innerHTML = '<p class="error-message">Something went wrong. Please try again.</p>';
                        }
                    } else {
                        messageDiv.innerHTML = '<p class="error-message">Server error. Please try again later.</p>';
                    }
                };
                
                xhr.send(`email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`);
            }
        });
    </script>
</body>
</html>