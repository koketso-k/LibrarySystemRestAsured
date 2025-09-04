<?php
/**
 * Test file for Return Functions
 * Run this file to test all return-related functionality
 */

require_once '../includes/config.php';
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

echo "<h1>Testing Return Functions</h1>";

// Create library instance
$library = new LibraryFunctions();

// Create test data
echo "<h2>Setting Up Test Data</h2>";

// Add a test book
$library->addBook('978-0136019701', 'Test Book for Returning', 'Test Author', 'Testing', 2023, 2);
$books = $library->getBooks('Test Book for Returning');
$book_id = $books[0]['id'];
echo "<p>Test book created with ID: $book_id</p>";

// Borrow the book first
$student_id = 1;
$borrow_result = $library->borrowBook($student_id, $book_id);

if ($borrow_result == 'success') {
    echo "<p>Book borrowed successfully for testing returns</p>";
    
    // Get the borrow record
    $borrowed_books = $library->getBorrowedBooks($student_id);
    $borrow_id = $borrowed_books[0]['borrow_id'];
    
    // Test 1: Return a book
    echo "<h2>Test 1: Returning a Book</h2>";
    $return_result = $library->returnBook($borrow_id);
    if ($return_result == 'success') {
        echo "<p style='color: green;'>✓ Book returned successfully</p>";
        
        // Test 2: Check book availability after return
        echo "<h2>Test 2: Checking Book Availability After Return</h2>";
        $book_after_return = $library->getBookById($book_id);
        echo "<p>Available copies after return: " . $book_after_return['available_copies'] . "/" . $book_after_return['total_copies'] . "</p>";
        if ($book_after_return['available_copies'] == 2) {
            echo "<p style='color: green;'>✓ Available copies correctly updated after return</p>";
        } else {
            echo "<p style='color: red;'>✗ Available copies not updated correctly after return</p>";
        }
        
        // Test 3: Check borrow status after return
        echo "<h2>Test 3: Checking Borrow Status After Return</h2>";
        $returned_books = $library->getBorrowedBooks($student_id);
        $returned = false;
        foreach ($returned_books as $book) {
            if ($book['borrow_id'] == $borrow_id && $book['status'] == 'returned') {
                $returned = true;
                break;
            }
        }
        if ($returned) {
            echo "<p style='color: green;'>✓ Borrow status correctly updated to 'returned'</p>";
        } else {
            echo "<p style='color: red;'>✗ Borrow status not updated correctly</p>";
        }
        
        // Test 4: Try to return already returned book
        echo "<h2>Test 4: Attempting to Return Already Returned Book</h2>";
        $return_result2 = $library->returnBook($borrow_id);
        if ($return_result2 == 'success') {
            echo "<p style='color: green;'>✓ Book return processed (idempotent operation)</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to process return: $return_result2</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Failed to return book: $return_result</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Failed to borrow book for testing: $borrow_result</p>";
}

// Test 5: Test overdue books functionality
echo "<h2>Test 5: Testing Overdue Books Detection</h2>";

// Create a borrow record with past due date (simulate overdue)
$library->addBook('978-0136019702', 'Overdue Test Book', 'Test Author', 'Testing', 2023, 1);
$overdue_book = $library->getBooks('Overdue Test Book');
$overdue_book_id = $overdue_book[0]['id'];

// Manually create an overdue record (bypassing normal borrowing process)
try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $past_date = date('Y-m-d', strtotime('-15 days'));
    $due_date = date('Y-m-d', strtotime('-1 day'));
    
    $query = "INSERT INTO borrows (student_id, book_id, borrow_date, due_date, status) 
              VALUES (:student_id, :book_id, :borrow_date, :due_date, 'borrowed')";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':book_id', $overdue_book_id);
    $stmt->bindParam(':borrow_date', $past_date);
    $stmt->bindParam(':due_date', $due_date);
    $stmt->execute();
    
    echo "<p>Created overdue borrow record for testing</p>";
    
    // Test overdue detection
    $overdue_books = $library->getOverdueBooks();
    if (count($overdue_books) > 0) {
        echo "<p style='color: green;'>✓ " . count($overdue_books) . " overdue books detected</p>";
        echo "<pre>" . print_r($overdue_books, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>✗ No overdue books detected</p>";
    }
    
    // Cleanup overdue test
    $query = "DELETE FROM borrows WHERE book_id = :book_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':book_id', $overdue_book_id);
    $stmt->execute();
    
    $library->deleteBook($overdue_book_id);
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error creating overdue test: " . $e->getMessage() . "</p>";
}

// Cleanup
echo "<h2>Cleanup: Removing Test Data</h2>";
$library->deleteBook($book_id);
echo "<p>Test books deleted</p>";

echo "<h2>All Return Tests Completed</h2>";
?>