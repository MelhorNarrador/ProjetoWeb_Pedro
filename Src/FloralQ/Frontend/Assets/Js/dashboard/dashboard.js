import * as api from "../apiClient.js";
import { showToast } from "../toast.js";
import { openModal, closeModal, setupModalClosers } from "./modalManager.js";
import { buildPlantCard } from "./plantCard.js";
import { drawMoistureChart, loadHistoryChart } from "./chartController.js";
import { maybeStartOnboarding, replayOnboarding } from "./onboarding.js";
import {
  getPredictionLabel,
  getMoistureStatus,
  getNormalizedMoisture,
  getMoistureColor,
} from "./utils/moisture.js";

// Cores do nível de confiança da previsão
const CONFIDENCE_COLORS = {
  high: "#2D6E3E",
  medium: "#E0A555",
  low: "#E05555",
};

// Cache em memória
let plantsCache = [];

// Estado da modal Add/Edit Plant
let editingPlantId = null;
let selectedPlantImageFile = null;
let removeImagePending = false;

// Base path para servir imagens de plantas (relativo a Frontend/Pages/)
const PLANT_IMAGE_BASE = "../Assets/Uploads/";

function resetImagePicker() {
  selectedPlantImageFile = null;
  removeImagePending = false;
  const input = document.getElementById("plant-image-file");
  if (input) input.value = "";
  const preview = document.getElementById("plant-image-preview");
  if (preview) preview.classList.add("hidden");
  const label = document.getElementById("plant-image-label");
  if (label) label.textContent = "Choose image";
  const removeBtn = document.getElementById("plant-image-remove");
  if (removeBtn) removeBtn.classList.add("hidden");
}

// Alertas de humidade
let userAlertThreshold = 30;

// Posição do gráfico de histórico: "card" ou "modal"
let userChartPosition = "card";

// Plantas conhecidas sem leituras, para detetar a chegada da primeira
const plantsWithoutReadings = new Set();

function checkFirstReadings(plants) {
  plants.forEach((plant) => {
    const hasReading = plant.sensor_reading_moisture_percent !== null;
    if (!hasReading) {
      plantsWithoutReadings.add(plant.plant_id);
    } else if (plantsWithoutReadings.has(plant.plant_id)) {
      // Transição: planta que não tinha leituras agora tem
      showToast(`First reading received for ${plant.plant_name}!`, "success");
      plantsWithoutReadings.delete(plant.plant_id);
    }
  });
}

function checkPlantAlerts(plants) {
  console.log(
    "[ALERTS] threshold:",
    userAlertThreshold,
    "plants:",
    plants.length,
  );
  plants.forEach((plant) => {
    const moisture = plant.sensor_reading_moisture_percent;
    if (moisture === null || moisture === undefined) return;

    const normalized = getNormalizedMoisture(
      moisture,
      plant.plant_type_min_moisture,
      plant.plant_type_max_moisture,
    );
    console.log("[ALERTS]", plant.plant_name, "normalized:", normalized);
    if (normalized === null) return;

    if (normalized <= userAlertThreshold) {
      showToast(`${plant.plant_name} needs water (${normalized}%)`, "error");
    }
  });
}

// Carrega plant_types + devices disponíveis para os dropdowns da modal
async function populateAddPlantDropdowns() {
  const [typesData, devicesData] = await Promise.all([
    api.getPlantTypes().catch(() => null),
    api.getUserDevices().catch(() => null),
  ]);

  const typeSelect = document.getElementById("plant-type");
  const deviceSelect = document.getElementById("plant-device");
  typeSelect.innerHTML = "";
  deviceSelect.innerHTML = "";

  typesData?.plant_types?.forEach((t) => {
    typeSelect.appendChild(
      Object.assign(document.createElement("option"), {
        value: t.plant_type_id,
        textContent: t.plant_type_name,
      }),
    );
  });

  devicesData?.devices?.forEach((d) => {
    deviceSelect.appendChild(
      Object.assign(document.createElement("option"), {
        value: d.device_id,
        textContent: d.device_code,
      }),
    );
  });
}

