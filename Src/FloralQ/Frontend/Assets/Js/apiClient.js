// Wrapper para todas as chamadas ao backend
// Cada função corresponde a um endpoint PHP; todas devolvem o JSON já parsed
const BASE = "../../Backend/API";
const AUTH = "../../Backend/Auth";

// Wrapper genérico de fetch: parse JSON e lança erro se a resposta for != 2xx
async function request(url, options = {}) {
  const response = await fetch(url, options);
  // Parse JSON do body (catch para o caso de não ser JSON válido)
  const body = await response.json().catch(() => null);
  if (!response.ok) {
    throw new Error(body?.message ?? `HTTP ${response.status}`);
  }
  return body;
}

// GET: todas as plantas do user (cards do dashboard)
export async function getPlants() {
  return request(`${BASE}/get_user_plants.php`);
}

// GET: espécies disponíveis (para o dropdown do form)
export async function getPlantTypes() {
  return request(`${BASE}/get_plant_types.php`);
}

// GET: devices do user que ainda não têm planta associada
export async function getUserDevices() {
  return request(`${BASE}/get_user_devices.php`);
}

// GET: previsão de quanto tempo até a planta secar
export async function getDryPrediction(deviceId) {
  return request(`${BASE}/get_dry_prediction.php?device_id=${deviceId}`);
}

// GET: últimas coordenadas GPS do sensor (para o mapa)
export async function getLocation(deviceCode) {
  return request(`${BASE}/get_location.php?device_code=${deviceCode}`);
}

// POST: cria uma nova planta
export async function createPlant(body) {
  return request(`${BASE}/create_plant.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });
}

// POST: associa um sensor à conta do user (resgata pelo activation code)
export async function redeemDevice(activationCode) {
  return request(`${BASE}/redeem_device.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ activation_code: activationCode }),
  });
}

// POST: autenticação
export async function login(email, password) {
  return request(`${AUTH}/login.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password }),
  });
}

// POST: criar conta nova
export async function register(name, email, password) {
  return request(`${AUTH}/register.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ name, email, password }),
  });
}

// POST: termina a sessão
export async function logout() {
  return fetch(`${AUTH}/logout.php`, { method: "POST" });
}

// GET: histórico de leituras (para o gráfico de linhas)
export async function getReadingsHistory(deviceCode, range = "24h") {
  return request(
    `${BASE}/get_readings_history.php?device_code=${deviceCode}&range=${range}`,
  );
}

// POST: apaga uma planta (e leituras associadas)
export async function deletePlant(plantId) {
  return request(`${BASE}/delete_plant.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ plant_id: plantId }),
  });
}

// POST: edita dados de uma planta existente
export async function updatePlant(body) {
  return request(`${BASE}/update_plant.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });
}

// GET: dados da conta do user (modal Account)
export async function getUserInfo() {
  return request(`${BASE}/get_user_info.php`);
}

// POST: stub que marca o email como verificado
export async function confirmEmail() {
  return request(`${BASE}/confirm_email.php`, { method: "POST" });
}

// POST: alterar password
export async function changePassword(currentPassword, newPassword) {
  return request(`${BASE}/change_password.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      current_password: currentPassword,
      new_password: newPassword,
    }),
  });
}

// POST: atualizar settings da conta (threshold, alerts, posição do chart)
export async function updateUserSettings(settings) {
  return request(`${BASE}/update_user_settings.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(settings),
  });
}

// POST: upload de imagem da planta (multipart/form-data)
export async function uploadPlantImage(plantId, file) {
  const formData = new FormData();
  formData.append("plant_id", plantId);
  formData.append("image", file);
  // Não definir Content-Type manualmente: o browser põe multipart/form-data com o boundary
  return request(`${BASE}/upload_plant_image.php`, {
    method: "POST",
    body: formData,
  });
}
