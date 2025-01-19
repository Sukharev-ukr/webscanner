<!-- app/public/view/pages/reset_password.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    >
</head>
<body>
<div class="container mt-5">
    <h1>Reset Password</h1>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <form action="/reset_password?token=<?php echo urlencode($token); ?>" method="POST">
        <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <input
                type="password"
                class="form-control"
                id="new_password"
                name="new_password"
                required
            >
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input
                type="password"
                class="form-control"
                id="confirm_password"
                name="confirm_password"
                required
            >
        </div>
        <button type="submit" class="btn btn-primary">
            Reset
        </button>
    </form>
</div>
</body>
</html>