// Configura a modal Add Plant para modo "add" ou "edit"
function setAddPlantMode(mode, plant = null) {
  const modal = document.getElementById("add-plant-modal-overlay");
  const title = modal.querySelector("h2");
  const desc = modal.querySelector(".modal-desc");
  const submitBtn = modal.querySelector(".submit-btn");
  const deviceSelect = document.getElementById("plant-device");

  if (mode === "edit" && plant) {
    editingPlantId = plant.plant_id;
    title.textContent = "Edit Plant";
    desc.textContent = "Update plant details.";
    submitBtn.textContent = "Save Changes";

    // Device não pode ser alterado: mostra só o atual, disabled
    deviceSelect.innerHTML = "";
    deviceSelect.appendChild(
      Object.assign(document.createElement("option"), {
        value: plant.device_id,
        textContent: plant.device_code,
      }),
    );
    deviceSelect.disabled = true;

    // Pré-preencher campos
    document.getElementById("plant-name").value = plant.plant_name;
    document.getElementById("plant-location").value =
      plant.plant_location_label ?? "";
    document.getElementById("plant-type").value = plant.plant_type_id;
    document.getElementById("plant-is-grown").checked = !!plant.plant_is_grown;
  } else {
    editingPlantId = null;
    title.textContent = "Add Plant";
    desc.textContent = "Associate a new plant with one of your devices.";
    submitBtn.textContent = "Create Plant";
    deviceSelect.disabled = false;

    // Limpar campos para começar em branco
    document.getElementById("plant-name").value = "";
    document.getElementById("plant-location").value = "";
    document.getElementById("plant-is-grown").checked = false;
    document.getElementById("add-plant-error").textContent = "";
    resetImagePicker();
  }

  // Em modo edit, mostrar imagem atual da planta (se existir)
  if (mode === "edit" && plant) {
    resetImagePicker();
    const preview = document.getElementById("plant-image-preview");
    const label = document.getElementById("plant-image-label");
    if (plant.plant_image_path) {
      preview.src = PLANT_IMAGE_BASE + plant.plant_image_path;
      preview.classList.remove("hidden");
      label.textContent = "Change image";
      document.getElementById("plant-image-remove").classList.remove("hidden");
    }
  }
}

// Listener do file input, atualiza preview e guarda o ficheiro selecionado
document.getElementById("plant-image-file").addEventListener("change", (e) => {
  const file = e.target.files[0];
  if (!file) {
    resetImagePicker();
    return;
  }

  // Validação local: tamanho
  if (file.size > 5 * 1024 * 1024) {
    showToast("Image must be 5MB or less", "error");
    e.target.value = "";
    return;
  }

  selectedPlantImageFile = file;
  removeImagePending = false; // user mudou de ideias depois de Remove
  const preview = document.getElementById("plant-image-preview");
  preview.src = URL.createObjectURL(file);
  preview.classList.remove("hidden");
  document.getElementById("plant-image-label").textContent = "Change image";
  document.getElementById("plant-image-remove").classList.add("hidden");
});

// Click no Remove: marca a imagem para ser apagada no submit
document
  .getElementById("plant-image-remove")
  .addEventListener("click", () => {
    removeImagePending = true;
    selectedPlantImageFile = null;
    document.getElementById("plant-image-file").value = "";
    document.getElementById("plant-image-preview").classList.add("hidden");
    document.getElementById("plant-image-label").textContent = "Choose image";
    document.getElementById("plant-image-remove").classList.add("hidden");
  });

