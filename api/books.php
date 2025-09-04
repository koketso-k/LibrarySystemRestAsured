<?php
/**
 * API: /api/books.php
 * Supports JSON and form submissions.
 * Methods:
 *   GET    ?search=...                 -> list books (optionally filtered)
 *   GET    ?id=123                     -> get single book by id
 *   POST   action=add                  -> add a book (admin only)
 *   POST   action=update&id=123        -> update a book (admin only)
 *   POST   action=delete&id=123        -> delete a book (admin only)
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$auth = new Auth();
$lib  = new LibraryFunctions();

function wants_json() {
    if (isset($_GET['format']) && strtolower($_GET['format']) === 'json') return true;
    if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) return true;
    if (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) return true;
    return true; // default JSON for API
}

function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $book = $lib->getBookById($id);
            if ($book) {
                json_response(['success' => true, 'data' => $book]);
            } else {
                json_response(['success' => false, 'error' => 'Book not found'], 404);
            }
        } else {
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $books = $lib->getBooks($search);
            json_response(['success' => true, 'count' => count($books), 'data' => $books]);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? strtolower($_POST['action']) : '';

        if (!in_array($action, ['add','update','delete'])) {
            json_response(['success' => false, 'error' => 'Invalid or missing action. Expected one of: add, update, delete'], 400);
        }

        if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
            json_response(['success' => false, 'error' => 'Admin authorization required'], 403);
        }

        if ($action === 'add') {
            $required = ['isbn','title','author','category','publication_year','total_copies'];
            foreach ($required as $r) {
                if (!isset($_POST[$r]) || $_POST[$r] === '') {
                    json_response(['success' => false, 'error' => "Missing field: $r"], 422);
                }
            }
            $ok = $lib->addBook(
                $_POST['isbn'],
                $_POST['title'],
                $_POST['author'],
                $_POST['category'],
                (int)$_POST['publication_year'],
                (int)$_POST['total_copies']
            );
            if ($ok) {
                json_response(['success' => true, 'message' => 'Book added']);
            } else {
                json_response(['success' => false, 'error' => 'Failed to add book'], 500);
            }
        }

        if ($action === 'update') {
            if (!isset($_POST['id'])) json_response(['success' => false, 'error' => 'Missing id'], 422);
            $id = (int)$_POST['id'];
            $required = ['isbn','title','author','category','publication_year','total_copies'];
            foreach ($required as $r) {
                if (!isset($_POST[$r]) || $_POST[$r] === '') {
                    json_response(['success' => false, 'error' => "Missing field: $r"], 422);
                }
            }
            $ok = $lib->updateBook(
                $id,
                $_POST['isbn'],
                $_POST['title'],
                $_POST['author'],
                $_POST['category'],
                (int)$_POST['publication_year'],
                (int)$_POST['total_copies']
            );
            if ($ok) {
                json_response(['success' => true, 'message' => 'Book updated']);
            } else {
                json_response(['success' => false, 'error' => 'Failed to update book'], 500);
            }
        }

        if ($action === 'delete') {
            if (!isset($_POST['id'])) json_response(['success' => false, 'error' => 'Missing id'], 422);
            $id = (int)$_POST['id'];
            $ok = $lib->deleteBook($id);
            if ($ok) {
                json_response(['success' => true, 'message' => 'Book deleted']);
            } else {
                json_response(['success' => false, 'error' => 'Failed to delete book'], 500);
            }
        }
    }

    json_response(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (Throwable $e) {
    json_response(['success' => false, 'error' => 'Server error', 'details' => $e->getMessage()], 500);
}
