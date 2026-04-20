import { login } from "./apiClient.js";

document.getElementById("login-form").addEventListener("submit", async (e) => {
  e.preventDefault();
  const errorMsg = document.getElementById("error-msg");
  errorMsg.textContent = "";

  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;

  const data = await login(email, password).catch(() => null);
  if (data?.success) {
    window.location.href = "dashboard.php";
  } else {
    errorMsg.textContent = data?.message ?? "Could not connect to server.";
  }
});
