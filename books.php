<?php
// =============================================
// Books Listing Page - For all users
// =============================================
$base_path = '';
$page_title = 'Books - Online Library Management System';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
include 'includes/header.php';

// Search functionality
$search = trim($_GET['search'] ?? '');
$category_filter = trim($_GET['category'] ?? '');

// Build query
$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($category_filter)) {
    $where_clauses[] = "category = ?";
    $params[] = $category_filter;
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get books
try {
    $query = "SELECT * FROM books $where_sql ORDER BY title ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $books = $stmt->fetchAll();
} catch (PDOException $e) {
    $books = [];
}

// Get all categories
try {
    $stmt = $conn->prepare("SELECT DISTINCT category FROM books ORDER BY category ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h4><i class="fas fa-book me-2"></i>Library Books</h4>
        <p>Browse and search our complete book collection</p>
    </div>
</div>

<main class="container py-4">
    <!-- Search & Filter -->
    <div class="search-section">
        <form method="GET" action="">
            <div class="row g-3 justify-content-center">
                <div class="col-md-6">
                    <div class="search-box input-group">
                        <input type="text" name="search" class="form-control search-input" placeholder="Search by title, author, or ISBN..." value="<?php echo e($search); ?>">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select" style="border-radius: 8px; padding: 0.7rem;" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo e($cat); ?>" <?php echo $category_filter === $cat ? 'selected' : ''; ?>><?php echo e($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!empty($search) || !empty($category_filter)): ?>
                    <div class="col-md-auto d-flex align-items-end">
                        <a href="books.php" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i>Clear</a>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="text-muted">
            Showing <strong><?php echo count($books); ?></strong> books
            <?php if (!empty($search)): ?>for "<strong><?php echo e($search); ?></strong>"<?php endif; ?>
            <?php if (!empty($category_filter)): ?>in <strong><?php echo e($category_filter); ?></strong><?php endif; ?>
        </span>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="add_book.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add New Book</a>
        <?php endif; ?>
    </div>

    <!-- Books Grid -->
    <?php if (count($books) > 0): ?>
    <div class="row g-4">
        <?php foreach ($books as $book): ?>
        <div class="col-lg-3 col-md-4 col-sm-6 book-card-wrapper" data-title="<?php echo e($book['title']); ?>" data-author="<?php echo e($book['author']); ?>" data-category="<?php echo e($book['category']); ?>">
            <div class="book-card">
                <div class="book-cover">
                    <i class="fas fa-book"></i>
                </div>
                <div class="book-info">
                    <span class="book-category"><?php echo e($book['category']); ?></span>
                    <h5 class="book-title"><?php echo e($book['title']); ?></h5>
                    <p class="book-author"><i class="fas fa-pen-fancy me-1"></i><?php echo e($book['author']); ?></p>
                    <?php if ($book['isbn']): ?>
                        <small class="text-muted d-block mb-1"><i class="fas fa-barcode me-1"></i>ISBN: <?php echo e($book['isbn']); ?></small>
                    <?php endif; ?>
                    <div class="book-availability d-flex justify-content-between align-items-center">
                        <?php if ($book['available_quantity'] > 0): ?>
                            <span class="available"><i class="fas fa-check-circle me-1"></i><?php echo $book['available_quantity']; ?> Available</span>
                        <?php else: ?>
                            <span class="unavailable"><i class="fas fa-times-circle me-1"></i>Not Available</span>
                        <?php endif; ?>
                        <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline-primary">
                            View <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-search"></i>
        <h5>No Books Found</h5>
        <p class="text-muted">Try adjusting your search or filter criteria.</p>
        <a href="books.php" class="btn btn-primary mt-2"><i class="fas fa-list me-2"></i>View All Books</a>
    </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
