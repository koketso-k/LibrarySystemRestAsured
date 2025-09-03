<?php
/**
 * Test file for Borrowing Functions
 * Run this file to test all borrowing-related functionality
 */

require_once '../includes/config.php';
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

echo "<h1>Testing Borrowing Functions</h1>";

// Create library instance
$library = new LibraryFunctions();

// Create test data
echo "<h2>Setting Up Test Data</h2>";

// Add a test book
$library->addBook('978-0136019701', 'Test Book for Borrowing', 'Test Author', 'Testing', 2023, 2);
$books = $library->getBooks('Test Book for Borrowing');
$book_id = $books[0]['id'];
echo "<p>Test book created with ID: $book_id</p>";

// Create a test student (we'll simulate this by using a known student or creating one)
// For testing purposes, we'll assume student with ID 1 exists
$student_id = 1;
echo "<p>Using student ID: $student_id for testing</p>";

// Test 1: Borrow a book
echo "<h2>Test 1: Borrowing a Book</h2>";
$result = $library->borrowBook($student_id, $book_id);
if ($result == 'success') {
    echo "<p style='color: green;'>✓ Book borrowed successfully</p>";
    
    // Test 2: Try to borrow the same book again (should fail)
    echo "<h2>Test 2: Attempting to Borrow Same Book Again</h2>";
    $result2 = $library->borrowBook($student_id, $book_id);
    if ($result2 == 'already_borrowed') {
        echo "<p style='color: green;'>✓ Correctly prevented double-borrowing</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to prevent double-borrowing: $result2</p>";
    }
    
    // Test 3: Get borrowed books for student
    echo "<h2>Test 3: Retrieving Student's Borrowed Books</h2>";
    $borrowed_books = $library->getBorrowedBooks($student_id);
    if (count($borrowed_books) > 0) {
        echo "<p style='color: green;'>✓ " . count($borrowed_books) . " borrowed books retrieved</p>";
        echo "<pre>" . print_r($borrowed_books, true) . "</pre>";
        
        // Test 4: Get all borrowed books (admin view)
        echo "<h2>Test 4: Retrieving All Borrowed Books (Admin View)</h2>";
        $all_borrowed = $library->getAllBorrowedBooks();
        if (count($all_borrowed) > 0) {
            echo "<p style='color: green;'>✓ " . count($all_borrowed) . " total borrowed books retrieved</p>";
        } else {
            echo "<p style='color: red;'>✗ No borrowed books retrieved</p>";
        }
        
        // Test 5: Check book availability after borrowing
        echo "<h2>Test 5: Checking Book Availability After Borrowing</h2>";
        $book_after_borrow = $library->getBookById($book_id);
        echo "<p>Available copies after borrowing: " . $book_after_borrow['available_copies'] . "/" . $book_after_borrow['total_copies'] . "</p>";
        if ($book_after_borrow['available_copies'] == 1) {
            echo "<p style='color: green;'>✓ Available copies correctly updated</p>";
        } else {
            echo "<p style='color: red;'>✗ Available copies not updated correctly</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ No borrowed books retrieved</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Failed to borrow book: $result</p>";
}

// Test 6: Borrow second copy of the same book (should work)
echo "<h2>Test 6: Borrowing Second Copy of Same Book</h2>";
$result3 = $library->borrowBook(2, $book_id); // Using different student
if ($result3 == 'success') {
    echo "<p style='color: green;'>✓ Second copy borrowed successfully</p>";
    
    // Check availability
    $book_after_second_borrow = $library->getBookById($book_id);
    echo "<p>Available copies after second borrowing: " . $book_after_second_borrow['available_copies'] . "/" . $book_after_second_borrow['total_copies'] . "</p>";
    
    // Test 7: Try to borrow third copy (should fail - not available)
    echo "<h2>Test 7: Attempting to Borrow When No Copies Available</h2>";
    $result4 = $library->borrowBook(3, $book_id); // Using different student
    if ($result4 == 'not_available') {
        echo "<p style='color: green;'>✓ Correctly prevented borrowing when no copies available</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to prevent borrowing when no copies available: $result4</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Failed to borrow second copy: $result3</p>";
}

// Cleanup
echo "<h2>Cleanup: Returning Borrowed Books</h2>";
$all_borrowed = $library->getAllBorrowedBooks();
foreach ($all_borrowed as $borrow) {
    if ($borrow['book_id'] == $book_id) {
        $library->returnBook($borrow['id']);
        echo "<p>Returned book borrowed by student " . $borrow['student_id'] . "</p>";
    }
}

// Delete test book
$library->deleteBook($book_id);
echo "<p>Test book deleted</p>";

echo "<h2>All Borrowing Tests Completed</h2>";
?>