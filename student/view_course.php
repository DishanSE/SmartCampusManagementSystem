<?php
// Include config file
require_once '../config.php';

// Start session
session_start();

// Check if user is logged in and has student role
requireRole('student');

// Check if course ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: my_courses.php");
    exit;
}

$courseId = $_GET['id'];
$studentId = $_SESSION['user_id'];

// Check if student is enrolled in this course
$enrollmentQuery = "SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?";
$enrollmentStmt = $conn->prepare($enrollmentQuery);
$enrollmentStmt->bind_param("ii", $studentId, $courseId);
$enrollmentStmt->execute();
$enrollmentResult = $enrollmentStmt->get_result();

if ($enrollmentResult->num_rows != 1) {
    // Student is not enrolled in this course
    header("location: unauthorized.php");
    exit;
}

// Get course details
$courseQuery = "
    SELECT c.*, u.first_name, u.last_name 
    FROM courses c
    LEFT JOIN users u ON c.lecturer_id = u.user_id
    WHERE c.course_id = ?
";
$courseStmt = $conn->prepare($courseQuery);
$courseStmt->bind_param("i", $courseId);
$courseStmt->execute();
$courseResult = $courseStmt->get_result();

if ($courseResult->num_rows != 1) {
    // Course not found
    header("location: my_courses.php");
    exit;
}

$course = $courseResult->fetch_assoc();

// Get course materials
$materialsQuery = "
    SELECT * FROM learning_materials 
    WHERE course_id = ? 
    ORDER BY created_at DESC
";
$materialsStmt = $conn->prepare($materialsQuery);
$materialsStmt->bind_param("i", $courseId);
$materialsStmt->execute();
$materialsResult = $materialsStmt->get_result();

// Get course assignments
$assignmentsQuery = "
    SELECT a.*, lm.title, lm.material_id
    FROM assignments a
    JOIN learning_materials lm ON a.material_id = lm.material_id
    WHERE lm.course_id = ?
    ORDER BY a.deadline ASC
";
$assignmentsStmt = $conn->prepare($assignmentsQuery);
$assignmentsStmt->bind_param("i", $courseId);
$assignmentsStmt->execute();
$assignmentsResult = $assignmentsStmt->get_result();

