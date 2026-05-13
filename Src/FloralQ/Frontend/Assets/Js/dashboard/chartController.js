// Controla os gráficos Chart.js do dashboard: doughnut (humidade atual) e line (histórico)
import { getNormalizedMoisture, getMoistureColor } from "./utils/moisture.js";
import { getReadingsHistory } from "../apiClient.js";

// Desenha o gráfico doughnut com a humidade atual no centro
export function drawMoistureChart(canvas, moisture, min, max) {
  const isNoData = moisture === "--";
  const percent = isNoData ? 0 : getNormalizedMoisture(moisture, min, max);
  // Garantir valor entre 0 e 100 para o doughnut (e nunca 0 para não desaparecer)
  const displayValue = Math.max(0, Math.min(percent, 100)) || 1;
  const color = getMoistureColor(moisture, min, max);

  const label = isNoData ? "--" : `${Math.max(0, percent)}%`;

  // Destrói gráfico anterior se já existia (evita memory leak ao re-render)
  if (canvas._chartInstance) {
    canvas._chartInstance.destroy();
  }

  canvas._chartInstance = new Chart(canvas, {
    type: "doughnut",
    data: {
      datasets: [
        {
          data: [displayValue, 100 - displayValue],
          backgroundColor: [color, "#E0E0C0"],
          borderWidth: 0,
          borderRadius: 12,
        },
      ],
    },
    options: {
      cutout: "75%",
      plugins: {
        legend: { display: false },
        tooltip: { enabled: false },
      },
    },
    plugins: [
      // Plugin custom que desenha o texto da % no centro do doughnut
      {
        id: "center-text",
        beforeDraw(chart) {
          const { ctx, chartArea } = chart;
          const centerX = (chartArea.left + chartArea.right) / 2;
          const centerY = (chartArea.top + chartArea.bottom) / 2;
          ctx.save();
          ctx.font = "bold 16px sans-serif";
          ctx.fillStyle = color;
          ctx.textAlign = "center";
          ctx.textBaseline = "middle";
          ctx.fillText(label, centerX, centerY);
          ctx.restore();
        },
      },
    ],
  });
}

// Desenha o gráfico de linhas com o histórico de humidade
export function drawLineChart(canvas, dataPoints, color = "#1E4D2B") {
  // Limpar gráfico anterior se existir (evita memory leak)
  if (canvas._chartInstance) {
    canvas._chartInstance.destroy();
  }

  const labels = dataPoints.map((p) => formatLabel(p.recorded_at));
  const values = dataPoints.map((p) => parseFloat(p.moisture));

  // Gradiente vertical (cor → transparente) para o fill da área debaixo da linha
  const ctx = canvas.getContext("2d");
  const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
  gradient.addColorStop(0, color + "40");
  gradient.addColorStop(1, color + "00");

  canvas._chartInstance = new Chart(canvas, {
    type: "line",
    data: {
      labels,
      datasets: [
        {
          data: values,
          borderColor: color,
          backgroundColor: gradient,
          borderWidth: 2,
          fill: true,
          tension: 0.4,
          pointRadius: 2,
          pointHoverRadius: 4,
          pointHoverBackgroundColor: color,
          pointHoverBorderColor: "white",
          pointHoverBorderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          displayColors: false,
          callbacks: {
            label: (ctx) => `${ctx.parsed.y}%`,
          },
        },
      },
      scales: {
        x: { display: false },
        y: {
          display: true,
          beginAtZero: false,
          grid: { color: "#E0E0C015" },
          ticks: {
            color: "#8A9480",
            font: { size: 10 },
            callback: (value) => `${value}%`,
          },
        },
      },
    },
  });
}

// Formata o timestamp ISO no formato local do browser
function formatLabel(timestamp) {
  const d = new Date(timestamp);
  return d.toLocaleString();
}

// Vai buscar o histórico de leituras ao backend e desenha o gráfico de linhas
// min/max são os limites da espécie, usados para normalizar para % relativa
export function loadHistoryChart(canvas, deviceCode, range, color, min, max) {
  getReadingsHistory(deviceCode, range)
    .then((res) => {
      if (res.success && res.data.length > 0) {
        // Normaliza cada leitura para % relativa ao range da planta
        const normalized = res.data.map((p) => ({
          recorded_at: p.recorded_at,
          moisture: getNormalizedMoisture(p.moisture, min, max) ?? p.moisture,
        }));
        drawLineChart(canvas, normalized, color);
      }
    })
    .catch(() => {});
}
