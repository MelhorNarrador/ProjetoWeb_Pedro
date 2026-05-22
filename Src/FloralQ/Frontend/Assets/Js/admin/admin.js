// Controlador do painel de administração
// Tabs: Utilizadores | Devices | Plantas
// Ações: promover/revogar admin, apagar utilizador

const BASE = "../../../Backend/API/admin";
const AUTH = "../../../Backend/Auth";

// --- API helpers ---

async function apiFetch(url, options = {}) {
  const res = await fetch(url, options);
  const body = await res.json().catch(() => null);
  if (!res.ok) throw new Error(body?.message ?? `HTTP ${res.status}`);
  return body;
}

async function getUsers() {
  return apiFetch(`${BASE}/get_all_users.php`);
}
async function getDevices() {
  return apiFetch(`${BASE}/get_all_devices.php`);
}
async function getPlants() {
  return apiFetch(`${BASE}/get_all_plants.php`);
}

async function updateRole(userId, role) {
  return apiFetch(`${BASE}/update_user_role.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ user_id: userId, role }),
  });
}

async function deleteUser(userId) {
  return apiFetch(`${BASE}/delete_user.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ user_id: userId }),
  });
}

async function deleteDevice(deviceId) {
  return apiFetch(`${BASE}/delete_device.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ device_id: deviceId }),
  });
}

async function deletePlant(plantId) {
  return apiFetch(`${BASE}/delete_plant.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ plant_id: plantId }),
  });
}

async function logout() {
  await fetch(`${AUTH}/logout.php`, { method: "POST" });
  window.location.href = "login.html";
}

// --- Utility formatters ---

