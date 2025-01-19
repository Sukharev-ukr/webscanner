<?php
// app/public/models/ScanModel.php
require_once __DIR__ . '/BaseModel.php';

class ScanModel extends BaseModel
{
    public function saveScan($userId, $ip, $ports, $resultsJson)
    {
        $sql = "INSERT INTO scans (user_id, ip_address, ports, results)
                VALUES (:user_id, :ip_address, :ports, :results)";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':user_id'    => $userId,
            ':ip_address' => $ip,
            ':ports'      => $ports,
            ':results'    => $resultsJson
        ]);
        return self::$pdo->lastInsertId();
    }

    // public function getAllScansByUser($userId)
    // {
    //     $sql = "SELECT * FROM scans WHERE user_id = :uid ORDER BY created_at DESC";
    //     $stmt = self::$pdo->prepare($sql);
    //     $stmt->execute([':uid' => $userId]);
    //     return $stmt->fetchAll();
    // }
    public function getScansByUser($userId)
{
    $sql = "SELECT * FROM scans WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = self::$pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getAllScans($userFilter = '', $ipFilter = '', $startDate = '', $endDate = '')
{
    $sql = "SELECT scans.*, users.username
            FROM scans
            JOIN users ON scans.user_id = users.id
            WHERE 1=1"; // always true, we’ll append conditions

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
        $sql .= " AND scans.created_at >= :start";
        $params[':start'] = $startDate . ' 00:00:00';
    }
    if (!empty($endDate)) {
        $sql .= " AND scans.created_at <= :end";
        $params[':end'] = $endDate . ' 23:59:59';
    }

    $sql .= " ORDER BY scans.created_at DESC";

    $stmt = self::$pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



    





    // Optionally, a method to get all scans if you're an admin, etc.
}
