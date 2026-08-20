<?php
// =============================================
// Book Details Page
// =============================================
$base_path = '';
$page_title = 'Book Details - Online Library Management System';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
include 'includes/header.php';

$book_id = intval($_GET['id'] ?? 0);
$issue_success = '';
$issue_error = '';

if ($book_id <= 0) {
    $_SESSION['error'] = 'Invalid book ID.';
    header('Location: books.php');
    exit();
}

// Handle issue request (for students)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
    $action = $_POST['action'] ?? '';

    if ($action === 'issue') {
        try {
            $conn->beginTransaction();

            // Check book availability with row lock
            $stmt = $conn->prepare("SELECT available_quantity, total_quantity FROM books WHERE id = ? FOR UPDATE");
            $stmt->execute([$book_id]);
            $book_check = $stmt->fetch();

            if (!$book_check) {
                throw new Exception('Book not found.');
            }

            if ($book_check['available_quantity'] <= 0) {
                throw new Exception('This book is not available for issue.');
            }

            // Check if student already has this book issued
            $stmt = $conn->prepare("SELECT id FROM issued_books WHERE user_id = ? AND book_id = ? AND status = 'issued'");
            $stmt->execute([$_SESSION['user_id'], $book_id]);
            if ($stmt->fetch()) {
                throw new Exception('You already have this book issued. Please return it first.');
            }

            // Issue the book
            $issue_date = date('Y-m-d');
            $due_date = date('Y-m-d', strtotime('+15 days'));

            $stmt = $conn->prepare("INSERT INTO issued_books (user_id, book_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, 'issued')");
            $stmt->execute([$_SESSION['user_id'], $book_id, $issue_date, $due_date]);

            // Decrease available quantity
            $stmt = $conn->prepare("UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ?");
            $stmt->execute([$book_id]);

            $conn->commit();
            $_SESSION['success'] = 'Book issued successfully! Due date: ' . date('d M Y', strtotime($due_date));
            header('Location: book_details.php?id=' . $book_id);
            exit();

        } catch (Exception $e) {
            $conn->rollBack();
            $issue_error = $e->getMessage();
        }
    }
}

// Fetch book details
try {
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if (!$book) {
        $_SESSION['error'] = 'Book not found.';
        header('Location: books.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Something went wrong.';
    header('Location: books.php');
    exit();
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h4><i class="fas fa-book me-2"></i>Book Details</h4>
        <p>Complete information about the selected book</p>
    </div>
</div>

<main class="container py-4">
    <div class="row g-4">
        <!-- Book Info Column -->
        <div class="col-lg-8">
            <div class="content-card">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <div class="book-cover rounded" style="height: 250px; border-radius: 12px !important;">
                                <i class="fas fa-book" style="font-size: 4rem;"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <span class="book-category mb-2 d-inline-block"><?php echo e($book['category']); ?></span>
                            <h3 class="fw-bold mb-2" style="color: var(--primary);"><?php echo e($book['title']); ?></h3>
                            <p class="text-muted mb-1"><i class="fas fa-pen-fancy me-2"></i><strong>Author:</strong> <?php echo e($book['author']); ?></p>

                            <?php if ($book['publisher']): ?>
                                <p class="text-muted mb-1"><i class="fas fa-building me-2"></i><strong>Publisher:</strong> <?php echo e($book['publisher']); ?></p>
                            <?php endif; ?>

                            <?php if ($book['publication_year']): ?>
                                <p class="text-muted mb-1"><i class="fas fa-calendar me-2"></i><strong>Year:</strong> <?php echo e($book['publication_year']); ?></p>
                            <?php endif; ?>

                            <?php if ($book['isbn']): ?>
                                <p class="text-muted mb-1"><i class="fas fa-barcode me-2"></i><strong>ISBN:</strong> <?php echo e($book['isbn']); ?></p>
                            <?php endif; ?>

                            <p class="text-muted mb-1"><i class="fas fa-layer-group me-2"></i><strong>Total Copies:</strong> <?php echo $book['total_quantity']; ?></p>
                            <p class="mb-3">
                                <?php if ($book['available_quantity'] > 0): ?>
                                    <span class="badge bg-success px-3 py-2" style="font-size: 0.9rem;"><i class="fas fa-check-circle me-1"></i><?php echo $book['available_quantity']; ?> Available</span>
                                <?php else: ?>
                                    <span class="badge bg-danger px-3 py-2" style="font-size: 0.9rem;"><i class="fas fa-times-circle me-1"></i>Not Available</span>
                                <?php endif; ?>
                            </p>

                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="btn btn-warning btn-lg"><i class="fas fa-edit me-2"></i>Edit Book</a>
                                    <a href="delete_book.php?id=<?php echo $book['id']; ?>" class="btn btn-danger btn-lg" onclick="return confirmDelete('Are you sure you want to delete this book? This action cannot be undone.')"><i class="fas fa-trash me-2"></i>Delete Book</a>
                                </div>
                            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'student' && $book['available_quantity'] > 0): ?>
                                <form method="POST" action="" onsubmit="return confirmIssue('<?php echo addslashes(e($book['title'])); ?>')">
                                    <input type="hidden" name="action" value="issue">
                                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-hand-holding me-2"></i>Request This Book</button>
                                </form>
                            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'student' && $book['available_quantity'] <= 0): ?>
                                <button class="btn btn-secondary btn-lg" disabled><i class="fas fa-ban me-2"></i>Currently Unavailable</button>
                            <?php elseif (!isset($_SESSION['user_id'])): ?>
                                <a href="login.php" class="btn btn-primary btn-lg"><i class="fas fa-sign-in-alt me-2"></i>Login to Request Book</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Error/Success messages for issue -->
                    <?php if (!empty($issue_error)): ?>
                        <div class="alert alert-danger mt-3"><i class="fas fa-exclamation-circle me-2"></i><?php echo e($issue_error); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <?php if (!empty($book['description'])): ?>
            <div class="content-card mt-4">
                <div class="card-header"><i class="fas fa-info-circle me-2"></i>Description</div>
                <div class="card-body">
                    <p class="text-muted"><?php echo e($book['description']); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="content-card">
                <div class="card-header"><i class="fas fa-info me-2"></i>Quick Info</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted">Book ID</td><td class="fw-semibold text-end">#<?php echo $book['id']; ?></td></tr>
                        <tr><td class="text-muted">Category</td><td class="fw-semibold text-end"><?php echo e($book['category']); ?></td></tr>
                        <tr><td class="text-muted">Total Copies</td><td class="fw-semibold text-end"><?php echo $book['total_quantity']; ?></td></tr>
                        <tr><td class="text-muted">Available</td><td class="fw-semibold text-end"><?php echo $book['available_quantity']; ?></td></tr>
                        <tr><td class="text-muted">Issued</td><td class="fw-semibold text-end"><?php echo $book['total_quantity'] - $book['available_quantity']; ?></td></tr>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                <a href="books.php" class="btn btn-outline-primary w-100"><i class="fas fa-arrow-left me-2"></i>Back to Books</a>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
