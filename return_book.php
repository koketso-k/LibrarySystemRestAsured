<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$library = new LibraryFunctions();

// Redirect if not logged in
if(!$auth->isLoggedIn() || !$auth->isStudent()) {
    header('Location: login.php');
    exit();
}

$borrow_id = isset($_GET['borrow_id']) ? intval($_GET['borrow_id']) : 0;

if($borrow_id <= 0) {
    header('Location: profile.php');
    exit();
}

$result = $library->returnBook($borrow_id);

if($result == 'success') {
    $_SESSION['message'] = 'Book returned successfully!';
} else {
    $_SESSION['error'] = 'Error returning book. Please try again.';
}

header('Location: profile.php');
exit();