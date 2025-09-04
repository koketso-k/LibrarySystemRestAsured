<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$library = new LibraryFunctions();

// Redirect if not admin
if(!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Get overdue books
$overdue_books = $library->getOverdueBooks();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=overdue_books_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, array('Student Name', 'Student Number', 'Book Title', 'ISBN', 'Borrow Date', 'Due Date', 'Days Overdue'));

// Add data rows
$today = new DateTime();
foreach($overdue_books as $book) {
    $due_date = new DateTime($book['due_date']);
    $interval = $today->diff($due_date);
    $days_overdue = $interval->days;
    if($today > $due_date) {
        $days_overdue = $interval->days;
    } else {
        $days_overdue = 0;
    }
    
    fputcsv($output, array(
        $book['full_name'],
        $book['student_number'],
        $book['title'],
        $book['isbn'],
        $book['borrow_date'],
        $book['due_date'],
        $days_overdue
    ));
}

fclose($output);
exit();