// Helpers para abrir/fechar modais (toggle da classe .hidden no overlay)

// Callback de cleanup registado por setupModalClosers; corre em todos os closeModal()
let onCloseCallback = null;

// Mostra o modal com o id dado
export function openModal(id) {
  document.getElementById(id).classList.remove("hidden");
}

// Esconde o modal e corre o callback de cleanup (se registado).
// Funciona tanto chamado por código (após sucesso) como pelos botões X/Cancel.
export function closeModal(id) {
  document.getElementById(id).classList.add("hidden");
  onCloseCallback?.(id);
}

// Liga os botões de fechar (✕ e Cancel) + click no fundo para fechar modais.
// Regista onClose como cleanup global (corre em qualquer chamada a closeModal).
export function setupModalClosers(onClose) {
  onCloseCallback = onClose;

  // Botões ✕ e botões Cancel — qualquer elemento com data-target fecha o modal
  document.querySelectorAll(".modal-close, .btn-cancel").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.target;
      if (!id) return;
      closeModal(id);
    });
  });

  // Clique no fundo escurecido (overlay) também fecha o modal
  document.querySelectorAll("[id$='-modal-overlay']").forEach((overlay) => {
    overlay.addEventListener("click", (e) => {
      // Só fecha se o clique foi mesmo no overlay (não no conteúdo)
      if (e.target === overlay) {
        closeModal(overlay.id);
      }
    });
  });
}