// Grid de plantas
async function loadPlants() {
  const grid = document.getElementById("plants-grid");
  const data = await api.getPlants().catch(() => null);

  grid.innerHTML = "";

  if (!data?.success || data.plants.length === 0) {
    grid.innerHTML =
      "<p style='color:#888'>No plants yet. Add your first plant!</p>";
    document.getElementById("plants-subtitle").textContent = "No plants yet";

    document.getElementById("stat-total").textContent = "0";
    document.getElementById("stat-healthy").textContent = "0";
    document.getElementById("stat-dry").textContent = "0";
    document.getElementById("stat-overwatered").textContent = "0";

    plantsCache = [];
    return;
  }

  // Subtítulo
  const total = data.plants.length;
  const online = data.plants.filter((p) => p.sensor_status === "online").length;

  document.getElementById("plants-subtitle").textContent =
    `${total} plant${total !== 1 ? "s" : ""} monitored · ${online} sensor${online !== 1 ? "s" : ""} online`;

  plantsCache = data.plants;
  data.plants.forEach((plant) => {
    const card = buildPlantCard(plant, userChartPosition);
    grid.appendChild(card);
    drawMoistureChart(
      card.querySelector("canvas"),
      plant.sensor_reading_moisture_percent ?? "--",
      plant.plant_type_min_moisture,
      plant.plant_type_max_moisture,
    );
  });

  let healthy = 0,
    dry = 0,
    overwatered = 0;
  data.plants.forEach((plant) => {
    const status = getMoistureStatus(
      plant.sensor_reading_moisture_percent ?? "--",
      plant.plant_type_min_moisture,
      plant.plant_type_max_moisture,
    );
    if (status === "healthy") healthy++;
    else if (status === "dry") dry++;
    else if (status === "overwatered") overwatered++;
  });
  document.getElementById("stat-total").textContent = total;
  document.getElementById("stat-healthy").textContent = healthy;
  document.getElementById("stat-dry").textContent = dry;
  document.getElementById("stat-overwatered").textContent = overwatered;

  checkPlantAlerts(data.plants);
}
// Event delegation
document.getElementById("plants-grid").addEventListener("click", (e) => {
  if (e.target.closest(".card-tab")) return;

  const card = e.target.closest(".plant-card");
  if (!card) return;
  const plant = plantsCache.find((p) => p.plant_id == card.dataset.plantId);
  if (plant) openPlantModal(plant);
});

