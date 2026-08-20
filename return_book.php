<?php
// =============================================
// Return Book Page (Admin Only)
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$required_role = 'admin';
require_once 'config/database.php';
require_once 'includes/auth.php';

$issue_id = intval($_GET['id'] ?? 0);

if ($issue_id <= 0) {
    $_SESSION['error'] = 'Invalid issue ID.';
    header('Location: issue_book.php');
    exit();
}

try {
    $conn->beginTransaction();

    // Get issue record with lock
    $stmt = $conn->prepare("SELECT * FROM issued_books WHERE id = ? AND status = 'issued' FOR UPDATE");
    $stmt->execute([$issue_id]);
    $issue = $stmt->fetch();

    if (!$issue) {
        throw new Exception('Issue record not found or already returned.');
    }

    // Update issued_books record
    $return_date = date('Y-m-d');
    $stmt = $conn->prepare("UPDATE issued_books SET return_date = ?, status = 'returned' WHERE id = ?");
    $stmt->execute([$return_date, $issue_id]);

    // Increase available book quantity
    $stmt = $conn->prepare("UPDATE books SET available_quantity = available_quantity + 1 WHERE id = ?");
    $stmt->execute([$issue['book_id']]);

    $conn->commit();
    $_SESSION['success'] = 'Book returned successfully!';
    header('Location: issue_book.php');
    exit();

} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = $e->getMessage();
    header('Location: issue_book.php');
    exit();
}
