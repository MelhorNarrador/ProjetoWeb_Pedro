import { getMoistureStatus, formatDryPrediction } from "./utils/moisture.js";
import { getDryPrediction } from "../apiClient.js";

export function buildPlantCard(plant) {
  const template = document.getElementById("plant-card-template");
  const card = template.content.cloneNode(true).querySelector(".plant-card");

  const moisture = plant.sensor_reading_moisture_percent ?? "--";
  const status = getMoistureStatus(
    moisture,
    plant.plant_type_min_moisture,
    plant.plant_type_max_moisture,
  );

  card.dataset.plantId = plant.plant_id;
  card.querySelector(".card-name").textContent = plant.plant_name;

  const statusEl = card.querySelector(".card-status");
  statusEl.textContent = status;
  statusEl.className = `card-status status-${status}`;

  card.querySelector("canvas").id = `chart-${plant.plant_id}`;

  const predictionEl = card.querySelector(".card-prediction");
  getDryPrediction(plant.device_id)
    .then((data) => {
      predictionEl.textContent =
        data.success && data.data ? formatDryPrediction(data.data) : "Not enough data";
    })
    .catch(() => {
      predictionEl.textContent = "Not enough data";
    });

  return card;
}
