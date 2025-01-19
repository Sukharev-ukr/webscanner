<!-- app/public/view/pages/forgot_password.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    >
</head>
<body>
<div class="container mt-5">
    <h1>Forgot Password</h1>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <form action="/forgot_password" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Enter your account email</label>
            <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                required
            >
        </div>
        <button type="submit" class="btn btn-primary">
            Send Reset Link
        </button>
    </form>
</div>
</body>
</html>
