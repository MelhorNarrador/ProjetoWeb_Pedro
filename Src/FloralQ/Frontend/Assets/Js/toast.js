// Sistema de toasts (mensagens flutuantes de sucesso/erro/aviso)
// Tempo em ms que cada toast fica visível antes de desaparecer
const TOAST_DURATION = 3500;

// Cria o container dos toasts no body se ainda não existir
function ensureContainer() {
  let container = document.getElementById("toast-container");
  if (!container) {
    container = document.createElement("div");
    container.id = "toast-container";
    document.body.appendChild(container);
  }
  return container;
}

// Mostra um toast. type pode ser "info", "success", "error" (controla a cor via CSS)
export function showToast(message, type = "info") {
  const container = ensureContainer();
  const toast = document.createElement("div");
  toast.className = `toast toast-${type}`;
  toast.textContent = message;
  container.appendChild(toast);

  // Slide-in: dispara a transição CSS no próximo frame
  requestAnimationFrame(() => toast.classList.add("toast-show"));

  // Auto-dismiss: tira a classe e remove do DOM no fim da transição
  setTimeout(() => {
    toast.classList.remove("toast-show");
    toast.addEventListener("transitionend", () => toast.remove(), {
      once: true,
    });
  }, TOAST_DURATION);
}
