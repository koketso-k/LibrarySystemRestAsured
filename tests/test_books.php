<?php
/**
 * Test file for Book Management Functions
 * Run this file to test all book-related functionality
 */

require_once '../includes/config.php';
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

echo "<h1>Testing Book Management Functions</h1>";

// Create library instance
$library = new LibraryFunctions();

// Test 1: Add a new book
echo "<h2>Test 1: Adding a New Book</h2>";
$result = $library->addBook('978-0136019701', 'PHP Programming', 'John Doe', 'Programming', 2023, 5);
if ($result) {
    echo "<p style='color: green;'>✓ Book added successfully</p>";
    
    // Test 2: Get all books
    echo "<h2>Test 2: Retrieving All Books</h2>";
    $books = $library->getBooks();
    if (count($books) > 0) {
        echo "<p style='color: green;'>✓ " . count($books) . " books retrieved</p>";
        echo "<pre>" . print_r($books, true) . "</pre>";
        
        // Test 3: Get book by ID
        echo "<h2>Test 3: Retrieving Book by ID</h2>";
        $book_id = $books[0]['id'];
        $book = $library->getBookById($book_id);
        if ($book) {
            echo "<p style='color: green;'>✓ Book retrieved successfully</p>";
            echo "<pre>" . print_r($book, true) . "</pre>";
            
            // Test 4: Update book
            echo "<h2>Test 4: Updating Book</h2>";
            $update_result = $library->updateBook($book_id, '978-0136019701', 'PHP Programming Updated', 'John Doe Jr.', 'Programming', 2023, 10);
            if ($update_result == 'success') {
                echo "<p style='color: green;'>✓ Book updated successfully</p>";
                
                // Verify update
                $updated_book = $library->getBookById($book_id);
                echo "<pre>" . print_r($updated_book, true) . "</pre>";
                
                // Test 5: Delete book
                echo "<h2>Test 5: Deleting Book</h2>";
                $delete_result = $library->deleteBook($book_id);
                if ($delete_result == 'success') {
                    echo "<p style='color: green;'>✓ Book deleted successfully</p>";
                } else {
                    echo "<p style='color: red;'>✗ Failed to delete book: $delete_result</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Failed to update book: $update_result</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Failed to retrieve book by ID</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ No books retrieved</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Failed to add book</p>";
}

// Test 6: Search books
echo "<h2>Test 6: Searching Books</h2>";
$library->addBook('978-0136019702', 'JavaScript Basics', 'Jane Smith', 'Programming', 2022, 3);
$library->addBook('978-0136019703', 'Advanced JavaScript', 'Jane Smith', 'Programming', 2023, 2);

$search_results = $library->getBooks('JavaScript');
echo "<p>Search results for 'JavaScript': " . count($search_results) . " books found</p>";
echo "<pre>" . print_r($search_results, true) . "</pre>";

// Cleanup: Remove test books
echo "<h2>Cleanup: Removing Test Books</h2>";
$all_books = $library->getBooks();
foreach ($all_books as $book) {
    if (in_array($book['title'], ['PHP Programming', 'JavaScript Basics', 'Advanced JavaScript'])) {
        $library->deleteBook($book['id']);
        echo "<p>Deleted: " . $book['title'] . "</p>";
    }
}

echo "<h2>All Book Tests Completed</h2>";
?>