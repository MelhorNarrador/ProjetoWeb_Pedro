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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.0/dist/driver.css" />
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
          <svg viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
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
        <img class="card-image" alt="" />
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

  <!-- card de detalhes da planta -->
  <div id="plant-modal-overlay" class="hidden">
    <div class="modal">
      <div id="plant-modal-body">
        <div class="plant-detail-header">
          <div class="plant-detail-icon">
            <svg
              class="plant-detail-icon-svg"
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
            <img class="plant-detail-icon-img hidden" alt="" />
          </div>
          <div class="plant-detail-title">
            <h2 class="modal-plant-name"></h2>
            <p class="plant-detail-subtitle">
              <span class="modal-location"></span> · <span class="modal-device"></span>
            </p>
          </div>
          <div class="detail-actions">
            <button class="btn-icon" id="edit-plant-btn" aria-label="Edit">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
              </svg>
            </button>
            <button class="btn-icon btn-icon-danger" id="delete-plant-btn" aria-label="Delete">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6" />
                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                <line x1="10" y1="11" x2="10" y2="17" />
                <line x1="14" y1="11" x2="14" y2="17" />
              </svg>
            </button>
            <button class="btn-icon modal-close" data-target="plant-modal-overlay" aria-label="Close">✕</button>
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

        <div class="modal-history hidden">
          <div class="card-tabs">
            <button class="modal-tab card-tab active" data-range="24h">24h</button>
            <button class="modal-tab card-tab" data-range="week">Week</button>
            <button class="modal-tab card-tab" data-range="month">Month</button>
            <button class="modal-tab card-tab" data-range="year">Year</button>
          </div>
          <div class="card-line-chart">
            <canvas class="modal-line-chart"></canvas>
          </div>
        </div>
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
      <p class="modal-desc">Associate a new plant with one of your devices.</p>
      <form id="add-plant-form">
        <div class="input-group">
          <label>Plant Image (optional)</label>
          <div class="image-picker">
            <img id="plant-image-preview" class="image-preview hidden" alt="Preview" />
            <label class="image-picker-btn" for="plant-image-file">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" />
                <circle cx="8.5" cy="8.5" r="1.5" />
                <path d="m21 15-5-5L5 21" />
              </svg>
              <span id="plant-image-label">Choose image</span>
            </label>
            <button type="button" id="plant-image-remove" class="image-remove-btn hidden">Remove</button>
            <input type="file" id="plant-image-file" accept="image/jpeg,image/png,image/webp" hidden />
          </div>
        </div>
        <div class="input-group">
          <label>Plant Name</label>
          <input type="text" id="plant-name" maxlength="30" required />
        </div>
        <div class="input-group">
          <label>Location</label>
          <input type="text" id="plant-location" maxlength="50" required />
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
  <!-- Modal de confirmação -->
  <div id="confirm-modal-overlay" class="hidden">
    <div class="modal modal-confirm">
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
          <h2 id="confirm-plant-name"></h2>
          <p class="plant-detail-subtitle" id="confirm-plant-subtitle"></p>
        </div>
        <button class="modal-close" data-target="confirm-modal-overlay" style="margin-left:auto">✕</button>
      </div>

      <div class="confirm-warning-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="12" />
          <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
      </div>

      <h3 class="confirm-title" id="confirm-title">Remove Plant?</h3>
      <p class="confirm-text" id="confirm-text">This action is irreversible. The plant and all reading history will be erased.</p>

      <div class="confirm-actions">
        <button class="submit-btn btn-danger" id="confirm-yes-btn">Yes, Remove</button>
        <button class="submit-btn btn-cancel" data-target="confirm-modal-overlay">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Welcome / Onboarding modal -->
  <div id="welcome-modal-overlay" class="hidden">
    <div class="modal modal-confirm">
      <div class="plant-detail-header">
        <div class="plant-detail-icon">
          <img src="../Assets/Img/FloralQ_Icon.svg" alt="FloralQ Icon" />
        </div>
        <div class="plant-detail-title">
          <h2>Welcome to FloralQ</h2>
          <p class="plant-detail-subtitle">Let's take a quick tour of how it works.</p>
        </div>
      </div>

      <p class="confirm-text" style="margin-top:16px">
        We'll walk you through redeeming your sensor, adding your first plant, and configuring alerts.
        It only takes a minute.
      </p>

      <div class="confirm-actions">
        <button class="submit-btn" id="welcome-start-btn">Start Tour</button>
        <button class="submit-btn btn-cancel" id="welcome-skip-btn">Skip</button>
      </div>
    </div>
  </div>

  <!-- Account modal -->
  <div id="account-modal-overlay" class="hidden">
    <div class="modal">
      <div class="plant-detail-header">
        <div class="plant-detail-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
        </div>
        <div class="plant-detail-title">
          <h2>Account</h2>
          <p class="plant-detail-subtitle">Manage your account settings.</p>
        </div>
        <button class="modal-close" data-target="account-modal-overlay" style="margin-left:auto">✕</button>
      </div>

      <div class="account-rows">
        <!-- Email -->
        <div class="account-row">
          <div class="account-row-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
              <polyline points="22,6 12,13 2,6" />
            </svg>
          </div>
          <div class="account-row-text">
            <strong>Email</strong>
            <p class="account-email" id="account-email">--</p>
            <span class="account-badge" id="account-email-status"></span>
          </div>
          <button class="submit-btn" id="confirm-email-btn">Confirm Email</button>
        </div>

        <!-- Password -->
        <div class="account-row">
          <div class="account-row-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
              <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
          </div>
          <div class="account-row-text">
            <strong>Password</strong>
            <p class="account-email">Change your login password</p>
          </div>
          <button class="submit-btn btn-outline" id="open-change-password">Change Password</button>
        </div>

        <!-- Alerts -->
        <div class="account-row">
          <div class="account-row-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
              <path d="M13.73 21a2 2 0 01-3.46 0" />
            </svg>
          </div>
          <div class="account-row-text">
            <strong>Alerts</strong>
            <p class="account-email">
              Alert when humidity drops to
              <select id="alert-threshold" class="alert-threshold-select">
                <option value="5">5%</option>
                <option value="10">10%</option>
                <option value="15">15%</option>
                <option value="20">20%</option>
                <option value="25">25%</option>
                <option value="30">30%</option>
                <option value="40">40%</option>
                <option value="50">50%</option>
              </select>
            </p>
            <label class="alert-email-toggle">
              <input type="checkbox" id="alert-email-enabled" />
              Also send email alerts
            </label>
          </div>
        </div>

        <!-- Presentation Mode -->
        <div class="account-section-header">
          <strong>Presentation Mode</strong>
          <p class="account-section-desc">Choose where to display the history charts.</p>
        </div>
        <div class="account-row chart-position-option" data-value="card">
          <div class="account-row-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="5" width="18" height="14" rx="2" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
          </div>
          <div class="account-row-text">
            <strong>Card</strong>
            <p class="account-email">Show the history chart inside each plant card.</p>
          </div>
        </div>
        <div class="account-row chart-position-option" data-value="modal">
          <div class="account-row-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="16" rx="2" />
              <line x1="3" y1="9" x2="21" y2="9" />
              <line x1="9" y1="21" x2="15" y2="21" />
            </svg>
          </div>
          <div class="account-row-text">
            <strong>Modal</strong>
            <p class="account-email">Show the chart only when you click a plant.</p>
          </div>
        </div>

        <!-- Replay tutorial -->
        <div class="account-row">
          <div class="account-row-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10" />
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
              <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
          </div>
          <div class="account-row-text">
            <strong>Tutorial</strong>
            <p class="account-email">Replay the onboarding tour.</p>
          </div>
          <button class="submit-btn btn-outline" id="replay-tutorial-btn">Replay</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Change Password modal -->
  <div id="change-password-modal-overlay" class="hidden">
    <div class="modal">
      <button class="modal-close" data-target="change-password-modal-overlay">✕</button>
      <h2>Change Password</h2>
      <p class="modal-desc">Enter your current password and your new password.</p>
      <form id="change-password-form">
        <div class="input-group">
          <label>Current Password</label>
          <input type="password" id="current-password" required />
        </div>
        <div class="input-group">
          <label>New Password</label>
          <input type="password" id="new-password" required minlength="6" />
        </div>
        <div class="input-group">
          <label>Repeat New Password</label>
          <input type="password" id="repeat-new-password" required minlength="6" />
        </div>
        <p id="change-password-error" class="error-msg"></p>
        <button type="submit" class="submit-btn">Save Password</button>
      </form>
    </div>
  </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script type="module" src="../Assets/Js/dashboard/dashboard.js"></script>
</body>

</html>