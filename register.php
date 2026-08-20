<?php
// =============================================
// Registration Page
// =============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: books.php');
    exit();
}

require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Full name, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password and confirm password do not match.';
    } else {
        try {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'This email is already registered. Please login instead.';
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, 'student')");
                $stmt->execute([$full_name, $email, $phone, $hashed_password]);

                $_SESSION['success'] = 'Registration successful! Please login.';
                header('Location: login.php');
                exit();
            }
        } catch (PDOException $e) {
            $error = 'Something went wrong. Please try again.';
        }
    }
}
$page_title = 'Register - Online Library Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? e($page_title) : 'Register'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="logo">
            <i class="fas fa-book-open"></i>
            <h3>LibraryMS</h3>
            <p class="text-muted">Create a new student account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" name="full_name" class="form-control border-start-0" placeholder="Enter your full name" required value="<?php echo isset($full_name) ? e($full_name) : ''; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control border-start-0" placeholder="Enter your email" required value="<?php echo isset($email) ? e($email) : ''; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Phone Number</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-phone text-muted"></i></span>
                    <input type="text" name="phone" class="form-control border-start-0" placeholder="Enter your phone number" value="<?php echo isset($phone) ? e($phone) : ''; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="Min 6 characters" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="confirm_password" class="form-control border-start-0" placeholder="Confirm your password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-user-plus me-2"></i>Register
            </button>
        </form>

        <p class="text-center text-muted mb-1">Already have an account?</p>
        <p class="text-center"><a href="login.php" class="fw-semibold" style="color: var(--accent);">Login here</a></p>

        <div class="text-center mt-3">
            <a href="index.php" class="text-muted text-decoration-none" style="font-size: 0.85rem;"><i class="fas fa-arrow-left me-1"></i>Back to Home</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>