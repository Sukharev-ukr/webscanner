<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <!-- Bootstrap CSS (CDN) -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    >
</head>
<body>
<div class="container mt-5">
    <h1>Register</h1>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form action="/register" method="POST" class="row g-3">
        <div class="col-md-6">
            <label for="username" class="form-label">Username</label>
            <input
                type="text"
                class="form-control"
                id="username"
                name="username"
                required
            >
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                required
            >
        </div>

        <div class="col-md-6">
            <label for="password" class="form-label">Password</label>
            <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                required
            >
        </div>

        <div class="col-md-6">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input
                type="password"
                class="form-control"
                id="confirm_password"
                name="confirm_password"
                required
            >
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">
                Register
            </button>
        </div>
    </form>
</div>

<!-- (Optional) Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>
</body>
</html>