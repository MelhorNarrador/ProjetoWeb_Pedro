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
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet" />

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - FloraIQ</title>
  <link rel="stylesheet" href="../Assets/Css/dashboard.css" />
</head>

<body>
  <!-- Navbar -->
  <nav id="navbar">
    <div class="nav-brand">
      <img src="../Assets/Img/FloralQ_Logo_Dark.svg" alt="FloralQ Logo" id="nav-logo">
    </div>

    <div id="nav-links">
      <button class="nav-btn nav-btn-accent" id="open-add-plant">
        + Add Plant
      </button>
      <div class="settings-wrapper">
        <button class="nav-btn nav-btn-icon" id="settings-btn" aria-label="Settings">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3" />
            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z" />
          </svg>
        </button>

        <div class="settings-dropdown hidden" id="settings-dropdown">
          <button class="settings-item" id="open-redeem">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
              <polyline points="7 10 12 15 17 10" />
              <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Redeem Device
          </button>
          <button class="settings-item" id="open-account">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            Account
          </button>
          <button class="settings-item" id="theme-toggle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
            </svg>
            Dark Mode
          </button>
          <div class="settings-divider"></div>
          <button class="settings-item settings-item-danger" id="logout-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
              <polyline points="16 17 21 12 16 7" />
              <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
            Logout
          </button>
        </div>
      </div>
  </nav>
  <!-- Main content -->
  <main>
    <div class="page-header">
      <h1>My Plants</h1>
      <p class="page-subtitle" id="plants-subtitle">Loading...</p>
    </div>
    <div class="stats-bar">
      <div class="stat-card">
        <div class="stat-icon stat-icon-green">
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <path d="M14 9.536V7a4 4 0 0 1 4-4h1.5a.5.5 0 0 1 .5.5V5a4 4 0 0 1-4 4 4 4 0 0 0-4 4c0 2 1 3 1 5a5 5 0 0 1-1 3" />
            <path d="M4 9a5 5 0 0 1 8 4 5 5 0 0 1-8-4" />
            <path d="M5 21h14" />
          </svg>
        </div>
        <div class="stat-info">
          <div class="stat-value" id="stat-total">0</div>
          <div class="stat-label">Total number of plants</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-icon-green">
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <path d="m14.479 19.374-.971.939a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5a5.2 5.2 0 0 1-.219 1.49" />
            <path d="M15 15h6" />
            <path d="M18 12v6" />
          </svg>
        </div>
        <div class="stat-info">
          <div class="stat-value stat-value-green" id="stat-healthy">0</div>
          <div class="stat-label">Healthy plants</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-icon-red">
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3" />
            <path d="M12 9v4" />
            <path d="M12 17h.01" />
          </svg>
        </div>
        <div class="stat-info">
          <div class="stat-value stat-value-red" id="stat-dry">0</div>
          <div class="stat-label">In need of water</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-icon-blue">
          <img src="../Assets/Img/overwatered_symbol.svg" alt="Overwatered" />
        </div>
        <div class="stat-info">
          <div class="stat-value stat-value-blue" id="stat-overwatered">0</div>
          <div class="stat-label">Overwatered</div>
        </div>
      </div>
    </div>
    <div id="plants-grid"></div>
  </main>

  <!-- template do card de planta -->
  <template id="plant-card-template">
    <div class="plant-card">
      <div class="card-top">
        <div class="chart-wrap">
          <canvas></canvas>
        </div>
        <div class="card-info">
          <div class="card-name"></div>
          <div class="card-status"></div>
          <div class="card-prediction">Loading prediction...</div>
          <div class="card-meta">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <circle cx="12" cy="12" r="10" />
              <path d="M12 6v6l4 2" />
            </svg>
            <span class="card-meta-text">--</span>
          </div>
        </div>
      </div>

      <div class="card-history">
        <div class="card-tabs">
          <button class="card-tab active" data-range="24h">24h</button>
          <button class="card-tab" data-range="week">Week</button>
          <button class="card-tab" data-range="month">Month</button>
          <button class="card-tab" data-range="year">Year</button>
        </div>
        <div class="card-line-chart">
          <canvas class="line-chart"></canvas>
        </div>
      </div>
    </div>
  </template>
  <div class="card-tabs">

    <!-- card de detalhes da planta -->
    <div id="plant-modal-overlay" class="hidden">
      <div class="modal">
        <button class="modal-close" data-target="plant-modal-overlay">✕</button>
        <div id="plant-modal-body">
          <div class="plant-detail-header">
            <div class="plant-detail-icon">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M14 9.536V7a4 4 0 0 1 4-4h1.5a.5.5 0 0 1 .5.5V5a4 4 0 0 1-4 4 4 4 0 0 0-4 4c0 2 1 3 1 5a5 5 0 0 1-1 3" />
                <path d="M4 9a5 5 0 0 1 8 4 5 5 0 0 1-8-4" />
                <path d="M5 21h14" />
              </svg>
            </div>
            <div class="plant-detail-title">
              <h2 class="modal-plant-name"></h2>
              <p class="plant-detail-subtitle">
                <span class="modal-location"></span> · <span class="modal-device"></span>
              </p>
            </div>
          </div>

          <div class="detail-grid">
            <div class="detail-item">
              <div class="detail-label">Current moisture</div>
              <div class="detail-value modal-moisture">--</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">State</div>
              <div class="detail-value modal-stage">--</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Prediction</div>
              <div class="detail-value modal-prediction">--</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Dry at</div>
              <div class="detail-value modal-dry-at">--</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Min moisture</div>
              <div class="detail-value modal-min-moisture">--</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Drying rate</div>
              <div class="detail-value modal-trend">--</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Confidence</div>
              <div class="detail-value modal-confidence">--</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Growth</div>
              <div class="detail-value modal-growth">--</div>
            </div>
          </div>

          <div class="modal-map"></div>
        </div>

        <!--adicionar planta-->
        <div id="add-plant-modal-overlay" class="hidden">
          <div class="modal">
            <button class="modal-close" data-target="add-plant-modal-overlay">
              ✕
            </button>
            <h2>Add Plant</h2>
            <p class="modal-desc">Associate a new plant with one of your devices.</p>
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
            <p class="modal-desc">Enter the activation code that came with your sensor.</p>
            <form id="redeem-form">
              <div class="input-group">
                <label>Activation Code</label>
                <input
                  type="text"
                  id="activation-code"
                  placeholder="Ex: 9F9573B0"
                  required />
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