// Modal de detalhes da planta
async function openPlantModal(plant) {
  const body = document.getElementById("plant-modal-body");
  const moisture = plant.sensor_reading_moisture_percent ?? "--";

  body.querySelector(".modal-plant-name").textContent = plant.plant_name;
  body.querySelector(".modal-location").textContent =
    plant.plant_location_label;

  // Alterna entre SVG (default) e imagem do user
  const iconImg = body.querySelector(".plant-detail-icon-img");
  const iconSvg = body.querySelector(".plant-detail-icon-svg");
  if (plant.plant_image_path) {
    iconImg.src = PLANT_IMAGE_BASE + plant.plant_image_path;
    iconImg.classList.remove("hidden");
    iconSvg.style.display = "none";
  } else {
    iconImg.classList.add("hidden");
    iconSvg.style.display = "";
  }
  const status = getMoistureStatus(
    moisture,
    plant.plant_type_min_moisture,
    plant.plant_type_max_moisture,
  );
  body.querySelector(".modal-stage").textContent =
    status.charAt(0).toUpperCase() + status.slice(1);
  body.querySelector(".modal-growth").textContent = plant.plant_is_grown
    ? "Grown"
    : "Newborn";
  body.querySelector(".modal-min-moisture").textContent =
    `${plant.plant_type_min_moisture}%`;

  body.querySelector(".modal-device").textContent = plant.device_is_professional
    ? "FloraIQ Professional"
    : "FloraIQ Home";
  const normalized = getNormalizedMoisture(
    moisture,
    plant.plant_type_min_moisture,
    plant.plant_type_max_moisture,
  );
  const moistureEl = body.querySelector(".modal-moisture");
  moistureEl.textContent = normalized === null ? "--" : `${normalized}%`;
  moistureEl.style.color = getMoistureColor(
    moisture,
    plant.plant_type_min_moisture,
    plant.plant_type_max_moisture,
  );

  const predictionEl = body.querySelector(".modal-prediction");
  const minMoistureEl = body.querySelector(".modal-min-moisture");
  const dryAtEl = body.querySelector(".modal-dry-at");
  const trendEl = body.querySelector(".modal-trend");
  const confidenceEl = body.querySelector(".modal-confidence");
  const mapEl = body.querySelector(".modal-map");

  mapEl.innerHTML = "";

  openModal("plant-modal-overlay");

  // Previsão de secagem
  const predData = await api
    .getDryPrediction(plant.device_id)
    .catch(() => null);
  if (predData?.success && predData.data) {
    const d = predData.data;
    predictionEl.textContent = getPredictionLabel(d.prediction);
    if (d.prediction === "drying") {
      dryAtEl.textContent = d.dry_at;
      trendEl.textContent = `${d.trend_per_hour}% per hour`;
      confidenceEl.textContent = d.confidence.toUpperCase();
      confidenceEl.style.color = CONFIDENCE_COLORS[d.confidence] ?? "";
    }
  }

  // Mapa GPS
  if (plant.device_is_professional) {
    const locData = await api.getLocation(plant.device_code).catch(() => null);
    if (locData?.success && locData.data) {
      const { latitude, longitude } = locData.data;
      mapEl.innerHTML = `<iframe width="100%" height="200"
        style="border-radius:8px;border:0" loading="lazy"
        src="https://www.google.com/maps?q=${latitude},${longitude}&z=15&output=embed">
      </iframe>`;
    }
  }

  // Histórico (gráfico de linhas), só renderiza se modo for "modal"
  const historyEl = body.querySelector(".modal-history");
  if (userChartPosition === "modal") {
    historyEl.classList.remove("hidden");
    const lineCanvas = body.querySelector(".modal-line-chart");
    const min = plant.plant_type_min_moisture;
    const max = plant.plant_type_max_moisture;
    const color = getMoistureColor(moisture, min, max);

    // Reset tabs para 24h
    body.querySelectorAll(".modal-tab").forEach((t, i) => {
      t.classList.toggle("active", i === 0);
    });
    loadHistoryChart(lineCanvas, plant.device_code, "24h", color, min, max);

    // Bind tabs
    body.querySelectorAll(".modal-tab").forEach((tab) => {
      tab.onclick = () => {
        body
          .querySelectorAll(".modal-tab")
          .forEach((t) => t.classList.remove("active"));
        tab.classList.add("active");
        loadHistoryChart(
          lineCanvas,
          plant.device_code,
          tab.dataset.range,
          color,
          min,
          max,
        );
      };
    });
  } else {
    historyEl.classList.add("hidden");
  }

  // Edit plant: reutiliza a modal Add Plant em modo edit
  document.getElementById("edit-plant-btn").onclick = async () => {
    await populateAddPlantDropdowns();
    setAddPlantMode("edit", plant);
    closeModal("plant-modal-overlay");
    openModal("add-plant-modal-overlay");
  };

  // Delete plant, abre modal de confirmação
  document.getElementById("delete-plant-btn").onclick = () => {
    // Preencher o modal com info da planta
    document.getElementById("confirm-plant-name").textContent =
      plant.plant_name;
    document.getElementById("confirm-plant-subtitle").textContent =
      `${plant.plant_location_label} · ${plant.device_is_professional ? "FloraIQ Professional" : "FloraIQ Home"}`;

    // Quando carrega "Yes, Remove"
    document.getElementById("confirm-yes-btn").onclick = async () => {
      const res = await api.deletePlant(plant.plant_id).catch((err) => ({
        success: false,
        message: err.message,
      }));
      if (res?.success) {
        closeModal("confirm-modal-overlay");
        closeModal("plant-modal-overlay");
        showToast("Plant deleted", "success");
        loadPlants();
      } else {
        showToast(res?.message ?? "Could not delete plant", "error");
      }
    };

    openModal("confirm-modal-overlay");
  };
}

// Formulário adicionar planta
document
  .getElementById("open-add-plant")
  .addEventListener("click", async () => {
    await populateAddPlantDropdowns();
    setAddPlantMode("add");
    openModal("add-plant-modal-overlay");
  });

document
  .getElementById("add-plant-form")
  .addEventListener("submit", async (e) => {
    e.preventDefault();
    const errorMsg = document.getElementById("add-plant-error");
    errorMsg.textContent = "";

    const body = {
      plant_name: document.getElementById("plant-name").value,
      plant_location_label: document.getElementById("plant-location").value,
      plant_type_id: parseInt(document.getElementById("plant-type").value),
      plant_is_grown: document.getElementById("plant-is-grown").checked,
    };

    const onErr = (err) => ({ success: false, message: err.message });
    let data;
    if (editingPlantId) {
      body.plant_id = editingPlantId;
      if (removeImagePending) body.remove_image = true;
      data = await api.updatePlant(body).catch(onErr);
    } else {
      body.device_id = parseInt(document.getElementById("plant-device").value);
      data = await api.createPlant(body).catch(onErr);
    }

    if (data?.success) {
      const wasCreating = !editingPlantId;
      const plantId = editingPlantId ?? data.plant_id;

      // Upload da imagem se o user selecionou uma
      if (selectedPlantImageFile && plantId) {
        try {
          await api.uploadPlantImage(plantId, selectedPlantImageFile);
        } catch (err) {
          showToast(
            `Plant saved but image upload failed: ${err.message}`,
            "error",
          );
        }
      }

      closeModal("add-plant-modal-overlay");
      showToast(editingPlantId ? "Plant updated" : "Plant created", "success");
      if (wasCreating) {
        showToast(
          "Sensor measures every 5 min. Wait a bit for the first readings.",
          "info",
        );
        document.dispatchEvent(new CustomEvent("plant-created"));
      }
      loadPlants();
    } else {
      errorMsg.textContent = data?.message ?? "Something went wrong.";
    }
  });

