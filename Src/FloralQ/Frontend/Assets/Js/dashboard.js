// ── Abrir/fechar modals ──────────────────────────────────────────

document
  .getElementById("open-add-plant")
  .addEventListener("click", function () {
    loadPlantTypes();
    loadUserDevices();
    document
      .getElementById("add-plant-modal-overlay")
      .classList.remove("hidden");
  });

document.getElementById("open-redeem").addEventListener("click", function () {
  document.getElementById("redeem-modal-overlay").classList.remove("hidden");
});

document.querySelectorAll(".modal-close").forEach(function (btn) {
  btn.addEventListener("click", function () {
    const targetId = btn.getAttribute("data-target");
    document.getElementById(targetId).classList.add("hidden");
  });
});

// Fechar modal ao clicar no fundo escuro
document.querySelectorAll("[id$='-modal-overlay']").forEach(function (overlay) {
  overlay.addEventListener("click", function (e) {
    if (e.target === overlay) {
      overlay.classList.add("hidden");
    }
  });
});

// Logout

document.getElementById("logout-btn").addEventListener("click", function () {
  fetch("../../Backend/Auth/logout.php", { method: "POST" }).then(function () {
    window.location.href = "login.html";
  });
});

// Carregar plantas

function loadPlants() {
  fetch("../../Backend/API/get_user_plants.php")
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      const grid = document.getElementById("plants-grid");
      grid.innerHTML = "";

      if (!data.success || data.plants.length === 0) {
        grid.innerHTML =
          "<p style='color:#888'>No plants yet. Add your first plant!</p>";
        return;
      }

      data.plants.forEach(function (plant) {
        const card = createPlantCard(plant);
        grid.appendChild(card);
      });
    });
}

// Criar card de planta

function createPlantCard(plant) {
  const card = document.createElement("div");
  card.className = "plant-card";

  const moisture = plant.sensor_reading_moisture_percent ?? "--";

  const status = getMoistureStatus(
    moisture,
    plant.plant_type_min_moisture,
    plant.plant_type_max_moisture,
  );

  card.innerHTML = `
    <div class="chart-container">
      <canvas id="chart-${plant.plant_id}"></canvas>
    </div>
    <h3>${plant.plant_name}</h3>
    <p class="status-${status}">${status}</p>
    <p id="dry-${plant.plant_id}">Loading prediction...</p>
  `;

  card.addEventListener("click", function () {
    openPlantModal(plant);
  });

  // Desenha o gráfico circular
  setTimeout(function () {
    drawMoistureChart(plant.plant_id, moisture, plant.plant_type_max_moisture);
  }, 0);

  // Vai buscar a previsão de quando seca
  loadDryPrediction(plant.plant_id, plant.device_id);

  return card;
}

// Estado da humidade

function getMoistureStatus(moisture, min, max) {
  if (moisture === "--") return "no data";
  const value = parseFloat(moisture);
  if (value < parseFloat(min)) return "dry";
  if (value > parseFloat(max)) return "overwatered";
  return "healthy";
}

// Gráfico circular de humidade

