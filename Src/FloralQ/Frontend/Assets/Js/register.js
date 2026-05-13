// Lógica da página de registo: valida campos e cria conta nova
import { register } from "./apiClient.js";

document.getElementById("register-form").addEventListener("submit", async (e) => {
  e.preventDefault();
  const errorMsg = document.getElementById("error-msg");
  errorMsg.textContent = "";

  const name = document.getElementById("name").value;
  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;
  const repeatPassword = document.getElementById("repeat-password").value;

  // Validação local: passwords têm de coincidir antes de chamar o backend
  if (password !== repeatPassword) {
    errorMsg.textContent = "Passwords do not match.";
    return;
  }

  // Cria a conta no backend
  const data = await register(name, email, password).catch(() => null);
  if (data?.success) {
    // Sucesso → redireciona para o login
    window.location.href = "login.html";
  } else {
    errorMsg.textContent = data?.message ?? "Could not connect to server.";
  }
});
