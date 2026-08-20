<?php
// =============================================
// Edit Book Page (Admin Only)
// =============================================
$base_path = '';
$page_title = 'Edit Book - Online Library Management System';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$required_role = 'admin';
require_once 'config/database.php';
require_once 'includes/auth.php';
include 'includes/header.php';

$book_id = intval($_GET['id'] ?? 0);
$error = '';

if ($book_id <= 0) {
    $_SESSION['error'] = 'Invalid book ID.';
    header('Location: books.php');
    exit();
}

// Fetch current book data
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $publication_year = intval($_POST['publication_year'] ?? 0);
    $total_quantity = intval($_POST['total_quantity'] ?? 1);
    $description = trim($_POST['description'] ?? '');

    // Validation
    if (empty($title) || empty($author) || empty($category)) {
        $error = 'Title, author, and category are required fields.';
    } elseif ($total_quantity < 1) {
        $error = 'Total quantity must be at least 1.';
    } else {
        try {
            // Calculate new available quantity
            $currently_issued = $book['total_quantity'] - $book['available_quantity'];
            $new_available = max(0, $total_quantity - $currently_issued);

            $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, category = ?, isbn = ?, publisher = ?, publication_year = ?, total_quantity = ?, available_quantity = ?, description = ? WHERE id = ?");
            $stmt->execute([$title, $author, $category, $isbn, $publisher, $publication_year, $total_quantity, $new_available, $description, $book_id]);

            $_SESSION['success'] = 'Book updated successfully!';
            header('Location: book_details.php?id=' . $book_id);
            exit();
        } catch (PDOException $e) {
            $error = 'Failed to update book. Please try again.';
        }
    }
} else {
    // Pre-fill form with existing data
    $title = $book['title'];
    $author = $book['author'];
    $category = $book['category'];
    $isbn = $book['isbn'];
    $publisher = $book['publisher'];
    $publication_year = $book['publication_year'];
    $total_quantity = $book['total_quantity'];
    $description = $book['description'];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h4><i class="fas fa-edit me-2"></i>Edit Book</h4>
        <p>Update book information in the library catalog</p>
    </div>
</div>

<main class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-section">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Book Title *</label>
                            <input type="text" name="title" class="form-control" required value="<?php echo e($title); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Author *</label>
                            <input type="text" name="author" class="form-control" required value="<?php echo e($author); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="Computer Science" <?php echo $category === 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                                <option value="Electronics" <?php echo $category === 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                                <option value="Mathematics" <?php echo $category === 'Mathematics' ? 'selected' : ''; ?>>Mathematics</option>
                                <option value="Physics" <?php echo $category === 'Physics' ? 'selected' : ''; ?>>Physics</option>
                                <option value="Chemistry" <?php echo $category === 'Chemistry' ? 'selected' : ''; ?>>Chemistry</option>
                                <option value="Mechanical" <?php echo $category === 'Mechanical' ? 'selected' : ''; ?>>Mechanical</option>
                                <option value="Electrical" <?php echo $category === 'Electrical' ? 'selected' : ''; ?>>Electrical</option>
                                <option value="Communication" <?php echo $category === 'Communication' ? 'selected' : ''; ?>>Communication</option>
                                <option value="Other" <?php echo $category === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" class="form-control" value="<?php echo e($isbn); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Publisher</label>
                            <input type="text" name="publisher" class="form-control" value="<?php echo e($publisher); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Publication Year</label>
                            <input type="number" name="publication_year" class="form-control" min="1900" max="<?php echo date('Y') + 1; ?>" value="<?php echo $publication_year > 0 ? e($publication_year) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Quantity *</label>
                            <input type="number" name="total_quantity" class="form-control" min="1" required value="<?php echo e($total_quantity); ?>">
                            <small class="text-muted">Currently <?php echo $book['total_quantity'] - $book['available_quantity']; ?> copy(ies) issued.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"><?php echo e($description); ?></textarea>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Update Book</button>
                                <a href="book_details.php?id=<?php echo $book_id; ?>" class="btn btn-outline-secondary px-4"><i class="fas fa-times me-2"></i>Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
