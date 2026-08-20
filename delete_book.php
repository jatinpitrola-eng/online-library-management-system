<?php
// =============================================
// Delete Book Page (Admin Only)
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$required_role = 'admin';
require_once 'config/database.php';
require_once 'includes/auth.php';

$book_id = intval($_GET['id'] ?? 0);

if ($book_id <= 0) {
    $_SESSION['error'] = 'Invalid book ID.';
    header('Location: books.php');
    exit();
}

try {
    // Check if book has active issues
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM issued_books WHERE book_id = ? AND status = 'issued'");
    $stmt->execute([$book_id]);
    $active_issues = $stmt->fetch()['total'];

    if ($active_issues > 0) {
        $_SESSION['error'] = 'Cannot delete this book. ' . $active_issues . ' copy(ies) are currently issued. Please return them first.';
        header('Location: book_details.php?id=' . $book_id);
        exit();
    }

    // Delete return history for this book
    $stmt = $conn->prepare("DELETE FROM issued_books WHERE book_id = ?");
    $stmt->execute([$book_id]);

    // Delete the book
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([$book_id]);

    $_SESSION['success'] = 'Book deleted successfully!';
    header('Location: books.php');
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = 'Failed to delete book. It may be referenced by other records.';
    header('Location: books.php');
    exit();
}
