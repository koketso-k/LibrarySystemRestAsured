<?php
require_once 'db_connection.php';

class LibraryFunctions {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Add a new book
    public function addBook($isbn, $title, $author, $category, $publication_year, $total_copies) {
        try {
            $query = "INSERT INTO books (isbn, title, author, category, publication_year, total_copies, available_copies) 
                      VALUES (:isbn, :title, :author, :category, :publication_year, :total_copies, :total_copies)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':isbn', $isbn);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':author', $author);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':publication_year', $publication_year);
            $stmt->bindParam(':total_copies', $total_copies);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    

// Add to includes/functions.php in the LibraryFunctions class

/**
 * Send due date reminder emails
 */
public function sendDueDateReminders() {
    try {
        // Get books due in 3 days
        $reminder_date = date('Y-m-d', strtotime('+3 days'));
        
        $query = "SELECT br.*, s.email, s.full_name, b.title 
                  FROM borrows br 
                  INNER JOIN students s ON br.student_id = s.id 
                  INNER JOIN books b ON br.book_id = b.id 
                  WHERE br.due_date = :reminder_date 
                  AND br.status = 'borrowed' 
                  AND br.reminder_sent = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':reminder_date', $reminder_date);
        $stmt->execute();
        
        $books_due_soon = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($books_due_soon as $borrow) {
            // In a real application, you would send an email here
            // This is a simulation of email sending
            
            $to = $borrow['email'];
            $subject = "Library Book Due Date Reminder";
            $message = "
            Dear {$borrow['full_name']},
            
            This is a reminder that your borrowed book '{$borrow['title']}' 
            is due on {$borrow['due_date']}.
            
            Please return the book on or before the due date to avoid late fees.
            
            Thank you,
            Library Management System
            ";
            
            // Simulate email sending (in production, use mail() or PHPMailer)
            error_log("Due reminder sent to: {$to} for book: {$borrow['title']}");
            
            // Mark reminder as sent
            $update_query = "UPDATE borrows SET reminder_sent = 1 WHERE id = :borrow_id";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':borrow_id', $borrow['id']);
            $update_stmt->execute();
        }
        
        return count($books_due_soon);
        
    } catch(PDOException $e) {
        error_log("Error sending reminders: " . $e->getMessage());
        return false;
    }
}

/**
 * Send overdue notifications
 */
