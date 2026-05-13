import {
  getMoistureStatus,
  getMoistureColor,
  formatDryPrediction,
} from "./utils/moisture.js";
import { getDryPrediction } from "../apiClient.js";
import { loadHistoryChart } from "./chartController.js";

export function buildPlantCard(plant, chartPosition = "card") {
  const template = document.getElementById("plant-card-template");
  const card = template.content.cloneNode(true).querySelector(".plant-card");

  const moisture = plant.sensor_reading_moisture_percent ?? "--";
  const status = getMoistureStatus(
    moisture,
    plant.plant_type_min_moisture,
    plant.plant_type_max_moisture,
  );
  const color = getMoistureColor(
    moisture,
    plant.plant_type_min_moisture,
    plant.plant_type_max_moisture,
  );

  card.dataset.plantId = plant.plant_id;
  card.classList.add(`status-${status}`);
  card.querySelector(".card-name").textContent = plant.plant_name;

  const statusEl = card.querySelector(".card-status");
  statusEl.textContent = status;
  statusEl.className = `card-status status-${status}`;

  card.querySelector(".chart-wrap canvas").id = `chart-${plant.plant_id}`;

  // Imagem da planta (ao lado direito)
  const imgEl = card.querySelector(".card-image");
  if (plant.plant_image_path) {
    imgEl.src = `../Assets/Uploads/${plant.plant_image_path}`;
  } else {
    imgEl.style.display = "none";
  }

  const metaEl = card.querySelector(".card-meta-text");
  const timeAgo = formatTimeAgo(plant.sensor_reading_recorded_at);
  metaEl.textContent =
    plant.sensor_status === "offline" ? `${timeAgo} · Sensor Offline` : timeAgo;

  const predictionEl = card.querySelector(".card-prediction");
  getDryPrediction(plant.device_id)
    .then((data) => {
      predictionEl.textContent =
        data.success && data.data
          ? formatDryPrediction(data.data)
          : "Not enough data";
    })
    .catch(() => {
      predictionEl.textContent = "Not enough data";
    });

  // Gráfico de histórico, só renderiza se o modo for "card"
  const historySection = card.querySelector(".card-history");
  if (chartPosition === "modal") {
    historySection.style.display = "none";
  } else {
    const lineCanvas = card.querySelector(".line-chart");
    const min = plant.plant_type_min_moisture;
    const max = plant.plant_type_max_moisture;
    loadHistoryChart(lineCanvas, plant.device_code, "24h", color, min, max);

    // Tabs
    card.querySelectorAll(".card-tab").forEach((tab) => {
      tab.addEventListener("click", () => {
        card
          .querySelectorAll(".card-tab")
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
      });
    });
  }

  return card;
}

function formatTimeAgo(timestamp) {
  if (!timestamp) return "--";
  const seconds = Math.floor(
    (Date.now() - new Date(timestamp).getTime()) / 1000,
  );
  if (seconds < 60) return `${seconds}s ago`;
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes} min ago`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours}h ago`;
  const days = Math.floor(hours / 24);
  return `${days}d ago`;
}
