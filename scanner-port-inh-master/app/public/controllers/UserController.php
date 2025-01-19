<?php
// app/public/controllers/UserController.php

require_once __DIR__ . '/../models/UserModel.php';

class UserController
{
    public function registerGet()
    {
        // Display the registration form with no error message initially
        $errorMessage = "";
        require __DIR__ . '/../view/pages/register.php';
    }

    public function registerPost()
    {
        // Retrieve form inputs
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        // Basic validation
        $errorMessage = '';
        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $errorMessage = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Invalid email format.';
        } elseif ($password !== $confirmPassword) {
            $errorMessage = 'Passwords do not match.';
        }

        // If there's an error, show the form again
        if ($errorMessage) {
            require __DIR__ . '/../view/pages/register.php';
            return;
        }

        // Otherwise, create the user in DB
        try {
            $userModel = new UserModel();
            $userId = $userModel->createUser($username, $password, $email);

            // Redirect to /login or somewhere else
            header('Location: /login');
            exit;
        } catch (Exception $e) {
            // If something went wrong (DB error, etc.)
            $errorMessage = 'Error: ' . $e->getMessage();
            require __DIR__ . '/../view/pages/register.php';
        }
    }
    public function loginGet()
    {
        // Show the login form
        $errorMessage = '';
        require __DIR__ . '/../view/pages/login.php';
    }
    public function loginPost()
{
    $usernameOrEmail = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($usernameOrEmail) || empty($password)) {
        $errorMessage = 'All fields are required.';
        require __DIR__ . '/../view/pages/login.php';
        return;
    }

    $userModel = new UserModel();
    $user = $userModel->getByUsernameOrEmail($usernameOrEmail);
    if (!$user || !password_verify($password, $user['password'])) {
        $errorMessage = 'Invalid username/password.';
        require __DIR__ . '/../view/pages/login.php';
        return;
    }

    // Start session if needed
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role']   = $user['role']; // IMPORTANT LINE
    session_regenerate_id(true);

    // Now decide where to go:
    if ($user['role'] === 'admin') {
        header('Location: /admin/history');
    } else {
        header('Location: /scan');
    }
    exit;
}


    public function getAll() {
        // Typically you'd call a UserModel method to fetch all users
        $userModel = new UserModel();
        $allUsers = $userModel->getAllUsers(); // or getAll()
        return $allUsers;
    }

    public function logout()
    {
        // Make sure a session is active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Destroy session data
        session_destroy();


         if (ini_get("session.use_cookies")) {
             $params = session_get_cookie_params();
             setcookie(session_name(), '', time() - 42000,
                 $params["path"], $params["domain"],
                 $params["secure"], $params["httponly"]
             );
         }

        // Redirect to the homepage or /login
        header('Location: /login');
        exit;
    }
    public function forgotPasswordGet()
    {
        $errorMessage = '';
        $successMessage = '';
        require __DIR__ . '/../view/pages/forgot_password.php';
    }
    public function forgotPasswordPost()
    {
        $email = trim($_POST['email'] ?? '');
        $errorMessage = '';
        $successMessage = '';

        if (empty($email)) {
            $errorMessage = 'Please enter your email.';
            require __DIR__ . '/../view/pages/forgot_password.php';
            return;
        }

        // Check if user with this email exists
        try {
            $userModel = new UserModel();
            $user = $userModel->getByEmail($email); // We'll define getByEmail($email) in the model

            if (!$user) {
                $errorMessage = 'No account found with that email.';
                require __DIR__ . '/../view/pages/forgot_password.php';
                return;
            }

            // Generate token
            $token = bin2hex(random_bytes(16));

            // Insert into password_resets
            $userModel->createPasswordReset($email, $token);

            // You’d normally email the link. We’ll just show it as success message for demonstration:
            $resetLink = "http://localhost/reset_password?token=" . urlencode($token);
            $successMessage = "Password reset link: $resetLink";

            require __DIR__ . '/../view/pages/forgot_password.php';
        } catch (Exception $e) {
            $errorMessage = 'Error: ' . $e->getMessage();
            require __DIR__ . '/../view/pages/forgot_password.php';
        }
    }
    public function resetPasswordGet()
    {
        $token = $_GET['token'] ?? '';
        $errorMessage = '';
        $successMessage = '';

        // We'll just display the form, but let's confirm the token is valid
        $userModel = new UserModel();
        $reset = $userModel->findPasswordReset($token);
        if (!$reset) {
            $errorMessage = 'Invalid or expired token.';
        }

        require __DIR__ . '/../view/pages/reset_password.php';
    }
    public function resetPasswordPost()
    {
        $token = $_GET['token'] ?? '';
        $newPassword = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        $errorMessage = '';
        $successMessage = '';

        // Validate form
        if (empty($newPassword) || empty($confirmPassword)) {
            $errorMessage = 'All fields are required.';
            require __DIR__ . '/../view/pages/reset_password.php';
            return;
        }
        if ($newPassword !== $confirmPassword) {
            $errorMessage = 'Passwords do not match.';
            require __DIR__ . '/../view/pages/reset_password.php';
            return;
        }

        try {
            $userModel = new UserModel();
            // Check token
            $reset = $userModel->findPasswordReset($token);
            if (!$reset) {
                $errorMessage = 'Invalid or expired token.';
                require __DIR__ . '/../view/pages/reset_password.php';
                return;
            }

            // Update user’s password
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $userModel->updateUserPasswordByEmail($reset['email'], $hashed);

            // Delete token from password_resets or mark used
            $userModel->deletePasswordReset($token);

            $successMessage = 'Password updated. You can now log in with your new password.';
            require __DIR__ . '/../view/pages/reset_password.php';
        } catch (Exception $e) {
            $errorMessage = 'Error: ' . $e->getMessage();
            require __DIR__ . '/../view/pages/reset_password.php';
        }
    }
}