// Formulário redeem de dispositivo
const openRedeemBtn = document.getElementById("open-redeem");
if (openRedeemBtn) {
  openRedeemBtn.addEventListener("click", () => {
    openModal("redeem-modal-overlay");
  });
}

document.getElementById("redeem-form").addEventListener("submit", async (e) => {
  e.preventDefault();
  const errorMsg = document.getElementById("redeem-error");
  errorMsg.textContent = "";
  const code = document.getElementById("activation-code").value;

  const data = await api.redeemDevice(code).catch((err) => ({
    success: false,
    message: err.message,
  }));
  if (data?.success) {
    closeModal("redeem-modal-overlay");
    showToast("Device redeemed successfully", "success");
    document.dispatchEvent(new CustomEvent("redeem-success"));
    loadPlants();
  } else {
    errorMsg.textContent = data?.message ?? "Something went wrong.";
  }
});

// Logout
const logoutBtn = document.getElementById("logout-btn");
if (logoutBtn) {
  logoutBtn.addEventListener("click", async () => {
    await api.logout();
    window.location.href = "login.html";
  });
}

// Account modal
function renderAccountEmailStatus(verified) {
  const statusEl = document.getElementById("account-email-status");
  const btn = document.getElementById("confirm-email-btn");
  if (verified) {
    statusEl.textContent = "Verified";
    statusEl.className = "account-badge account-badge-verified";
    btn.style.display = "none";
  } else {
    statusEl.textContent = "Not verified";
    statusEl.className = "account-badge account-badge-pending";
    btn.style.display = "";
  }
}

document.getElementById("open-account").addEventListener("click", async () => {
  document.getElementById("settings-dropdown").classList.add("hidden");

  // Estado de loading enquanto carrega
  document.getElementById("account-email").textContent = "Loading...";
  document.getElementById("account-email-status").textContent = "";
  openModal("account-modal-overlay");

  const data = await api.getUserInfo().catch(() => null);
  if (data?.success && data.data) {
    document.getElementById("account-email").textContent =
      data.data.user_account_email;
    renderAccountEmailStatus(!!data.data.user_account_email_verified);

    // Pré-preencher Alerts
    userAlertThreshold = data.data.user_account_alert_threshold ?? 30;
    document.getElementById("alert-threshold").value = userAlertThreshold;
    document.getElementById("alert-email-enabled").checked =
      !!data.data.user_account_alert_email_enabled;

    // Pré-preencher Presentation Mode
    userChartPosition = data.data.user_account_chart_position ?? "card";
    renderChartPositionSelection();
  } else {
    document.getElementById("account-email").textContent = "--";
    showToast("Could not load account info", "error");
  }
});

function renderChartPositionSelection() {
  document.querySelectorAll(".chart-position-option").forEach((opt) => {
    opt.classList.toggle("active", opt.dataset.value === userChartPosition);
  });
}

document.querySelectorAll(".chart-position-option").forEach((opt) => {
  opt.addEventListener("click", async () => {
    const newValue = opt.dataset.value;
    if (newValue === userChartPosition) return;

    const data = await api
      .updateUserSettings({ chart_position: newValue })
      .catch((err) => ({ success: false, message: err.message }));
    if (data?.success) {
      userChartPosition = newValue;
      renderChartPositionSelection();
      showToast("Presentation mode updated", "success");
      loadPlants();
    } else {
      showToast(data?.message ?? "Could not save", "error");
    }
  });
});

