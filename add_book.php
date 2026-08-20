<?php
// =============================================
// Add Book Page (Admin Only)
// =============================================
$base_path = '';
$page_title = 'Add Book - Online Library Management System';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$required_role = 'admin';
require_once 'config/database.php';
require_once 'includes/auth.php';
include 'includes/header.php';

$error = '';

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
    } elseif ($publication_year < 0 || $publication_year > date('Y') + 1) {
        $error = 'Please enter a valid publication year.';
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO books (title, author, category, isbn, publisher, publication_year, total_quantity, available_quantity, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $author, $category, $isbn, $publisher, $publication_year, $total_quantity, $total_quantity, $description]);

            $_SESSION['success'] = 'Book "' . $title . '" added successfully!';
            header('Location: books.php');
            exit();
        } catch (PDOException $e) {
            $error = 'Failed to add book. Please try again.';
        }
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h4><i class="fas fa-plus-circle me-2"></i>Add New Book</h4>
        <p>Add a new book to the library catalog</p>
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
                            <input type="text" name="title" class="form-control" placeholder="Enter book title" required value="<?php echo isset($title) ? e($title) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Author *</label>
                            <input type="text" name="author" class="form-control" placeholder="Enter author name" required value="<?php echo isset($author) ? e($author) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="Computer Science" <?php echo (isset($category) && $category === 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                                <option value="Electronics" <?php echo (isset($category) && $category === 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
                                <option value="Mathematics" <?php echo (isset($category) && $category === 'Mathematics') ? 'selected' : ''; ?>>Mathematics</option>
                                <option value="Physics" <?php echo (isset($category) && $category === 'Physics') ? 'selected' : ''; ?>>Physics</option>
                                <option value="Chemistry" <?php echo (isset($category) && $category === 'Chemistry') ? 'selected' : ''; ?>>Chemistry</option>
                                <option value="Mechanical" <?php echo (isset($category) && $category === 'Mechanical') ? 'selected' : ''; ?>>Mechanical</option>
                                <option value="Electrical" <?php echo (isset($category) && $category === 'Electrical') ? 'selected' : ''; ?>>Electrical</option>
                                <option value="Communication" <?php echo (isset($category) && $category === 'Communication') ? 'selected' : ''; ?>>Communication</option>
                                <option value="Other" <?php echo (isset($category) && $category === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" class="form-control" placeholder="e.g. 978-0262033848" value="<?php echo isset($isbn) ? e($isbn) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Publisher</label>
                            <input type="text" name="publisher" class="form-control" placeholder="Enter publisher name" value="<?php echo isset($publisher) ? e($publisher) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Publication Year</label>
                            <input type="number" name="publication_year" class="form-control" placeholder="e.g. 2024" min="1900" max="<?php echo date('Y') + 1; ?>" value="<?php echo isset($publication_year) && $publication_year > 0 ? e($publication_year) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Quantity *</label>
                            <input type="number" name="total_quantity" class="form-control" placeholder="e.g. 5" min="1" required value="<?php echo isset($total_quantity) && $total_quantity > 0 ? e($total_quantity) : '1'; ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Enter a brief description of the book..."><?php echo isset($description) ? e($description) : ''; ?></textarea>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Add Book</button>
                                <a href="books.php" class="btn btn-outline-secondary px-4"><i class="fas fa-times me-2"></i>Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
