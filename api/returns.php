<?php
/**
 * API: /api/returns.php
 * Supports JSON and form submissions.
 * Methods:
 *   GET                 -> list overdue books (admin only)
 *   POST action=return  -> return a borrowed book (student or admin)
 *        fields: borrow_id
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
        if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
            json_response(['success' => false, 'error' => 'Admin authorization required'], 403);
        }
        $rows = $lib->getOverdueBooks();
        json_response(['success' => true, 'count' => count($rows), 'data' => $rows]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? strtolower($_POST['action']) : '';

        if ($action !== 'return') {
            json_response(['success' => false, 'error' => 'Invalid or missing action. Expected: return'], 400);
        }
        if (!$auth->isLoggedIn()) {
            json_response(['success' => false, 'error' => 'Authentication required'], 401);
        }
        if (!isset($_POST['borrow_id']) || $_POST['borrow_id'] === '') {
            json_response(['success' => false, 'error' => 'Missing borrow_id'], 422);
        }
        $borrowId = (int)$_POST['borrow_id'];

        $ok = $lib->returnBook($borrowId);
        if ($ok) {
            json_response(['success' => true, 'message' => 'Book returned']);
        } else {
            json_response(['success' => false, 'error' => 'Return failed (invalid borrow_id or DB error)'], 400);
        }
    }

    json_response(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (Throwable $e) {
    json_response(['success' => false, 'error' => 'Server error', 'details' => $e->getMessage()], 500);
}
