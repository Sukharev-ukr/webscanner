<!-- app/public/view/pages/scan.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Ajax Port Scanner</title>
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    >
</head>
<body class="p-3">
<div class="container">
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="/">Port Scanner</a>
    <div>
      <a href="/scan" class="btn btn-outline-primary">Scan</a>
      <a href="/history" class="btn btn-outline-secondary">History</a>
      <a href="/logout" class="btn btn-danger">Logout</a>
    </div>
  </div>
</nav>
    <h1>Port Scanner (AJAX)</h1>

    <!-- IP and Ports Input Form (no direct action, we'll handle AJAX in JS) -->
    <form id="scanForm" class="row g-3" onsubmit="return false;">
        <div class="col-md-6">
            <label for="ip" class="form-label">IP Address</label>
            <input
                type="text"
                class="form-control"
                id="ip"
                name="ip"
                placeholder="e.g., 8.8.8.8"
                required
            >
        </div>

        <div class="col-md-6">
            <label for="ports" class="form-label">Ports (comma-separated or range)</label>
            <input
                type="text"
                class="form-control"
                id="ports"
                name="ports"
                placeholder="e.g. 80,443 or 1-100"
                required
            >
        </div>

        <div class="col-12">
            <button id="scanBtn" type="submit" class="btn btn-primary">
                Scan
            </button>
        </div>
        <div class="col-12 mt-3">
    <a href="/history" class="btn btn-secondary">
        View History
    </a>
</div>
    </form>

    <!-- Output Section -->
    <div id="results" class="mt-4">
        <!-- We'll insert open ports here via JavaScript -->
    </div>
</div>

<script>
    const scanForm = document.getElementById('scanForm');
    const resultsDiv = document.getElementById('results');

    scanForm.addEventListener('submit', async () => {
        // Gather form data
        const ip = document.getElementById('ip').value.trim();
        const ports = document.getElementById('ports').value.trim();

        // Basic validation
        if (!ip || !ports) {
            alert('Please enter IP and ports');
            return;
        }

        try {
            // Send POST request to /scan, expecting JSON response
            const response = await fetch('/scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ip, ports })
            });

            if (!response.ok) {
                throw new Error('Network response was not OK');
            }

            // The JSON should contain something like: { openPorts: [80,443], error: "" }
            const data = await response.json();

            // If there's an error message, show it
            if (data.error) {
                resultsDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }

            // Otherwise, list open ports
            if (data.openPorts && data.openPorts.length > 0) {
                const portsList = data.openPorts.join(', ');
                resultsDiv.innerHTML = `
            <div class="alert alert-success">
              <strong>Open Ports:</strong> ${portsList}
            </div>
          `;
            } else {
                // No open ports found
                resultsDiv.innerHTML = `
            <div class="alert alert-info">
              No open ports found.
            </div>
          `;
            }
        } catch (err) {
            console.error(err);
            resultsDiv.innerHTML = `
          <div class="alert alert-danger">
            Error scanning ports: ${err.message}
          </div>
        `;
        }
    });
</script>
</body>
</html>
