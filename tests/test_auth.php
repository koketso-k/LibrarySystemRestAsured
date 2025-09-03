<?php
/**
 * Test file for Authentication Functions
 * Run this file to test all authentication-related functionality
 */

require_once '../includes/config.php';
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

echo "<h1>Testing Authentication Functions</h1>";

// Create auth instance
$auth = new Auth();

// Test 1: Student Registration
echo "<h2>Test 1: Student Registration</h2>";
$test_student_number = 'TEST' . time();
$test_email = 'test' . time() . '@example.com';

$result = $auth->registerStudent($test_student_number, 'Test User', $test_email, 'password123');
if ($result) {
    echo "<p style='color: green;'>✓ Student registration successful</p>";
    echo "<p>Test student created with number: $test_student_number</p>";
    
    // Test 2: Student Login
    echo "<h2>Test 2: Student Login</h2>";
    session_start(); // Start session for login test
    $login_result = $auth->loginStudent($test_student_number, 'password123');
    if ($login_result) {
        echo "<p style='color: green;'>✓ Student login successful</p>";
        echo "<p>Session data: " . print_r($_SESSION, true) . "</p>";
        
        // Test 3: Check login status
        echo "<h2>Test 3: Checking Login Status</h2>";
        if ($auth->isLoggedIn()) {
            echo "<p style='color: green;'>✓ User is logged in</p>";
        } else {
            echo "<p style='color: red;'>✗ User is not logged in</p>";
        }
        
        // Test 4: Check user type
        echo "<h2>Test 4: Checking User Type</h2>";
        if ($auth->isStudent()) {
            echo "<p style='color: green;'>✓ User is a student</p>";
        } else {
            echo "<p style='color: red;'>✗ User is not a student</p>";
        }
        
        // Test 5: Logout
        echo "<h2>Test 5: Logout</h2>";
        $auth->logout();
        if (!$auth->isLoggedIn()) {
            echo "<p style='color: green;'>✓ Logout successful</p>";
        } else {
            echo "<p style='color: red;'>✗ Logout failed</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Student login failed</p>";
    }
    
    // Test 6: Admin Login
    echo "<h2>Test 6: Admin Login</h2>";
    $admin_login = $auth->loginAdmin('admin', 'password');
    if ($admin_login) {
        echo "<p style='color: green;'>✓ Admin login successful</p>";
        
        // Test 7: Check admin status
        echo "<h2>Test 7: Checking Admin Status</h2>";
        if ($auth->isAdmin()) {
            echo "<p style='color: green;'>✓ User is an admin</p>";
        } else {
            echo "<p style='color: red;'>✗ User is not an admin</p>";
        }
        
        $auth->logout();
    } else {
        echo "<p style='color: red;'>✗ Admin login failed</p>";
    }
    
    // Test 8: Cleanup - Remove test student
    echo "<h2>Test 8: Cleaning Up Test Student</h2>";
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "DELETE FROM students WHERE student_number = :student_number";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':student_number', $test_student_number);
        $stmt->execute();
        
        echo "<p style='color: green;'>✓ Test student removed successfully</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error removing test student: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>✗ Student registration failed</p>";
}

// Test 9: Duplicate Registration Prevention
echo "<h2>Test 9: Duplicate Registration Prevention</h2>";
$result2 = $auth->registerStudent('ADMIN001', 'Duplicate Test', 'duplicate@example.com', 'password123');
if (!$result2) {
    echo "<p style='color: green;'>✓ Duplicate registration prevented</p>";
} else {
    echo "<p style='color: red;'>✗ Duplicate registration allowed</p>";
}

// Test 10: Invalid Login
echo "<h2>Test 10: Invalid Login Attempt</h2>";
$invalid_login = $auth->loginStudent('nonexistent', 'wrongpassword');
if (!$invalid_login) {
    echo "<p style='color: green;'>✓ Invalid login correctly rejected</p>";
} else {
    echo "<p style='color: red;'>✗ Invalid login incorrectly accepted</p>";
}

echo "<h2>All Authentication Tests Completed</h2>";
?>