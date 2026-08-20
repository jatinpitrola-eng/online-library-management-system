<?php
// =============================================
// Navigation Bar
// =============================================
$base_path = isset($base_path) ? $base_path : '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo $base_path; ?>index.php">
            <i class="fas fa-book-open me-2"></i>LibraryMS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>index.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'books.php' || basename($_SERVER['PHP_SELF']) == 'book_details.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>books.php">
                        <i class="fas fa-book me-1"></i> Books
                    </a>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i> My Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_books.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>my_books.php">
                        <i class="fas fa-book-reader me-1"></i> My Books
                    </a>
                </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>students.php">
                        <i class="fas fa-users me-1"></i> Students
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'issue_book.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>issue_book.php">
                        <i class="fas fa-hand-holding me-1"></i> Issue Book
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'admin/dashboard.php') !== false) ? 'active' : ''; ?>" href="<?php echo $base_path; ?>admin/dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i> <?php echo e($_SESSION['full_name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo $base_path; ?>profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                        <?php if ($_SESSION['role'] === 'student'): ?>
                        <li><a class="dropdown-item" href="<?php echo $base_path; ?>dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>My Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?php echo $base_path; ?>my_books.php"><i class="fas fa-book-reader me-2"></i>My Books</a></li>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?php echo $base_path; ?>add_book.php"><i class="fas fa-plus me-2"></i>Add Book</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo $base_path; ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_path; ?>login.php"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_path; ?>register.php"><i class="fas fa-user-plus me-1"></i> Register</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>