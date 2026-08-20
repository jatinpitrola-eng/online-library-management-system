<?php
// =============================================
// My Books - Student's issued and returned books
// =============================================
$base_path = '';
$page_title = 'My Books - Online Library Management System';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'includes/auth.php';
include 'includes/header.php';

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'issued';

try {
    // Currently issued books
    $stmt = $conn->prepare("SELECT ib.*, b.title as book_title, b.author as book_author, b.category, b.isbn 
                          FROM issued_books ib 
                          JOIN books b ON ib.book_id = b.id 
                          WHERE ib.user_id = ? AND ib.status = 'issued' 
                          ORDER BY ib.due_date ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $issued = $stmt->fetchAll();

    // Returned books
    $stmt = $conn->prepare("SELECT ib.*, b.title as book_title, b.author as book_author 
                          FROM issued_books ib 
                          JOIN books b ON ib.book_id = b.id 
                          WHERE ib.user_id = ? AND ib.status = 'returned' 
                          ORDER BY ib.return_date DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $returned = $stmt->fetchAll();

} catch (PDOException $e) {
    die('Error loading book history.');
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h4><i class="fas fa-book-reader me-2"></i>My Books</h4>
        <p>View your currently issued books and return history</p>
    </div>
</div>

<main class="container py-4">
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'issued' ? 'active' : ''; ?>" href="my_books.php?tab=issued">
                <i class="fas fa-hand-holding me-1"></i>Currently Issued (<?php echo count($issued); ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'returned' ? 'active' : ''; ?>" href="my_books.php?tab=returned">
                <i class="fas fa-undo me-1"></i>Return History (<?php echo count($returned); ?>)
            </a>
        </li>
    </ul>

    <?php if ($tab === 'issued'): ?>
        <!-- Currently Issued -->
        <div class="content-card">
            <div class="card-body p-0">
                <?php if (count($issued) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($issued as $item): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><a href="book_details.php?id=<?php echo $item['book_id']; ?>" class="text-decoration-none fw-semibold"><?php echo e($item['book_title']); ?></a></td>
                                <td><?php echo e($item['book_author']); ?></td>
                                <td><?php echo date('d M Y', strtotime($item['issue_date'])); ?></td>
                                <td>
                                    <?php echo date('d M Y', strtotime($item['due_date'])); ?>
                                    <?php if (strtotime($item['due_date']) < time()): ?>
                                        <br><span class="text-danger" style="font-size: 0.78rem;"><i class="fas fa-exclamation-triangle"></i> Overdue!</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (strtotime($item['due_date']) < time()): ?>
                                        <span class="badge bg-danger badge-status">Overdue</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning badge-status">Issued</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h5>No Books Issued</h5>
                    <p class="text-muted">You don't have any books currently issued.</p>
                    <a href="books.php" class="btn btn-primary mt-2"><i class="fas fa-search me-2"></i>Browse Books</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Return History -->
        <div class="content-card">
            <div class="card-body p-0">
                <?php if (count($returned) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Issue Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($returned as $item): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td class="fw-semibold"><?php echo e($item['book_title']); ?></td>
                                <td><?php echo e($item['book_author']); ?></td>
                                <td><?php echo date('d M Y', strtotime($item['issue_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($item['return_date'])); ?></td>
                                <td><span class="badge bg-success badge-status">Returned</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h5>No Return History</h5>
                    <p class="text-muted">Your returned books will appear here.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>