public function sendOverdueNotifications() {
    try {
        $today = date('Y-m-d');
        
        $query = "SELECT br.*, s.email, s.full_name, b.title,
                  DATEDIFF(:today, br.due_date) as days_overdue
                  FROM borrows br 
                  INNER JOIN students s ON br.student_id = s.id 
                  INNER JOIN books b ON br.book_id = b.id 
                  WHERE br.due_date < :today 
                  AND br.status = 'borrowed' 
                  AND br.overdue_notice_sent = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':today', $today);
        $stmt->execute();
        
        $overdue_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($overdue_books as $borrow) {
            $to = $borrow['email'];
            $subject = "Overdue Library Book Notification";
            $fine = $borrow['days_overdue'] * 0.5;
            
            $message = "
            Dear {$borrow['full_name']},
            
            Your borrowed book '{$borrow['title']}' was due on {$borrow['due_date']} 
            and is now {$borrow['days_overdue']} days overdue.
            
            Current fine: \$" . number_format($fine, 2) . "
            (Fines accumulate at \$0.50 per day)
            
            Please return the book as soon as possible to avoid additional charges.
            
            Thank you,
            Library Management System
            ";
            
            // Simulate email sending
            error_log("Overdue notice sent to: {$to} for book: {$borrow['title']}");
            
            // Mark overdue notice as sent
            $update_query = "UPDATE borrows SET overdue_notice_sent = 1 WHERE id = :borrow_id";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':borrow_id', $borrow['id']);
            $update_stmt->execute();
        }
        
        return count($overdue_books);
        
    } catch(PDOException $e) {
        error_log("Error sending overdue notices: " . $e->getMessage());
        return false;
    }
}


    // Get all books with optional search
    public function getBooks($search = '') {
        try {
            $query = "SELECT * FROM books";
            
            if(!empty($search)) {
                $query .= " WHERE title LIKE :search OR author LIKE :search OR isbn LIKE :search";
            }
            
            $query .= " ORDER BY title";
            
            $stmt = $this->conn->prepare($query);
            
            if(!empty($search)) {
                $search_term = '%' . $search . '%';
                $stmt->bindParam(':search', $search_term);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return array();
        }
    }
    
    // Get book by ID
    public function getBookById($id) {
        try {
            $query = "SELECT * FROM books WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Borrow a book
    public function borrowBook($student_id, $book_id) {
        try {
            // Check if student has already borrowed this book and not returned it
            $query = "SELECT id FROM borrows WHERE student_id = :student_id AND book_id = :book_id AND status != 'returned'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':book_id', $book_id);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                return "already_borrowed";
            }
            
            // Check if book is available
            $query = "SELECT available_copies FROM books WHERE id = :book_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':book_id', $book_id);
            $stmt->execute();
            
            $book = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($book['available_copies'] < 1) {
                return "not_available";
            }
            
            // Calculate due date (14 days from today)
            $borrow_date = date('Y-m-d');
            $due_date = date('Y-m-d', strtotime('+14 days'));
            
            // Start transaction
            $this->conn->beginTransaction();
            
            // Insert borrow record
            $query = "INSERT INTO borrows (student_id, book_id, borrow_date, due_date) 
                      VALUES (:student_id, :book_id, :borrow_date, :due_date)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':book_id', $book_id);
            $stmt->bindParam(':borrow_date', $borrow_date);
            $stmt->bindParam(':due_date', $due_date);
            $stmt->execute();
            
            // Update available copies
            $query = "UPDATE books SET available_copies = available_copies - 1 WHERE id = :book_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':book_id', $book_id);
            $stmt->execute();
            
            // Commit transaction
            $this->conn->commit();
            
            return "success";
        } catch(PDOException $e) {
            $this->conn->rollBack();
            echo "Error: " . $e->getMessage();
            return "error";
        }
    }
    
    // Return a book
    public function returnBook($borrow_id) {
        try {
            // Get borrow record
            $query = "SELECT * FROM borrows WHERE id = :borrow_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':borrow_id', $borrow_id);
            $stmt->execute();
            
            $borrow = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$borrow) {
                return "not_found";
            }
            
            // Start transaction
            $this->conn->beginTransaction();
            
            // Update borrow record
            $return_date = date('Y-m-d');
            $status = 'returned';
            
            $query = "UPDATE borrows SET return_date = :return_date, status = :status WHERE id = :borrow_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':return_date', $return_date);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':borrow_id', $borrow_id);
            $stmt->execute();
            
            // Update available copies
            $query = "UPDATE books SET available_copies = available_copies + 1 WHERE id = :book_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':book_id', $borrow['book_id']);
            $stmt->execute();
            
            // Commit transaction
            $this->conn->commit();
            
            return "success";
        } catch(PDOException $e) {
            $this->conn->rollBack();
            echo "Error: " . $e->getMessage();
            return "error";
        }
    }
    
    // Get borrowed books by student
    public function getBorrowedBooks($student_id) {
        try {
            $query = "SELECT b.*, br.borrow_date, br.due_date, br.return_date, br.status, br.id as borrow_id 
                      FROM books b 
                      INNER JOIN borrows br ON b.id = br.book_id 
                      WHERE br.student_id = :student_id 
                      ORDER BY br.borrow_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return array();
        }
    }
    
    // Get all borrowed books (for admin)
    public function getAllBorrowedBooks() {
        try {
            $query = "SELECT br.*, s.full_name, s.student_number, b.title, b.isbn 
                      FROM borrows br 
                      INNER JOIN students s ON br.student_id = s.id 
                      INNER JOIN books b ON br.book_id = b.id 
                      ORDER BY br.borrow_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return array();
        }
    }
    
    // Get overdue books
    public function getOverdueBooks() {
        try {
            $current_date = date('Y-m-d');
            $query = "SELECT br.*, s.full_name, s.student_number, b.title, b.isbn 
                      FROM borrows br 
                      INNER JOIN students s ON br.student_id = s.id 
                      INNER JOIN books b ON br.book_id = b.id 
                      WHERE br.due_date < :current_date AND br.status = 'borrowed' 
                      ORDER BY br.due_date ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':current_date', $current_date);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return array();
        }
    }
    
    // Update book details
    public function updateBook($id, $isbn, $title, $author, $category, $publication_year, $total_copies) {
        try {
            // Get current book details
            $current_book = $this->getBookById($id);
            
            if(!$current_book) {
                return "not_found";
            }
            
            // Calculate new available copies
            $borrowed_copies = $current_book['total_copies'] - $current_book['available_copies'];
            $new_available_copies = $total_copies - $borrowed_copies;
            
            if($new_available_copies < 0) {
                return "invalid_copies";
            }
            
            $query = "UPDATE books 
                      SET isbn = :isbn, title = :title, author = :author, category = :category, 
                          publication_year = :publication_year, total_copies = :total_copies, 
                          available_copies = :available_copies 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':isbn', $isbn);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':author', $author);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':publication_year', $publication_year);
            $stmt->bindParam(':total_copies', $total_copies);
            $stmt->bindParam(':available_copies', $new_available_copies);
            $stmt->bindParam(':id', $id);
            
            if($stmt->execute()) {
                return "success";
            }
            
            return "error";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return "error";
        }
    }
    
    // Delete a book
    public function deleteBook($id) {
        try {
            // Check if book is currently borrowed
            $query = "SELECT id FROM borrows WHERE book_id = :book_id AND status != 'returned'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':book_id', $id);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                return "currently_borrowed";
            }
            
            $query = "DELETE FROM books WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if($stmt->execute()) {
                return "success";
            }
            
            return "error";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return "error";
        }
    }
}
?>