export function getMoistureStatus(moisture, min, max) {
  if (moisture === "--") return "no-data";
  const value = parseFloat(moisture);
  if (value < parseFloat(min)) return "dry";
  if (value > parseFloat(max)) return "overwatered";
  return "healthy";
}

export function getPredictionLabel(key) {
  const labels = {
    drying: "Drying",
    recently_watered: "Recently watered",
    increasing_moisture: "Moisture increasing",
    insufficient_data: "Not enough data",
    unstable_data: "Unstable data",
  };
  return labels[key] ?? "Unknown";
}

export function formatDryPrediction(data) {
  const { prediction } = data;
  if (prediction === "drying") return `Runs dry in ~${data.dry_in_friendly}`;
  return getPredictionLabel(prediction);
}
