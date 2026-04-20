<?php
// Verificar se o utilizador está autenticado
session_start();

if (empty($_SESSION["user_id"])) {
    // Redirecionar para login se não autenticado
    header("Location: login.html");
    exit;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - FloraIQ</title>
    <link rel="stylesheet" href="../Assets/Css/dashboard.css" />
  </head>
  <body>
    <nav id="navbar">
      <img
        src="../Assets/Img/FloralQ_Logo.png"
        alt="FloraIQ Logo"
        id="nav-logo"
      />
      <div id="nav-links">
        <button class="nav-btn" id="open-add-plant">+ Add Plant</button>
        <button class="nav-btn" id="open-redeem">Redeem Device</button>
        <button class="nav-btn" id="logout-btn">Logout</button>
      </div>
    </nav>

    <main>
      <h1>My Plants</h1>
      <div id="plants-grid"></div>
    </main>

    <!-- template do card de planta -->
    <template id="plant-card-template">
      <div class="plant-card">
        <div class="chart-container">
          <canvas></canvas>
        </div>
        <h3 class="card-name"></h3>
        <p class="card-status"></p>
        <p class="card-prediction">Loading prediction...</p>
      </div>
    </template>

    <!-- card de detalhes da planta -->
    <div id="plant-modal-overlay" class="hidden">
      <div class="modal">
        <button class="modal-close" data-target="plant-modal-overlay">✕</button>
        <div id="plant-modal-body">
          <h2 class="modal-plant-name"></h2>
          <p>Location: <span class="modal-location"></span></p>
          <p>Status: <span class="modal-stage"></span></p>
          <p>Device: <span class="modal-device"></span></p>
          <p>Current moisture: <span class="modal-moisture"></span></p>
          <p>Prediction: <span class="modal-prediction">--</span></p>
          <p>Min moisture: <span class="modal-min-moisture">--</span></p>
          <p>Runs dry at: <span class="modal-dry-at">--</span></p>
          <p>Drying rate: <span class="modal-trend">--</span></p>
          <p>Prediction confidence: <span class="modal-confidence">--</span></p>
          <div class="modal-map"></div>
        </div>
      </div>
    </div>

    <!--adicionar planta-->
    <div id="add-plant-modal-overlay" class="hidden">
      <div class="modal">
        <button class="modal-close" data-target="add-plant-modal-overlay">
          ✕
        </button>
        <h2>Add Plant</h2>
        <form id="add-plant-form">
          <div class="input-group">
            <label>Plant Name</label>
            <input type="text" id="plant-name" required />
          </div>
          <div class="input-group">
            <label>Location</label>
            <input type="text" id="plant-location" required />
          </div>
          <div class="input-group">
            <label>Plant Type</label>
            <select id="plant-type"></select>
          </div>
          <div class="input-group">
            <label>Device</label>
            <select id="plant-device"></select>
          </div>
          <div class="input-group checkbox-group">
            <label>
              <input type="checkbox" id="plant-is-grown" />
              Plant is already grown
            </label>
          </div>
          <p id="add-plant-error" class="error-msg"></p>
          <button type="submit" class="submit-btn">Create Plant</button>
        </form>
      </div>
    </div>

    <!--redeem de dispositivo-->
    <div id="redeem-modal-overlay" class="hidden">
      <div class="modal">
        <button class="modal-close" data-target="redeem-modal-overlay">
          ✕
        </button>
        <h2>Redeem Device</h2>
        <form id="redeem-form">
          <div class="input-group">
            <label>Activation Code</label>
            <input
              type="text"
              id="activation-code"
              placeholder="Ex: 9F9573B0"
              required
            />
          </div>
          <p id="redeem-error" class="error-msg"></p>
          <button type="submit" class="submit-btn">Redeem</button>
        </form>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../Assets/Js/secrets.js"></script>
    <script type="module" src="../Assets/Js/dashboard/dashboard.js"></script>
  </body>
</html>
