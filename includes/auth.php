<?php
require_once 'db_connection.php';

class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Student registration
    public function registerStudent($student_number, $full_name, $email, $password) {
        try {
            // Check if student already exists
            $query = "SELECT id FROM students WHERE student_number = :student_number OR email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_number', $student_number);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                return false; // Student already exists
            }
            
            // Insert new student
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO students (student_number, full_name, email, password) 
                      VALUES (:student_number, :full_name, :email, :password)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_number', $student_number);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            
            if($stmt->execute()) {
                return true;
            }
            
            return false;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Student login
    public function loginStudent($student_number, $password) {
        try {
            $query = "SELECT id, student_number, full_name, email, password 
                      FROM students WHERE student_number = :student_number";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_number', $student_number);
            $stmt->execute();
            
            if($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if(password_verify($password, $row['password'])) {
                    $_SESSION['student_id'] = $row['id'];
                    $_SESSION['student_number'] = $row['student_number'];
                    $_SESSION['full_name'] = $row['full_name'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_type'] = 'student';
                    return true;
                }
            }
            
            return false;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Admin login
    public function loginAdmin($username, $password) {
        try {
            $query = "SELECT id, username, password FROM admins WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if(password_verify($password, $row['password'])) {
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_type'] = 'admin';
                    return true;
                }
            }
            
            return false;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Check if user is admin
    public function isAdmin() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
    }
    
    // Check if user is student
    public function isStudent() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
    }
    
    // Logout
    public function logout() {
        $_SESSION = array();
        session_destroy();
    }
}
?>