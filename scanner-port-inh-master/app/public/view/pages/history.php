<!-- app/public/view/pages/history.php -->
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>My Scan History</title>
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  >
</head>
<body class="p-3">
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="/scan">Port Scanner</a>
    <div>
      <a href="/scan" class="btn btn-outline-primary">Scan</a>
      <a href="/history" class="btn btn-outline-secondary">History</a>
      <a href="/logout" class="btn btn-danger">Logout</a>
    </div>
  </div>
</nav>
<div class="container">
  <h1>My Scan History</h1>

  <!-- Filter Form -->
  
</div>
  <form method="GET" action="/history" class="row g-3 mb-4">
    <div class="col-md-3">
      <label class="form-label">IP Address</label>
      <input type="text" name="ip" value="<?php echo htmlspecialchars($_GET['ip'] ?? ''); ?>"
             class="form-control" placeholder="e.g. 8.8.8.8">
    </div>
    <div class="col-md-3">
      <label class="form-label">Start Date</label>
      <input type="date" name="start" value="<?php echo htmlspecialchars($_GET['start'] ?? ''); ?>"
             class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">End Date</label>
      <input type="date" name="end" value="<?php echo htmlspecialchars($_GET['end'] ?? ''); ?>"
             class="form-control">
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">
        Filter
      </button>
    </div>
  </form>

  <!-- Results Table -->
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>IP</th>
        <th>Ports</th>
        <th>Open Ports</th>
        <th>Timestamp</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!empty($scans)): ?>
      <?php foreach ($scans as $scan): ?>
        <?php
          // If results is JSON, parse it
          $resultsData = json_decode($scan['results'], true);
          // e.g. { "open": [80,443] } or ["80","443"] if you stored an array
          $openPortsStr = '';
          if (is_array($resultsData) && !empty($resultsData['open'])) {
            $openPortsStr = implode(',', $resultsData['open']);
          } elseif (is_array($resultsData)) {
            // If the entire array is open ports
            $openPortsStr = implode(',', $resultsData);
          } else {
            // fallback: just show results raw
            $openPortsStr = $scan['results'];
          }
        ?>
        <tr>
          <td><?php echo $scan['id']; ?></td>
          <td><?php echo htmlspecialchars($scan['ip_address']); ?></td>
          <td><?php echo htmlspecialchars($scan['ports']); ?></td>
          <td><?php echo htmlspecialchars($openPortsStr); ?></td>
          <td><?php echo $scan['created_at']; ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="5">No scans found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
<form method="GET" action="/export" class="mb-4">
    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
    <button type="submit" class="btn btn-success">
        Export as CSV
    </button>
</form>
<div class="col-12 mt-3">
    <a href="/scan" class="btn btn-secondary">
        Back to Scan
    </a>
</div>

</body>
</html>
