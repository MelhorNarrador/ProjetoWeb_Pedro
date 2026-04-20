const BASE = "../../Backend/API";
const AUTH = "../../Backend/Auth";

async function request(url, options = {}) {
  const response = await fetch(url, options);
  if (!response.ok) throw new Error(`HTTP ${response.status}: ${url}`);
  return response.json();
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
