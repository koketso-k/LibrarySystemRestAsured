<?php
/**
 * API: /api/borrow.php
 * Supports JSON and form submissions.
 * Methods:
 *   GET                      -> list current user's borrowed books (student)
 *   GET    ?all=1            -> list all borrowed books (admin only)
 *   POST   action=borrow     -> borrow a book (student)
 *        fields: book_id (or isbn with id lookup)
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$auth = new Auth();
$lib  = new LibraryFunctions();

function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!$auth->isLoggedIn()) {
            json_response(['success' => false, 'error' => 'Authentication required'], 401);
        }
        if (isset($_GET['all']) && $auth->isAdmin()) {
            $rows = $lib->getAllBorrowedBooks();
            json_response(['success' => true, 'count' => count($rows), 'data' => $rows]);
        } else {
            // For students, list their borrowed books
            $studentId = $_SESSION['user']['id'] ?? null;
            if (!$studentId) json_response(['success' => false, 'error' => 'Student session not found'], 401);
            $rows = $lib->getBorrowedBooks($studentId);
            json_response(['success' => true, 'count' => count($rows), 'data' => $rows]);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? strtolower($_POST['action']) : '';

        if ($action !== 'borrow') {
            json_response(['success' => false, 'error' => 'Invalid or missing action. Expected: borrow'], 400);
        }
        if (!$auth->isLoggedIn() || !$auth->isStudent()) {
            json_response(['success' => false, 'error' => 'Student authentication required'], 403);
        }

        $studentId = $_SESSION['user']['id'] ?? null;
        if (!$studentId) json_response(['success' => false, 'error' => 'Student session not found'], 401);

        if (!isset($_POST['book_id']) || $_POST['book_id'] === '') {
            json_response(['success' => false, 'error' => 'Missing book_id'], 422);
        }
        $bookId = (int)$_POST['book_id'];

        $ok = $lib->borrowBook($studentId, $bookId);
        if ($ok) {
            json_response(['success' => true, 'message' => 'Book borrowed']);
        } else {
            json_response(['success' => false, 'error' => 'Borrow failed (no copies available, already borrowed, or DB error)'], 400);
        }
    }

    json_response(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (Throwable $e) {
    json_response(['success' => false, 'error' => 'Server error', 'details' => $e->getMessage()], 500);
}
