<?php

require_once(__DIR__ . "/../controllers/UserController.php");
require_once(__DIR__ . "/../controllers/ScanController.php");

// Set content type for JSON responses
header('Content-Type: application/json');

// Basic function to send JSON response
function sendJsonResponse($data, $statusCode = 200)
{
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Route: Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/api/login') {
    header('Content-Type: application/json');
    $userController = new UserController();
    $userController->apiLogin();
}

// Route: Scan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/api/scan') {
    header('Content-Type: application/json');
    $scanController = new ScanController();
    $scanController->apiScan();
}

// Route: User History
Route::add('/api/history', function() {
    header('Content-Type: application/json');
    $scanController = new ScanController();
    $scanController->apiHistoryGet();
}, 'get');

// Route: Admin History
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/api/admin/history') {
    header('Content-Type: application/json');
    $scanController = new ScanController();
    $scanController->apiAdminHistory();
}

Route::add('/api/export/json', function () {
    header('Content-Type: application/json');
    $scanController = new ScanController();
    $scanController->exportHistoryAsJson();
}, 'get');

Route::add('/api/export/csv', function () {
    header('Content-Type: application/json');
    $scanController = new ScanController();
    $scanController->exportHistoryAsCsv();
}, 'get');

Route::add('/api/scan', function () {
    header('Content-Type: application/json');
    $scanController = new ScanController();
    $scanController->getAllScans(); // Ensure this method returns JSON
}, 'get');


// Default: Not Found
//sendJsonResponse(['error' => 'Endpoint not found'], 404);