function drawMoistureChart(plantId, moisture, max) {
  const ctx = document.getElementById("chart-" + plantId);
  if (!ctx) return;

  const raw = moisture === "--" ? 0 : parseFloat(moisture);
  const maxVal = parseFloat(max) || 100;

  // Percentagem relativa ao máximo da planta (pode passar de 100%)
  const relative = Math.round((raw / maxVal) * 100);
  // Para o gráfico visual, limita a 100% para não quebrar o doughnut
  const value = Math.min(relative, 100);

  const color =
    relative > 100
      ? "#5599e0"
      : relative >= 50
        ? "#a8d96c"
        : relative >= 25
          ? "#f0c040"
          : "#e05555";
  const label = moisture === "--" ? "--" : relative + "%";

  new Chart(ctx, {
    type: "doughnut",
    data: {
      datasets: [
        {
          data: [value, 100 - value],
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
        beforeDraw: function (chart) {
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

// Previsão de secagem

function loadDryPrediction(plantId, deviceId) {
  fetch("../../Backend/API/get_dry_prediction.php?device_id=" + deviceId)
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      const el = document.getElementById("dry-" + plantId);
      if (!el) return;

      if (!data.success || !data.data) {
        el.textContent = "Not enough data";
        return;
      }

      const prediction = data.data.prediction;

      if (prediction === "drying") {
        el.textContent = "Runs dry in ~" + data.data.dry_in_friendly;
      } else if (prediction === "recently_watered") {
        el.textContent = "Recently watered";
      } else if (prediction === "increasing_moisture") {
        el.textContent = "Moisture increasing";
      } else if (prediction === "insufficient_data") {
        el.textContent = "Not enough data";
      } else {
        el.textContent = "Unstable data";
      }
    });
}

// Modal de detalhes da planta

function openPlantModal(plant) {
  const body = document.getElementById("plant-modal-body");
  const moisture = plant.sensor_reading_moisture_percent ?? "--";
  const grown = plant.plant_is_grown ? "Grown" : "Newborn";
  const professional = plant.device_is_professional
    ? "FloraIQ Professional"
    : "FloraIQ Home";

  body.innerHTML = `
    <h2>${plant.plant_name}</h2>
    <p>Location: <span>${plant.plant_location_label}</span></p>
    <p>Status: <span>${grown}</span></p>
    <p>Device: <span>${professional}</span></p>
    <p>Current moisture: <span>${moisture}%</span></p>
    <p>Prediction: <span id="modal-prediction">--</span></p>
    <p>Min moisture: <span id="modal-min-moisture">--</span></p>
    <p>Runs dry at: <span id="modal-dry-at">--</span></p>
    <p>Drying rate: <span id="modal-trend">--</span></p>
    <p>Prediction confidence: <span id="modal-confidence">--</span></p>
    <div id="modal-map"></div>
  `;

  document.getElementById("plant-modal-overlay").classList.remove("hidden");

  // Vai buscar a previsão para preencher os campos
  fetch("../../Backend/API/get_dry_prediction.php?device_id=" + plant.device_id)
    .then(function (r) {
      return r.json();
    })
    .then(function (data) {
      if (!data.success || !data.data) return;

      const prediction = data.data.prediction;
      const predictionLabels = {
        drying: "Drying",
        recently_watered: "Recently watered",
        increasing_moisture: "Moisture increasing",
        insufficient_data: "Not enough data",
        unstable_data: "Unstable data",
      };

      document.getElementById("modal-prediction").textContent =
        predictionLabels[prediction] || "Unknown";

      if (prediction === "drying") {
        const d = data.data;
        document.getElementById("modal-min-moisture").textContent =
          d.min_moisture + "%";
        document.getElementById("modal-dry-at").textContent = d.dry_at;
        document.getElementById("modal-trend").textContent =
          d.trend_per_hour + "% per hour";
        document.getElementById("modal-confidence").textContent = d.confidence;
      }
    });

  // Mapa só aparece se o dispositivo tiver GPS
  if (plant.device_is_professional) {
    fetch("../../Backend/API/get_location.php?device_code=" + plant.device_code)
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (data.success && data.data) {
          loadMap(data.data.latitude, data.data.longitude);
        }
      });
  }
}

// Google Maps

function loadMap(lat, lng) {
  const mapDiv = document.getElementById("modal-map");
  mapDiv.innerHTML = `<iframe
    width="100%" height="200"
    style="border-radius:8px; border:0"
    loading="lazy"
    src="https://www.google.com/maps?q=${lat},${lng}&z=15&output=embed">
  </iframe>`;
}

// Formulário: adicionar planta

function loadPlantTypes() {
  fetch("../../Backend/API/get_plant_types.php")
    .then(function (r) {
      return r.json();
    })
    .then(function (data) {
      const select = document.getElementById("plant-type");
      select.innerHTML = "";
      data.plant_types.forEach(function (type) {
        select.innerHTML += `<option value="${type.plant_type_id}">${type.plant_type_name}</option>`;
      });
    });
}

function loadUserDevices() {
  fetch("../../Backend/API/get_user_devices.php")
    .then(function (r) {
      return r.json();
    })
    .then(function (data) {
      const select = document.getElementById("plant-device");
      select.innerHTML = "";
      data.devices.forEach(function (device) {
        select.innerHTML += `<option value="${device.device_id}">${device.device_code}</option>`;
      });
    });
}

document
  .getElementById("add-plant-form")
  .addEventListener("submit", function (e) {
    e.preventDefault();

    const errorMsg = document.getElementById("add-plant-error");

    const body = {
      plant_name: document.getElementById("plant-name").value,
      plant_location_label: document.getElementById("plant-location").value,
      plant_type_id: parseInt(document.getElementById("plant-type").value),
      device_id: parseInt(document.getElementById("plant-device").value),
      plant_is_grown: document.getElementById("plant-is-grown").checked,
    };

    fetch("../../Backend/API/create_plant.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body),
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (data.success) {
          document
            .getElementById("add-plant-modal-overlay")
            .classList.add("hidden");
          loadPlants();
        } else {
          errorMsg.textContent = data.message;
        }
      });
  });

// Formulario: redeem de dispositivo

document.getElementById("redeem-form").addEventListener("submit", function (e) {
  e.preventDefault();

  const errorMsg = document.getElementById("redeem-error");
  const code = document.getElementById("activation-code").value;

  fetch("../../Backend/API/redeem_device.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ activation_code: code }),
  })
    .then(function (r) {
      return r.json();
    })
    .then(function (data) {
      if (data.success) {
        document.getElementById("redeem-modal-overlay").classList.add("hidden");
        loadPlants();
      } else {
        errorMsg.textContent = data.message;
      }
    });
});

//Inicializar

loadPlants();
setInterval(loadPlants, 30000); // atualiza a cada 30 segundos
