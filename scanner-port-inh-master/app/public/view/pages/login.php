<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  >
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
  <div class="card shadow-sm p-4" style="max-width: 400px; width: 100%;">
    <h3 class="text-center mb-4">Login</h3>
    <form method="POST" action="/login">
      <div class="mb-3">
        <label for="username" class="form-label">Username or Email</label>
        <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username or email" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <div class="text-center mt-3">
      <a href="/forgot_password" class="text-decoration-none">Forgot Password?</a>
    </div>
  </div>
</body>
</html>