// Save alert settings on change
document
  .getElementById("alert-threshold")
  .addEventListener("change", async (e) => {
    const newThreshold = parseInt(e.target.value);
    const data = await api
      .updateUserSettings({ alert_threshold: newThreshold })
      .catch((err) => ({ success: false, message: err.message }));
    if (data?.success) {
      userAlertThreshold = newThreshold;
      showToast("Alert threshold updated", "success");
    } else {
      showToast(data?.message ?? "Could not save", "error");
    }
  });

document
  .getElementById("alert-email-enabled")
  .addEventListener("change", async (e) => {
    const data = await api
      .updateUserSettings({ alert_email_enabled: e.target.checked })
      .catch((err) => ({ success: false, message: err.message }));
    if (!data?.success) {
      // Reverte o checkbox se falhou
      e.target.checked = !e.target.checked;
      showToast(data?.message ?? "Could not save", "error");
    }
  });

document
  .getElementById("confirm-email-btn")
  .addEventListener("click", async () => {
    const data = await api.confirmEmail().catch((err) => ({
      success: false,
      message: err.message,
    }));
    if (data?.success) {
      renderAccountEmailStatus(true);
      showToast("Email confirmed", "success");
    } else {
      showToast(data?.message ?? "Could not confirm email", "error");
    }
  });

// Change Password
document
  .getElementById("open-change-password")
  .addEventListener("click", () => {
    closeModal("account-modal-overlay");
    openModal("change-password-modal-overlay");
  });

document
  .getElementById("change-password-form")
  .addEventListener("submit", async (e) => {
    e.preventDefault();
    const errorMsg = document.getElementById("change-password-error");
    errorMsg.textContent = "";

    const current = document.getElementById("current-password").value;
    const next = document.getElementById("new-password").value;
    const repeat = document.getElementById("repeat-new-password").value;

    if (next !== repeat) {
      errorMsg.textContent = "New passwords don't match";
      return;
    }
    if (next === current) {
      errorMsg.textContent = "New password must be different from current";
      return;
    }

    const data = await api.changePassword(current, next).catch((err) => ({
      success: false,
      message: err.message,
    }));

    if (data?.success) {
      closeModal("change-password-modal-overlay");
      showToast("Password changed successfully", "success");
    } else {
      errorMsg.textContent = data?.message ?? "Could not change password";
    }
  });

// Limpeza ao fechar modais
function clearModal(id) {
  if (id === "add-plant-modal-overlay") {
    setAddPlantMode("add");
  } else if (id === "change-password-modal-overlay") {
    document.getElementById("current-password").value = "";
    document.getElementById("new-password").value = "";
    document.getElementById("repeat-new-password").value = "";
    document.getElementById("change-password-error").textContent = "";
  } else if (id === "redeem-modal-overlay") {
    document.getElementById("activation-code").value = "";
    document.getElementById("redeem-error").textContent = "";
  }
}

setupModalClosers(clearModal);

// Settings dropdown
const settingsBtn = document.getElementById("settings-btn");
const settingsDropdown = document.getElementById("settings-dropdown");

settingsBtn.addEventListener("click", (e) => {
  e.stopPropagation();
  settingsDropdown.classList.toggle("hidden");
});

// Fechar ao clicar fora (suprimido enquanto o tutorial está ativo)
document.addEventListener("click", (e) => {
  if (window.__tutorialActive) return;
  if (!settingsDropdown.contains(e.target) && e.target !== settingsBtn) {
    settingsDropdown.classList.add("hidden");
  }
});

// Init
async function initDashboard() {
  // Carrega settings do user para os alertas e modo de gráfico
  const userData = await api.getUserInfo().catch(() => null);
  if (userData?.success && userData.data) {
    userAlertThreshold = userData.data.user_account_alert_threshold ?? 30;
    userChartPosition = userData.data.user_account_chart_position ?? "card";
  }
  await loadPlants();
  // Dispara o welcome popup só se primeira visita E user sem plantas
  maybeStartOnboarding(plantsCache.length);
}

// Replay tutorial a partir do Account modal
document.getElementById("replay-tutorial-btn").addEventListener("click", () => {
  closeModal("account-modal-overlay");
  replayOnboarding();
});

initDashboard();
setInterval(loadPlants, 5 * 60 * 1000);
