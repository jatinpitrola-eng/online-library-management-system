<?php
// =============================================
// Footer - Include this at the bottom of every page
// =============================================
$base_path = isset($base_path) ? $base_path : '';
?>

<!-- Flash Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-5 z-3 shadow" role="alert" style="z-index: 9999; min-width: 300px;">
        <i class="fas fa-check-circle me-2"></i> <?php echo e($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-5 z-3 shadow" role="alert" style="z-index: 9999; min-width: 300px;">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo e($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5 class="fw-bold"><i class="fas fa-book-open me-2"></i>Online Library Management System</h5>
                <p class="text-muted mb-0">A complete college library management solution.<br>Manage books, students, and issue/return operations efficiently.</p>
            </div>
            <div class="col-md-3">
                <h6 class="fw-bold">Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="<?php echo $base_path; ?>index.php" class="text-muted text-decoration-none">Home</a></li>
                    <li><a href="<?php echo $base_path; ?>books.php" class="text-muted text-decoration-none">Browse Books</a></li>
                    <li><a href="<?php echo $base_path; ?>login.php" class="text-muted text-decoration-none">Login</a></li>
                    <li><a href="<?php echo $base_path; ?>register.php" class="text-muted text-decoration-none">Register</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="fw-bold">Technology</h6>
                <ul class="list-unstyled text-muted">
                    <li><i class="fab fa-php me-2"></i>PHP 8+</li>
                    <li><i class="fas fa-database me-2"></i>MySQL</li>
                    <li><i class="fab fa-bootstrap me-2"></i>Bootstrap 5</li>
                    <li><i class="fab fa-js me-2"></i>JavaScript</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary">
        <p class="text-center text-muted mb-0">&copy; <?php echo date('Y'); ?> Online Library Management System. College Project.</p>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo $base_path; ?>js/script.js"></script>

<!-- Auto-hide flash messages after 5 seconds -->
<script>
setTimeout(function() {
    document.querySelectorAll('.alert').forEach(function(alert) {
        var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        if (bsAlert) bsAlert.close();
    });
}, 5000);
</script>
</body>
</html>