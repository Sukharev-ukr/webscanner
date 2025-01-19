<?php
// app/public/controllers/ScanController.php

require_once __DIR__ . '/../models/ScanModel.php'; // if you want to save to DB

class ScanController
{
    public function scanAjax()
    {
        // We expect JSON input: { ip: "...", ports: "80,443" or "1-100" }
        header('Content-Type: application/json');

        // 1. Read the raw POST body and parse JSON
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);

        $ip = trim($data['ip'] ?? '');
        $portsInput = trim($data['ports'] ?? '');

        // 2. Validate
        if (!$ip || !$portsInput) {
            echo json_encode(['openPorts' => [], 'error' => 'Missing IP or ports']);
            return;
        }

        // 3. Parse ports
        $ports = $this->parsePorts($portsInput);
        if (empty($ports)) {
            echo json_encode(['openPorts' => [], 'error' => 'Invalid ports format']);
            return;
        }

        // 4. Check each port with fsockopen
        $openPorts = [];
        foreach ($ports as $port) {
            if ($this->isPortOpen($ip, $port)) {
                $openPorts[] = $port;
            }
        }

        // 5. (Optional) Save to DB
        // If you want to store just the open ports, do something like:
         $scanModel = new ScanModel();
         $scanModel->saveScan($_SESSION['user_id'], $ip, $portsInput, json_encode(['open' => $openPorts]));

        // 6. Return JSON
        echo json_encode([
            'openPorts' => $openPorts,
            'error' => ''
        ]);
    }

    private function parsePorts($portsInput)
    {
        $portsList = [];

        // Detect range, e.g. "1-100"
        if (preg_match('/^(\d+)\-(\d+)$/', $portsInput, $matches)) {
            $start = (int) $matches[1];
            $end   = (int) $matches[2];
            if ($start > 0 && $end >= $start) {
                for ($p = $start; $p <= $end; $p++) {
                    $portsList[] = $p;
                }
            }
        } else {
            // Comma-separated
            $split = explode(',', $portsInput);
            foreach ($split as $p) {
                $p = trim($p);
                if (ctype_digit($p)) {
                    $portsList[] = (int) $p;
                }
            }
        }

        return $portsList;
    }

    private function isPortOpen($ip, $port, $timeout = 0.1)
    {
        $errno = 0;
        $errstr = '';
        $connection = @fsockopen($ip, $port, $errno, $errstr, $timeout);
        if ($connection) {
            fclose($connection);
            return true;
        }
        return false;
    }

    public function historyGet()
{
    if (!class_exists('ScanModel')) {
        die('ScanModel class not found.');
    }

    $scanModel = new ScanModel();
    $scans = $scanModel->getScansByUser($_SESSION['user_id']);
    require __DIR__ . '/../view/pages/history.php';
}
public function adminHistoryGet()
{
    // We assume an admin is already logged in from the route check
    $scanModel = new ScanModel();

    // Optional filters
    $userFilter = $_GET['user'] ?? '';
    $ipFilter   = $_GET['ip'] ?? '';
    $startDate  = $_GET['start'] ?? '';
    $endDate    = $_GET['end'] ?? '';

    // Fetch all scans
    $allScans = $scanModel->getAllScans($userFilter, $ipFilter, $startDate, $endDate);

    // Debug the data
    // echo '<pre>';
    // print_r($allScans);
    // echo '</pre>';

    // Pass the data to the view
    require __DIR__ . '/../view/pages/admin_history.php';
}


