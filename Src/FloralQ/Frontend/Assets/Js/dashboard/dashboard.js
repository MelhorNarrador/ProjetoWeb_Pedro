import * as api from "../apiClient.js";
import { openModal, closeModal, setupModalClosers } from "./modalManager.js";
import { buildPlantCard } from "./plantCard.js";
import { drawMoistureChart } from "./chartController.js";
import { getPredictionLabel } from "./utils/moisture.js";

// Cache em memória
let plantsCache = [];

// Grid de plantas
async function loadPlants() {
  const grid = document.getElementById("plants-grid");
  const data = await api.getPlants().catch(() => null);

  grid.innerHTML = "";

  if (!data?.success || data.plants.length === 0) {
    grid.innerHTML = "<p style='color:#888'>No plants yet. Add your first plant!</p>";
    plantsCache = [];
    return;
  }

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
}

// Event delegation
document.getElementById("plants-grid").addEventListener("click", (e) => {
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
  body.querySelector(".modal-location").textContent = plant.plant_location_label;
  body.querySelector(".modal-stage").textContent = plant.plant_is_grown ? "Grown" : "Newborn";
  body.querySelector(".modal-device").textContent = plant.device_is_professional
    ? "FloraIQ Professional"
    : "FloraIQ Home";
  body.querySelector(".modal-moisture").textContent =
    moisture === "--" ? "--" : `${moisture}%`;

  const predictionEl = body.querySelector(".modal-prediction");
  const minMoistureEl = body.querySelector(".modal-min-moisture");
  const dryAtEl = body.querySelector(".modal-dry-at");
  const trendEl = body.querySelector(".modal-trend");
  const confidenceEl = body.querySelector(".modal-confidence");
  const mapEl = body.querySelector(".modal-map");

  
  [predictionEl, minMoistureEl, dryAtEl, trendEl, confidenceEl].forEach(
    (el) => (el.textContent = "--"),
  );
  mapEl.innerHTML = "";

  openModal("plant-modal-overlay");

  // Previsão de secagem
  const predData = await api.getDryPrediction(plant.device_id).catch(() => null);
  if (predData?.success && predData.data) {
    const d = predData.data;
    predictionEl.textContent = getPredictionLabel(d.prediction);
    if (d.prediction === "drying") {
      minMoistureEl.textContent = `${d.min_moisture}%`;
      dryAtEl.textContent = d.dry_at;
      trendEl.textContent = `${d.trend_per_hour}% per hour`;
      confidenceEl.textContent = d.confidence;
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
}

// Formulário adicionar planta
document.getElementById("open-add-plant").addEventListener("click", async () => {
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

  openModal("add-plant-modal-overlay");
});

document.getElementById("add-plant-form").addEventListener("submit", async (e) => {
  e.preventDefault();
  const errorMsg = document.getElementById("add-plant-error");
  errorMsg.textContent = "";

  const body = {
    plant_name: document.getElementById("plant-name").value,
    plant_location_label: document.getElementById("plant-location").value,
    plant_type_id: parseInt(document.getElementById("plant-type").value),
    device_id: parseInt(document.getElementById("plant-device").value),
    plant_is_grown: document.getElementById("plant-is-grown").checked,
  };

  const data = await api.createPlant(body).catch(() => null);
  if (data?.success) {
    closeModal("add-plant-modal-overlay");
    loadPlants();
  } else {
    errorMsg.textContent = data?.message ?? "Something went wrong.";
  }
});

// Formulário redeem de dispositivo 
document.getElementById("open-redeem").addEventListener("click", () => {
  openModal("redeem-modal-overlay");
});

document.getElementById("redeem-form").addEventListener("submit", async (e) => {
  e.preventDefault();
  const errorMsg = document.getElementById("redeem-error");
  errorMsg.textContent = "";
  const code = document.getElementById("activation-code").value;

  const data = await api.redeemDevice(code).catch(() => null);
  if (data?.success) {
    closeModal("redeem-modal-overlay");
    loadPlants();
  } else {
    errorMsg.textContent = data?.message ?? "Something went wrong.";
  }
});

// Logout
document.getElementById("logout-btn").addEventListener("click", async () => {
  await api.logout();
  window.location.href = "login.html";
});

// Limpeza ao fechar modais
function clearModal(id) {
  if (id === "add-plant-modal-overlay") {
    document.getElementById("plant-name").value = "";
    document.getElementById("plant-location").value = "";
    document.getElementById("plant-is-grown").checked = false;
    document.getElementById("add-plant-error").textContent = "";
  } else if (id === "redeem-modal-overlay") {
    document.getElementById("activation-code").value = "";
    document.getElementById("redeem-error").textContent = "";
  }
}

setupModalClosers(clearModal);

// Init
loadPlants();
setInterval(loadPlants, 5 * 60 * 1000);
