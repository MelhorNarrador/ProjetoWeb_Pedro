// Desenha um gráfico redondo para umidade usando Chart.js
import { getNormalizedMoisture } from "./utils/moisture.js";
export function drawMoistureChart(canvas, moisture, min, max) {
  const isNoData = moisture === "--";
  const percent = isNoData ? 0 : getNormalizedMoisture(moisture, min, max);
  const displayValue = Math.max(0, Math.min(percent, 100)) || 1;
  const color =
    percent <= 40
      ? "#e05555"
      : percent <= 80
        ? "#a8d96c"
        : percent <= 100
          ? "#2d6e3e"
          : "#5599e0";

  const label = isNoData ? "--" : `${Math.max(0, percent)}%`;

  new Chart(canvas, {
    type: "doughnut",
    data: {
      datasets: [
        {
          data: [displayValue, 100 - displayValue],
          backgroundColor: [color, "#E0E0C0"],
          borderWidth: 0,
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

// Desenha um gráfico de linhas com Chart.js
export function drawLineChart(canvas, dataPoints, color = "#1E4D2B") {
  // Limpar gráfico anterior se existir
  if (canvas._chartInstance) {
    canvas._chartInstance.destroy();
  }

  const labels = dataPoints.map((p) => formatLabel(p.recorded_at));
  const values = dataPoints.map((p) => parseFloat(p.moisture));
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

// Formata o timestamp
function formatLabel(timestamp) {
  const d = new Date(timestamp);
  return d.toLocaleString();
}