function fmtDate(iso) {
  if (!iso) return "—";
  return new Date(iso).toLocaleDateString("pt-PT", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
}

function fmtMoisture(val) {
  return val != null ? `${val}%` : "—";
}

// --- Badge builders ---

function badge(text, cls) {
  return `<span class="badge ${cls}">${text}</span>`;
}

function roleBadge(role) {
  return role === "admin"
    ? badge("Admin", "badge-admin")
    : badge("User", "badge-user");
}

function statusBadge(status) {
  return status === "online"
    ? badge("Online", "badge-online")
    : badge("Offline", "badge-offline");
}

// --- Render functions ---

function setTableContent(tableId, html) {
  document.querySelector(`#${tableId} tbody`).innerHTML = html;
}

function setTableRows(tableId, rows) {
  const tbody = document.querySelector(`#${tableId} tbody`);
  tbody.replaceChildren(...rows);
}

function showState(tableId, msg) {
  setTableContent(
    tableId,
    `<tr><td colspan="99" class="table-state">${msg}</td></tr>`,
  );
}

// Render da tabela de utilizadores
function renderUsers(users) {
  if (!users.length) {
    showState("table-users", "No users found.");
    return;
  }

  const selfId = Number(document.body.dataset.userId);

  const rows = users.map((u) => {
    const isSelf = u.user_account_id == selfId;
    const isAdmin = u.user_account_role === "admin";

    const tr = document.createElement("tr");
    // Nome
    const tdName = document.createElement("td");
    tdName.textContent = u.user_account_name;
    tr.appendChild(tdName);
    // Email
    const tdEmail = document.createElement("td");
    tdEmail.textContent = u.user_account_email;
    tdEmail.classList.add("td-muted");
    tr.appendChild(tdEmail);
    // Role
    const tdRole = document.createElement("td");
    tdRole.innerHTML = roleBadge(u.user_account_role);
    tr.appendChild(tdRole);
    // Data de criação
    const tdCreated = document.createElement("td");
    tdCreated.textContent = fmtDate(u.user_created_at);
    tdCreated.classList.add("td-muted");
    tr.appendChild(tdCreated);
    // Plantas
    const tdPlants = document.createElement("td");
    tdPlants.textContent = u.plant_count;
    tr.appendChild(tdPlants);
    // Devices
    const tdDevices = document.createElement("td");
    tdDevices.textContent = u.device_count;
    tr.appendChild(tdDevices);
    // Ações
    const tdActions = document.createElement("td");
    const actionDiv = document.createElement("div");
    actionDiv.classList.add("action-btns");
    // Promover/revogar admin
    const roleBtn = document.createElement("button");
    roleBtn.className = isAdmin
      ? "btn-action btn-demote"
      : "btn-action btn-promote";
    roleBtn.dataset.id = u.user_account_id;
    roleBtn.dataset.action = isAdmin ? "demote" : "promote";
    roleBtn.textContent = isAdmin ? "Demote" : "Promote";
    if (isAdmin && isSelf) {
      roleBtn.disabled = true;
    }
    actionDiv.appendChild(roleBtn);
    // Apagar utilizador
    const deleteBtn = document.createElement("button");
    deleteBtn.className = "btn-action btn-delete";
    deleteBtn.dataset.id = u.user_account_id;
    deleteBtn.dataset.action = "delete";
    deleteBtn.textContent = "Delete";
    if (isSelf || isAdmin) {
      deleteBtn.disabled = true;
    }
    actionDiv.appendChild(deleteBtn);

    tdActions.appendChild(actionDiv);
    tr.appendChild(tdActions);

    return tr;
  });

  setTableRows("table-users", rows);
  attachSearch("search-users", "table-users");
}

// Render da tabela de devices
function renderDevices(devices) {
  if (!devices.length) {
    showState("table-devices", "No devices registered.");
    return;
  }
  const rows = devices.map((d) => {
    const tr = document.createElement("tr");
    const tdCode = document.createElement("td");
    const codeEl = document.createElement("code");
    codeEl.textContent = d.device_code;
    tdCode.appendChild(codeEl);
    // Tipo (PRO/Home)
    if (d.device_is_professional) {
      const proBadge = document.createElement("span");
      proBadge.innerHTML = badge("Pro", "badge-pro");
      tdCode.appendChild(proBadge);
    } else {
      const homeBadge = document.createElement("span");
      homeBadge.innerHTML = badge("Home", "badge-home");
      tdCode.appendChild(homeBadge);
    }
    // Owener
    tr.appendChild(tdCode);
    const tdOwner = document.createElement("td");
    if (d.user_account_name) {
      tdOwner.appendChild(document.createTextNode(d.user_account_name));
      tdOwner.appendChild(document.createElement("br"));
      const emailEl = document.createElement("span");
      emailEl.className = "td-muted";
      emailEl.textContent = d.user_account_email;
      tdOwner.appendChild(emailEl);
    } else {
      const unassignedEl = document.createElement("span");
      unassignedEl.className = "td-muted";
      unassignedEl.textContent = "Unassigned";
      tdOwner.appendChild(unassignedEl);
    }
    tr.appendChild(tdOwner);
    // Activation code
    const tdActCode = document.createElement("td");
    if (d.activation_code) {
      const badgeSpan = document.createElement("span");
      badgeSpan.className = "badge badge-code";
      badgeSpan.textContent = d.activation_code;
      tdActCode.appendChild(badgeSpan);
    } else {
      const redeemedSpan = document.createElement("span");
      redeemedSpan.className = "td-muted";
      redeemedSpan.textContent = "Redeemed";
      tdActCode.appendChild(redeemedSpan);
    }

    tr.appendChild(tdActCode);

    // Última leitura do sensor
    const tdReading = document.createElement("td");
    tdReading.className = "td-muted";
    tdReading.textContent = `${fmtMoisture(d.sensor_reading_moisture_percent)} ${fmtDate(d.sensor_reading_recorded_at)}`;
    tr.appendChild(tdReading);

    // Status (online/offline)
    const tdStatus = document.createElement("td");
    const statusSpan = document.createElement("span");
    statusSpan.innerHTML = statusBadge(d.sensor_status);
    tdStatus.appendChild(statusSpan);
    tr.appendChild(tdStatus);

    // Ações (Delete)
    const tdActions = document.createElement("td");
    const actionDiv = document.createElement("div");
    actionDiv.className = "action-btns";

    const deleteBtn = document.createElement("button");
    deleteBtn.className = "btn-action btn-delete";
    deleteBtn.dataset.id = d.device_id;
    deleteBtn.dataset.action = "delete-device";
    deleteBtn.textContent = "Delete";
    actionDiv.appendChild(deleteBtn);

    tdActions.appendChild(actionDiv);
    tr.appendChild(tdActions);

    return tr;
  });

  setTableRows("table-devices", rows);
  attachSearch("search-devices", "table-devices");
}

// Render da tabela de plantas
function renderPlants(plants) {
  if (!plants.length) {
    showState("table-plants", "No plants in the system.");
    return;
  }

  const rows = plants.map((p) => {
    const tr = document.createElement("tr");
    // Nome
    const tdName = document.createElement("td");
    tdName.textContent = p.plant_name;
    tr.appendChild(tdName);
    // Tipo
    const tdType = document.createElement("td");
    tdType.textContent = p.plant_type_name ?? "—";
    tr.appendChild(tdType);
    // Owner
    const tdOwner = document.createElement("td");
    tdOwner.appendChild(document.createTextNode(p.user_account_name));
    tdOwner.appendChild(document.createElement("br"));
    const emailEl = document.createElement("span");
    emailEl.className = "td-muted";
    emailEl.textContent = p.user_account_email;
    tdOwner.appendChild(emailEl);
    tr.appendChild(tdOwner);
    // Device
    const tdDevice = document.createElement("td");
    tdDevice.className = "td-muted";
    const codeEl = document.createElement("code");
    codeEl.textContent = p.device_code;
    tdDevice.appendChild(codeEl);
    tr.appendChild(tdDevice);
    // Última leitura do sensor
    const tdMoisture = document.createElement("td");
    tdMoisture.textContent = fmtMoisture(p.sensor_reading_moisture_percent);
    tr.appendChild(tdMoisture);
    // Data de criação
    const tdCreated = document.createElement("td");
    tdCreated.className = "td-muted";
    tdCreated.textContent = fmtDate(p.created_at);
    tr.appendChild(tdCreated);
    // Ações (Delete)
    const tdActions = document.createElement("td");
    const actionDiv = document.createElement("div");
    actionDiv.className = "action-btns";

    const deleteBtn = document.createElement("button");
    deleteBtn.className = "btn-action btn-delete";
    deleteBtn.dataset.id = p.plant_id;
    deleteBtn.dataset.action = "delete-plant";
    deleteBtn.textContent = "Delete";
    actionDiv.appendChild(deleteBtn);

    tdActions.appendChild(actionDiv);
    tr.appendChild(tdActions);

    return tr;
  });

  setTableRows("table-plants", rows);
  attachSearch("search-plants", "table-plants");
}

// --- Search / filter ---

// Filtra as linhas da tabela pelo texto do input — verifica todas as células
function attachSearch(inputId, tableId) {
  const input = document.getElementById(inputId);
  input.value = ""; // limpa o campo a cada render
  input.addEventListener("input", () => {
    const q = input.value.toLowerCase().trim();
    document.querySelectorAll(`#${tableId} tbody tr`).forEach((row) => {
      const text = row.textContent.toLowerCase();
      row.style.display = !q || text.includes(q) ? "" : "none";
    });
  });
}

// --- Action handlers ---

function attachUserActions() {
  document
    .querySelector("#table-users tbody")
    .addEventListener("click", async (e) => {
      const btn = e.target.closest("[data-action]");
      if (!btn || btn.disabled) return;

      const userId = Number(btn.dataset.id);
      const action = btn.dataset.action;

      if (action === "promote") {
        if (!confirm("Promote this user to Admin?")) return;
        await handleRoleChange(userId, "admin");
      } else if (action === "demote") {
        if (!confirm("Demote this admin to User?")) return;
        await handleRoleChange(userId, "user");
      } else if (action === "delete") {
        if (
          !confirm(
            "Delete this user and all their data? This cannot be undone.",
          )
        )
          return;
        await handleDelete(userId);
      }
    });
}

function attachDeviceActions() {
  document
    .querySelector("#table-devices tbody")
    .addEventListener("click", async (e) => {
      const btn = e.target.closest("[data-action='delete-device']");
      if (!btn || btn.disabled) return;
      if (
        !confirm(
          "Delete this device, its plant and all sensor readings? This cannot be undone.",
        )
      )
        return;
      try {
        await deleteDevice(Number(btn.dataset.id));
        await loadDevices();
      } catch (err) {
        alert(`Error: ${err.message}`);
      }
    });
}

function attachPlantActions() {
  document
    .querySelector("#table-plants tbody")
    .addEventListener("click", async (e) => {
      const btn = e.target.closest("[data-action='delete-plant']");
      if (!btn || btn.disabled) return;
      if (
        !confirm(
          "Delete this plant and its sensor readings? The device will remain. This cannot be undone.",
        )
      )
        return;
      try {
        await deletePlant(Number(btn.dataset.id));
        await loadPlants();
      } catch (err) {
        alert(`Error: ${err.message}`);
      }
    });
}

async function handleRoleChange(userId, role) {
  try {
    await updateRole(userId, role);
    await loadUsers();
  } catch (err) {
    alert(`Error: ${err.message}`);
  }
}

async function handleDelete(userId) {
  try {
    await deleteUser(userId);
    await loadUsers();
  } catch (err) {
    alert(`Error: ${err.message}`);
  }
}

// --- Data loaders ---

async function loadUsers() {
  showState("table-users", "Loading…");
  try {
    const data = await getUsers();
    renderUsers(data.users ?? []);
  } catch (err) {
    showState("table-users", `Error: ${err.message}`);
  }
}

async function loadDevices() {
  showState("table-devices", "Loading…");
  try {
    const data = await getDevices();
    renderDevices(data.devices ?? []);
  } catch (err) {
    showState("table-devices", `Error: ${err.message}`);
  }
}

async function loadPlants() {
  showState("table-plants", "Loading…");
  try {
    const data = await getPlants();
    renderPlants(data.plants ?? []);
  } catch (err) {
    showState("table-plants", `Error: ${err.message}`);
  }
}

// --- Tabs ---

const loaders = { users: loadUsers, devices: loadDevices, plants: loadPlants };

function switchTab(name) {
  document
    .querySelectorAll(".admin-tab")
    .forEach((t) => t.classList.toggle("active", t.dataset.tab === name));
  document
    .querySelectorAll(".tab-content")
    .forEach((c) => c.classList.toggle("hidden", c.id !== `tab-${name}`));

  // Carrega os dados da tab se ainda não tiver carregado (ou recarrega ao voltar à tab)
  loaders[name]();
}

// Tabs
document.querySelectorAll(".admin-tab").forEach((btn) => {
  btn.addEventListener("click", () => switchTab(btn.dataset.tab));
});

// Logout
document.getElementById("btn-logout").addEventListener("click", logout);

attachUserActions();
attachDeviceActions();
attachPlantActions();
// Carrega a primeira tab
switchTab("users");
