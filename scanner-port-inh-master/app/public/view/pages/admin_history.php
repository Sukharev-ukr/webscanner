<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Admin Scan History</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-3">
<div class="container">
  <h1>Admin Scan History</h1>

  <!-- Filter Form -->
  <form method="GET" action="/admin/history" class="row g-3 mb-4">
    <div class="col-md-3">
      <label class="form-label">User</label>
      <input type="text" name="user" value="<?php echo htmlspecialchars($_GET['user'] ?? ''); ?>" class="form-control" placeholder="Username or Email">
    </div>
    <div class="col-md-3">
      <label class="form-label">IP Address</label>
      <input type="text" name="ip" value="<?php echo htmlspecialchars($_GET['ip'] ?? ''); ?>" class="form-control" placeholder="e.g. 8.8.8.8">
    </div>
    <div class="col-md-3">
      <label class="form-label">Start Date</label>
      <input type="date" name="start" value="<?php echo htmlspecialchars($_GET['start'] ?? ''); ?>" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">End Date</label>
      <input type="date" name="end" value="<?php echo htmlspecialchars($_GET['end'] ?? ''); ?>" class="form-control">
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>
    
  </form>
  <form method="GET" action="/admin/export" class="mb-4">
    <button type="submit" class="btn btn-success">
        Export All Scans as CSV
    </button>
</form>


  <!-- Results Table -->
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>User</th>
        <th>IP</th>
        <th>Ports</th>
        <th>Open Ports</th>
        <th>Timestamp</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($allScans)): ?>
        <?php foreach ($allScans as $scan): ?>
          <?php
          // Decode and format results
          $resultsData = json_decode($scan['results'], true);
          $openPortsStr = is_array($resultsData['open'] ?? null)
            ? implode(',', $resultsData['open'])
            : $scan['results'];
          ?>
          <tr>
            <td><?php echo htmlspecialchars($scan['id']); ?></td>
            <td><?php echo htmlspecialchars($scan['username']); ?></td>
            <td><?php echo htmlspecialchars($scan['ip_address']); ?></td>
            <td><?php echo htmlspecialchars($scan['ports']); ?></td>
            <td><?php echo htmlspecialchars($openPortsStr); ?></td>
            <td><?php echo htmlspecialchars($scan['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6">No scans found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</body>
</html>
