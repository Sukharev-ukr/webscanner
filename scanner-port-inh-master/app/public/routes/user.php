<?php

require_once(__DIR__ . "/../controllers/UserController.php");
require_once(__DIR__ . "/../controllers/ScanController.php");


Route::add('/user/([a-z-0-9-]*)', function ($userId) {
    $userController = new UserController();
    $user = $userController->get($userId);
    require_once(__DIR__ . "/../view/pages/user.php");
});
Route::add('/register', function() {
    $controller = new UserController();
    $controller->registerGet();
}, 'get');

// POST /register
Route::add('/register', function() {
    $controller = new UserController();
    $controller->registerPost();
}, 'post');

// GET /login
Route::add('/login', function() {
    $userController = new UserController();
    $userController->loginGet();
}, 'get');

// POST /login
Route::add('/login', function() {
    $userController = new UserController();
    $userController->loginPost();
}, 'post');
Route::add('/users', function () {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    $userController = new UserController();
    $users = $userController->getAll();
    require_once(__DIR__ . "/../view/pages/users.php");
});

Route::add('/logout', function() {
    // We'll define a method like logout() in UserController
    $userController = new UserController();
    $userController->logout();
}, 'get');

// GET /forgot_password => show form
Route::add('/forgot_password', function() {
    $userController = new UserController();
    $userController->forgotPasswordGet();
}, 'get');

// POST /forgot_password => process form
Route::add('/forgot_password', function() {
    $userController = new UserController();
    $userController->forgotPasswordPost();
}, 'post');

// GET /reset_password?token=XYZ
Route::add('/reset_password', function() {
    $userController = new UserController();
    $userController->resetPasswordGet();
}, 'get');

// POST /reset_password => user submits new password
Route::add('/reset_password', function() {
    $userController = new UserController();
    $userController->resetPasswordPost();
}, 'post');

// GET /scan => show the scanning page
Route::add('/scan', function() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    // Just load the view
    require_once __DIR__ . '/../view/pages/scan.php';
}, 'get');

// POST /scan => handle AJAX scanning
Route::add('/scan', function() {
    if (!isset($_SESSION['user_id'])) {
        header('Content-Type: application/json', true, 401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $scanController = new ScanController();
    $scanController->scanAjax();
}, 'post');

// user.php or a new file, up to you
Route::add('/history', function() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    $scanController = new ScanController();
    $scanController->historyGet();
}, 'get');

Route::add('/admin/history', function() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: /login');
        exit;
    }

    $scanController = new ScanController();
    $scanController->adminHistoryGet();
}, 'get');

// Export user scan history
Route::add('/export', function () {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    $userId = $_SESSION['user_id'];
    $scanController = new ScanController();
    $scanController->exportUserScans($userId);
}, 'get');
// Export all scans (admin only)
Route::add('/admin/export', function () {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: /login');
        exit;
    }

    $scanController = new ScanController();
    $scanController->exportAllScans();
}, 'get');






if (preg_match('#^/api/#', $_SERVER['REQUEST_URI'])) {
    require_once (__DIR__ . "/../api/routes.php");
}









