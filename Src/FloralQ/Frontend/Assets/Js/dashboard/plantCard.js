import { getMoistureStatus, formatDryPrediction } from "./utils/moisture.js";
import { getDryPrediction, getReadingsHistory } from "../apiClient.js";
import { drawLineChart } from "./chartController.js";

const STATUS_COLORS = {
  healthy: "#1E4D2B",
  dry: "#E05555",
  overwatered: "#5599E0",
  "no-data": "#8A9480",
};

export function buildPlantCard(plant) {
  const template = document.getElementById("plant-card-template");
  const card = template.content.cloneNode(true).querySelector(".plant-card");

  const moisture = plant.sensor_reading_moisture_percent ?? "--";
  const status = getMoistureStatus(
    moisture,
    plant.plant_type_min_moisture,
    plant.plant_type_max_moisture,
  );
  const color = STATUS_COLORS[status];

  card.dataset.plantId = plant.plant_id;
  card.classList.add(`status-${status}`);
  card.querySelector(".card-name").textContent = plant.plant_name;

  const statusEl = card.querySelector(".card-status");
  statusEl.textContent = status;
  statusEl.className = `card-status status-${status}`;

  card.querySelector(".chart-wrap canvas").id = `chart-${plant.plant_id}`;

  const metaEl = card.querySelector(".card-meta-text");
  metaEl.textContent = formatTimeAgo(plant.sensor_reading_recorded_at);

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

  // Gráfico de histórico
  const lineCanvas = card.querySelector(".line-chart");
  loadHistoryChart(lineCanvas, plant.device_code, "24h", color);

  // Tabs
  card.querySelectorAll(".card-tab").forEach((tab) => {
    tab.addEventListener("click", () => {
      card
        .querySelectorAll(".card-tab")
        .forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");
      loadHistoryChart(lineCanvas, plant.device_code, tab.dataset.range, color);
    });
  });

  return card;
}

// Vai buscar histórico ao backend e desenha
function loadHistoryChart(canvas, deviceCode, range, color) {
  getReadingsHistory(deviceCode, range)
    .then((res) => {
      if (res.success && res.data.length > 0) {
        drawLineChart(canvas, res.data, color);
      }
    })
    .catch(() => {});
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
