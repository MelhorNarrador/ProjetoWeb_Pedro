<?php
// Painel de administração, acesso restrito a admins
require_once "../../Backend/Utils/init.php";
startSecureSession();

if (empty($_SESSION["user_id"])) {
  header("Location: login.html");
  exit;
}

if ($_SESSION["user_role"] !== "admin") {
  header("Location: dashboard.php");
  exit;
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Panel - FloraIQ</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../Assets/Css/tokens.css" />
  <link rel="stylesheet" href="../Assets/Css/admin.css" />
</head>

<body data-user-id="<?= (int)$_SESSION['user_id'] ?>">

  <nav id="navbar">
    <div class="nav-brand">
      <img src="../Assets/Img/FloralQ_Logo_Dark.svg" alt="FloraIQ" id="nav-logo" />
      <span class="admin-badge">Admin</span>
    </div>
    <button id="btn-logout" class="btn-logout">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
        <polyline points="16 17 21 12 16 7" />
        <line x1="21" y1="12" x2="9" y2="12" />
      </svg>
      Logout
    </button>
  </nav>

  <main>
    <div class="admin-header">
      <h1>Backoffice</h1>
      <p>Manage users, devices, and plants across the platform.</p>
    </div>

    <!-- Tabs -->
    <div class="admin-tabs">
      <button class="admin-tab active" data-tab="users">Users</button>
      <button class="admin-tab" data-tab="devices">Devices</button>
      <button class="admin-tab" data-tab="plants">Plants</button>
    </div>

    <!-- Tab: Users -->
    <div id="tab-users" class="tab-content">
      <input class="search-bar" id="search-users" type="search" placeholder="Search by name or email…" />
      <div class="admin-table-wrap">
        <table id="table-users">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Joined</th>
              <th>Plants</th>
              <th>Devices</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="7" class="table-state">Loading…</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tab: Devices -->
    <div id="tab-devices" class="tab-content hidden">
      <input class="search-bar" id="search-devices" type="search" placeholder="Search by device code or owner…" />
      <div class="admin-table-wrap">
        <table id="table-devices">
          <thead>
            <tr>
              <th>Device Code</th>
              <th>Owner</th>
              <th>Activation Code</th>
              <th>Last Reading</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="6" class="table-state">Loading…</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tab: Plants -->
    <div id="tab-plants" class="tab-content hidden">
      <input class="search-bar" id="search-plants" type="search" placeholder="Search by name, type or owner…" />
      <div class="admin-table-wrap">
        <table id="table-plants">
          <thead>
            <tr>
              <th>Plant Name</th>
              <th>Type</th>
              <th>Owner</th>
              <th>Device</th>
              <th>Moisture</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="7" class="table-state">Loading…</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script type="module" src="../Assets/Js/admin/admin.js"></script>
</body>

</html>