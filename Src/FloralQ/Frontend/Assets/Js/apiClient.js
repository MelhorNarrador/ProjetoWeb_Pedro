const BASE = "../../Backend/API";
const AUTH = "../../Backend/Auth";

async function request(url, options = {}) {
  const response = await fetch(url, options);
  // Parsear o body
  const body = await response.json().catch(() => null);
  if (!response.ok) {
    throw new Error(body?.message ?? `HTTP ${response.status}`);
  }
  return body;
}

export async function getPlants() {
  return request(`${BASE}/get_user_plants.php`);
}

export async function getPlantTypes() {
  return request(`${BASE}/get_plant_types.php`);
}

export async function getUserDevices() {
  return request(`${BASE}/get_user_devices.php`);
}

export async function getDryPrediction(deviceId) {
  return request(`${BASE}/get_dry_prediction.php?device_id=${deviceId}`);
}

export async function getLocation(deviceCode) {
  return request(`${BASE}/get_location.php?device_code=${deviceCode}`);
}

export async function createPlant(body) {
  return request(`${BASE}/create_plant.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });
}

export async function redeemDevice(activationCode) {
  return request(`${BASE}/redeem_device.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ activation_code: activationCode }),
  });
}

export async function login(email, password) {
  return request(`${AUTH}/login.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password }),
  });
}

export async function register(name, email, password) {
  return request(`${AUTH}/register.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ name, email, password }),
  });
}

export async function logout() {
  return fetch(`${AUTH}/logout.php`, { method: "POST" });
}

export async function getReadingsHistory(deviceCode, range = "24h") {
  return request(
    `${BASE}/get_readings_history.php?device_code=${deviceCode}&range=${range}`,
  );
}

export async function deletePlant(plantId) {
  return request(`${BASE}/delete_plant.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ plant_id: plantId }),
  });
}

export async function updatePlant(body) {
  return request(`${BASE}/update_plant.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });
}

export async function getUserInfo() {
  return request(`${BASE}/get_user_info.php`);
}

export async function confirmEmail() {
  return request(`${BASE}/confirm_email.php`, { method: "POST" });
}

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

export async function updateUserSettings(settings) {
  return request(`${BASE}/update_user_settings.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(settings),
  });
}

export async function uploadPlantImage(plantId, file) {
  const formData = new FormData();
  formData.append("plant_id", plantId);
  formData.append("image", file);
  // Sem Content-Type: o browser define multipart/form-data com o boundary
  return request(`${BASE}/upload_plant_image.php`, {
    method: "POST",
    body: formData,
  });
}
