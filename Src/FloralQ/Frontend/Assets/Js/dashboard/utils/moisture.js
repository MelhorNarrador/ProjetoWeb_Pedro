// Funções partilhadas para classificar/normalizar humidade
// Source of truth dos thresholds — usado pelo card, modal, gráfico e status

// Cores por estado de humidade (centralizadas para chart e status coincidirem)
const MOISTURE_COLORS = {
  red: "#E05555",
  greenLight: "#A8D96C",
  greenDark: "#2D6E3E",
  blue: "#5599E0",
  gray: "#8A9480",
};

// Devolve a categoria de estado da planta (dry/healthy/overwatered/no-data)
export function getMoistureStatus(moisture, min, max) {
  const percent = getNormalizedMoisture(moisture, min, max);
  if (percent === null) return "no-data";
  if (percent <= 40) return "dry";
  if (percent > 100) return "overwatered";
  return "healthy";
}

// Cor para gráficos: 4 tons (vermelho / verde claro / verde escuro / azul)
export function getMoistureColor(moisture, min, max) {
  const percent = getNormalizedMoisture(moisture, min, max);
  if (percent === null) return MOISTURE_COLORS.gray;
  if (percent <= 40) return MOISTURE_COLORS.red;
  if (percent <= 80) return MOISTURE_COLORS.greenLight;
  if (percent <= 100) return MOISTURE_COLORS.greenDark;
  return MOISTURE_COLORS.blue;
}

// Tradução das keys de previsão devolvidas pelo backend para texto legível
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

// Formata o objeto de previsão completo para mostrar no card/modal
export function formatDryPrediction(data) {
  const { prediction } = data;
  if (prediction === "drying") return `Runs dry in ~${data.dry_in_friendly}`;
  return getPredictionLabel(prediction);
}

// Converte humidade absoluta (0–100) para % relativa ao range da espécie
// Ex.: min=20, max=60, raw=40 → 50% (a meio do range confortável da planta)
export function getNormalizedMoisture(raw, min, max) {
  if (raw === "--" || raw === null || raw === undefined) return null;
  const r = parseFloat(raw);
  const minVal = parseFloat(min) || 0;
  const maxVal = parseFloat(max) || 100;
  const range = maxVal - minVal;
  return range > 0 ? Math.round(((r - minVal) / range) * 100) : Math.round(r);
}
