import * as api from "../apiClient.js";
import { showToast } from "../toast.js";
import { openModal, closeModal, setupModalClosers } from "./modalManager.js";
import { buildPlantCard } from "./plantCard.js";
import { drawMoistureChart } from "./chartController.js";
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
  }
}

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
    const card = buildPlantCard(plant);
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
}
// Event delegation
document.getElementById("plants-grid").addEventListener("click", (e) => {
  if (e.target.closest(".card-tab")) return; // ← ignora cliques em tabs

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
      data = await api.updatePlant(body).catch(onErr);
    } else {
      body.device_id = parseInt(document.getElementById("plant-device").value);
      data = await api.createPlant(body).catch(onErr);
    }

    if (data?.success) {
      closeModal("add-plant-modal-overlay");
      showToast(
        editingPlantId ? "Plant updated" : "Plant created",
        "success",
      );
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

// Limpeza ao fechar modais
function clearModal(id) {
  if (id === "add-plant-modal-overlay") {
    setAddPlantMode("add");
  } else if (id === "redeem-modal-overlay") {
    document.getElementById("activation-code").value = "";
    document.getElementById("redeem-error").textContent = "";
  }
}

setupModalClosers(clearModal);

// ── Settings dropdown ──
const settingsBtn = document.getElementById("settings-btn");
const settingsDropdown = document.getElementById("settings-dropdown");

settingsBtn.addEventListener("click", (e) => {
  e.stopPropagation();
  settingsDropdown.classList.toggle("hidden");
});

// Fechar ao clicar fora
document.addEventListener("click", (e) => {
  if (!settingsDropdown.contains(e.target) && e.target !== settingsBtn) {
    settingsDropdown.classList.add("hidden");
  }
});

// Init
loadPlants();
setInterval(loadPlants, 5 * 60 * 1000);
