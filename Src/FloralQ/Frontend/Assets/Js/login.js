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

  // Desativar botão para impedir double-submit
  const submitBtn = e.target.querySelector('button[type="submit"]');
  submitBtn.disabled = true;

  try {
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    // Chama o endpoint de login (catch preserva a mensagem de erro do backend)
    const data = await login(email, password).catch((err) => ({
      success: false,
      message: err.message,
    }));
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
  } finally {
    submitBtn.disabled = false;
  }
});
