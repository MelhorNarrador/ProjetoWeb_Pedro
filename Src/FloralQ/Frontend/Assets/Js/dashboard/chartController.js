export function drawMoistureChart(canvas, moisture, min, max) {
  const raw = moisture === "--" ? 0 : parseFloat(moisture);
  const minVal = parseFloat(min) || 0;
  const maxVal = parseFloat(max) || 100;

  const range = maxVal - minVal;
  const percent =
    range > 0 ? Math.round(((raw - minVal) / range) * 100) : Math.round(raw);

  const value = Math.max(0, Math.min(percent, 100));
  const isNoData = moisture === "--";
  const displayValue = isNoData ? 0 : value === 0 ? 1 : value;

  const color =
    percent <= 40 ? "#e05555"
    : percent <= 80 ? "#a8d96c"
    : percent <= 100 ? "#2d6e3e"
    : "#5599e0";

  const label = isNoData ? "--" : `${Math.max(0, percent)}%`;

  new Chart(canvas, {
    type: "doughnut",
    data: {
      datasets: [
        {
          data: [displayValue, 100 - displayValue],
          backgroundColor: [color, "#2a2a2a"],
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
          ctx.fillStyle = "#e0e0e0";
          ctx.textAlign = "center";
          ctx.textBaseline = "middle";
          ctx.fillText(label, centerX, centerY);
          ctx.restore();
        },
      },
    ],
  });
}