// Page title
$pageTitle = $course['title'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .course-header {
            background-color: #3498db;
            color: white;
            padding: 40px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .course-header h2 {
            margin-bottom: 10px;
        }
        
        .course-header p {
            margin-bottom: 5px;
            opacity: 0.9;
        }
        
        .course-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .materials-section, .sidebar-section {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .materials-section h3, .sidebar-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .material-item {
            padding: 15px;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .material-item:last-child {
            border-bottom: none;
        }
        
        .material-item h4 {
            margin-bottom: 5px;
        }
        
        .material-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: white;
            margin-bottom: 10px;
        }
        
        .material-type.lesson {
            background-color: #3498db;
        }
        
        .material-type.assignment {
            background-color: #e74c3c;
        }
        
        .material-type.resource {
            background-color: #2ecc71;
        }
        
        .material-actions {
            margin-top: 10px;
        }
        
        .assignment-item {
            padding: 15px;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .assignment-item:last-child {
            border-bottom: none;
        }
        
        .assignment-item h4 {
            margin-bottom: 5px;
        }
        
        .deadline {
            color: #e74c3c;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .upcoming-deadline {
            color: #e67e22;
        }
        
        .past-deadline {
            color: #7f8c8d;
        }
        
        .lecturer-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .lecturer-info img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }
        
        .lecturer-details h4 {
            margin-bottom: 5px;
        }
        
        .lecturer-details p {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .no-items {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
        }
        
        @media (max-width: 768px) {
            .course-content {
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
                <a href="my_courses.php" class="btn" style="background-color: #7f8c8d;">Back to My Courses</a>
            </div>
            
            <div class="course-header">
                <h2><?php echo $course['title']; ?></h2>
                <p><strong>Course Code:</strong> <?php echo $course['course_code']; ?></p>
                <p><strong>Lecturer:</strong> <?php echo $course['first_name'] . ' ' . $course['last_name']; ?></p>
            </div>
            
            <div class="course-content">
                <div class="materials-section">
                    <h3>Learning Materials</h3>
                    
                    <?php if ($materialsResult->num_rows > 0): ?>
                        <?php while ($material = $materialsResult->fetch_assoc()): ?>
                            <div class="material-item">
                                <span class="material-type <?php echo $material['material_type']; ?>">
                                    <?php echo ucfirst($material['material_type']); ?>
                                </span>
                                
                                <h4><?php echo $material['title']; ?></h4>
                                
                                <?php if (!empty($material['content'])): ?>
                                    <p><?php echo substr(strip_tags($material['content']), 0, 100) . (strlen(strip_tags($material['content'])) > 100 ? '...' : ''); ?></p>
                                <?php endif; ?>
                                
                                <div class="material-actions">
                                    <a href="view_material.php?id=<?php echo $material['material_id']; ?>" class="btn btn-sm">View Material</a>
                                    
                                    <?php if ($material['material_type'] == 'assignment'): ?>
                                        <?php
                                        // Get assignment details
                                        $assignmentQuery = "SELECT * FROM assignments WHERE material_id = ?";
                                        $assignmentStmt = $conn->prepare($assignmentQuery);
                                        $assignmentStmt->bind_param("i", $material['material_id']);
                                        $assignmentStmt->execute();
                                        $assignmentResult = $assignmentStmt->get_result();
                                        $assignment = $assignmentResult->fetch_assoc();
                                        
                                        // Check if student has submitted
                                        $submissionQuery = "SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?";
                                        $submissionStmt = $conn->prepare($submissionQuery);
                                        $submissionStmt->bind_param("ii", $assignment['assignment_id'], $studentId);
                                        $submissionStmt->execute();
                                        $submissionResult = $submissionStmt->get_result();
                                        $hasSubmitted = $submissionResult->num_rows > 0;
                                        ?>
                                        
                                        <?php if ($hasSubmitted): ?>
                                            <a href="view_submission.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-sm" style="background-color: #2ecc71;">View Submission</a>
                                        <?php else: ?>
                                            <a href="submit_assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-sm" style="background-color: #e74c3c;">Submit Assignment</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-items">
                            <p>No learning materials available for this course yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar-section">
                    <h3>Upcoming Assignments</h3>
                    
                    <?php if ($assignmentsResult->num_rows > 0): ?>
                        <?php 
                        $now = time();
                        $upcomingAssignments = false;
                        ?>
                        
                        <?php while ($assignment = $assignmentsResult->fetch_assoc()): ?>
                            <?php
                            $deadline = strtotime($assignment['deadline']);
                            if ($deadline > $now) {
                                $upcomingAssignments = true;
                                $timeLeft = $deadline - $now;
                                $daysLeft = floor($timeLeft / (60 * 60 * 24));
                                
                                // Check if student has submitted
                                $submissionQuery = "SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?";
                                $submissionStmt = $conn->prepare($submissionQuery);
                                $submissionStmt->bind_param("ii", $assignment['assignment_id'], $studentId);
                                $submissionStmt->execute();
                                $submissionResult = $submissionStmt->get_result();
                                $hasSubmitted = $submissionResult->num_rows > 0;
                            ?>
                                <div class="assignment-item">
                                    <h4><?php echo $assignment['title']; ?></h4>
                                    
                                    <div class="deadline <?php echo ($daysLeft <= 2) ? 'upcoming-deadline' : ''; ?>">
                                        Due: <?php echo date('F j, Y, g:i a', $deadline); ?>
                                        <br>
                                        <?php if ($daysLeft == 0): ?>
                                            <strong>Due today!</strong>
                                        <?php elseif ($daysLeft == 1): ?>
                                            <strong>Due tomorrow!</strong>
                                        <?php else: ?>
                                            <strong><?php echo $daysLeft; ?> days left</strong>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($hasSubmitted): ?>
                                        <span style="color: #2ecc71; font-weight: bold;">✓ Submitted</span>
                                    <?php else: ?>
                                        <a href="submit_assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-sm">Submit Now</a>
                                    <?php endif; ?>
                                </div>
                            <?php } ?>
                        <?php endwhile; ?>
                        
                        <?php if (!$upcomingAssignments): ?>
                            <div class="no-items">
                                <p>No upcoming assignments.</p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-items">
                            <p>No assignments available for this course yet.</p>
                        </div>
                    <?php endif; ?>
                    
                    <h3 style="margin-top: 30px;">Course Lecturer</h3>
                    
                    <div class="lecturer-info">
                        <img src="../assets/images/default-profile.png" alt="Lecturer">
                        <div class="lecturer-details">
                            <h4><?php echo $course['first_name'] . ' ' . $course['last_name']; ?></h4>
                            <p>Lecturer</p>
                        </div>
                    </div>
                    
                    <p><?php echo $course['description']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>