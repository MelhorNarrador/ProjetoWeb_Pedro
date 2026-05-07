import { login } from "./apiClient.js";
const savedEmail = localStorage.getItem("rememberEmail");
if (savedEmail) {
  document.getElementById("email").value = savedEmail;
  document.getElementById("remember-me").checked = true;
}

document.getElementById("login-form").addEventListener("submit", async (e) => {
  e.preventDefault();
  const errorMsg = document.getElementById("error-msg");
  errorMsg.textContent = "";

  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;

  const data = await login(email, password).catch(() => null);
  if (data?.success) {
    if (document.getElementById("remember-me").checked) {
      localStorage.setItem("rememberEmail", email);
    } else {
      localStorage.removeItem("rememberEmail");
    }
    window.location.href = "dashboard.php";
  } else {
    errorMsg.textContent = data?.message ?? "Could not connect to server.";
  }
});
