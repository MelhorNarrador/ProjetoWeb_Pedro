// Lógica da página de login: submete credenciais e redireciona para o dashboard
import { login } from "./apiClient.js";

// Se o user já fez "Remember me" antes, preenche o email automaticamente
const savedEmail = localStorage.getItem("rememberEmail");
if (savedEmail) {
  document.getElementById("email").value = savedEmail;
  document.getElementById("remember-me").checked = true;
}

// Handler do submit do form de login
document.getElementById("login-form").addEventListener("submit", async (e) => {
  e.preventDefault();
  const errorMsg = document.getElementById("error-msg");
  errorMsg.textContent = "";

  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;

  // Chama o endpoint de login (catch para não rebentar se o servidor estiver down)
  const data = await login(email, password).catch(() => null);
  if (data?.success) {
    // Guarda/limpa o email conforme o checkbox "Remember me"
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