public function getAllScans($userFilter = '', $ipFilter = '', $startDate = '', $endDate = '')
{
    $sql = "SELECT scans.*, users.username
            FROM scans
            JOIN users ON scans.user_id = users.id
            WHERE 1=1"; // Always true; we append conditions below

    $params = [];

    if (!empty($userFilter)) {
        $sql .= " AND (users.username LIKE :userFilter OR users.email LIKE :userFilter)";
        $params[':userFilter'] = "%$userFilter%";
    }
    if (!empty($ipFilter)) {
        $sql .= " AND scans.ip_address LIKE :ipFilter";
        $params[':ipFilter'] = "%$ipFilter%";
    }
    if (!empty($startDate)) {
        $sql .= " AND scans.created_at >= :startDate";
        $params[':startDate'] = $startDate . ' 00:00:00';
    }
    if (!empty($endDate)) {
        $sql .= " AND scans.created_at <= :endDate";
        $params[':endDate'] = $endDate . ' 23:59:59';
    }

    $sql .= " ORDER BY scans.created_at DESC";

    $stmt = self::$pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function exportUserScans($userId)
{
    $scanModel = new ScanModel();
    $scans = $scanModel->getScansByUser($userId);

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="user_scan_history.csv"');

    $output = fopen('php://output', 'w');

    // Add CSV headers
    fputcsv($output, ['ID', 'IP Address', 'Ports', 'Open Ports', 'Timestamp']);

    // Add data rows
    foreach ($scans as $scan) {
        $resultsData = json_decode($scan['results'], true);
        $openPorts = is_array($resultsData['open'] ?? []) ? implode(',', $resultsData['open']) : '';

        fputcsv($output, [
            $scan['id'],
            $scan['ip_address'],
            $scan['ports'],
            $openPorts,
            $scan['created_at']
        ]);
    }

    fclose($output);
    exit;
}


public function exportAllScans()
{
    // Fetch all scans
    $scanModel = new ScanModel();
    $scans = $scanModel->getAllScans();

    if (empty($scans)) {
        error_log("Admin Export - No scans found.");
        exit('No scans available to export.');
    }

    // Prepare CSV headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="admin_scan_history.csv"');

    $output = fopen('php://output', 'w');
    if (!$output) {
        error_log("Admin Export - Failed to open output stream.");
        exit('Failed to export data.');
    }

    // Write headers
    fputcsv($output, ['ID', 'User', 'IP Address', 'Ports', 'Open Ports', 'Timestamp']);

    // Write scan data
    foreach ($scans as $scan) {
        $resultsData = json_decode($scan['results'], true);
        $openPorts = is_array($resultsData['open'] ?? []) ? implode(',', $resultsData['open']) : '';

        fputcsv($output, [
            $scan['id'],
            $scan['username'],
            $scan['ip_address'],
            $scan['ports'],
            $openPorts,
            $scan['created_at']
        ]);
    }

    fclose($output);
    exit;
}
public function apiScan()
{
    $data = json_decode(file_get_contents('php://input'), true);
    $ip = trim($data['ip'] ?? '');
    $portsInput = trim($data['ports'] ?? '');

    if (empty($ip) || empty($portsInput)) {
        sendJsonResponse(['error' => 'IP and ports are required'], 400);
    }

    $ports = $this->parsePorts($portsInput);
    if (empty($ports)) {
        sendJsonResponse(['error' => 'Invalid port format'], 400);
    }

    $openPorts = [];
    foreach ($ports as $port) {
        if ($this->isPortOpen($ip, $port)) {
            $openPorts[] = $port;
        }
    }

    sendJsonResponse(['ip' => $ip, 'open_ports' => $openPorts]);
}
public function apiHistory()
{
    header('Content-Type: application/json');

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }

    $userId = $_SESSION['user_id'];
    $scanModel = new ScanModel();

    // Fetch user’s scans
    $scans = $scanModel->getScansByUser($userId);

    if (empty($scans)) {
        echo json_encode(['error' => 'No scans found']);
        return;
    }

    echo json_encode(['scans' => $scans]);
}

public function apiAdminHistory()
{
    // Validate admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        sendJsonResponse(['error' => 'Unauthorized'], 403);
    }

    $scanModel = new ScanModel();
    $scans = $scanModel->getAllScans();

    sendJsonResponse(['history' => $scans]);
}
public function apiHistoryGet()
{
    header('Content-Type: application/json');
    $userId = $_SESSION['user_id'];
    $scanModel = new ScanModel();

    // Retrieve optional filters from query params
    $ipFilter = $_GET['ip'] ?? '';
    $startDate = $_GET['start'] ?? ''; // e.g., 2025-01-01
    $endDate = $_GET['end'] ?? '';     // e.g., 2025-01-31

    // Fetch user-specific scan history
    $scans = $scanModel->getScansByUser($userId, $ipFilter, $startDate, $endDate);

    // Return history as JSON
    echo json_encode(['scans' => $scans]);
}






    public function exportHistoryAsCsv()
    {
        $scanModel = new ScanModel();

        $scans = $scanModel->getAllScans();

        if (empty($scans)) {
            http_response_code(404);
            echo 'No scan history available to export.';
            exit;
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="scan_history.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        fputcsv($output, ['ID', 'User ID', 'IP Address', 'Ports', 'Open Ports', 'Created At']);

        foreach ($scans as $scan) {

            $results = json_decode($scan['results'], true);
            $openPorts = is_array($results['open'] ?? []) ? implode(',', $results['open']) : '';


            fputcsv($output, [
                $scan['id'],
                $scan['user_id'],
                $scan['ip_address'],
                $scan['ports'],
                $openPorts,
                $scan['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    public function exportHistoryAsJson()
    {
        $scanModel = new ScanModel();
        $scans = $scanModel->getAllScans();

        if (empty($scans)) {
            http_response_code(404);
            echo json_encode(['error' => 'No scan history available to export.']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $scans
        ]);
        exit;
    }




}
