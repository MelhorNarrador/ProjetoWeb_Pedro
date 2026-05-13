// Helpers para abrir/fechar modais (toggle da classe .hidden no overlay)

// Mostra o modal com o id dado
export function openModal(id) {
  document.getElementById(id).classList.remove("hidden");
}

// Esconde o modal com o id dado
export function closeModal(id) {
  document.getElementById(id).classList.add("hidden");
}

// Liga os botões de fechar (✕ e Cancel) + click no fundo para fechar modais
// onClose é um callback opcional chamado quando um modal é fechado
export function setupModalClosers(onClose) {
  // Botões ✕ e botões Cancel — qualquer elemento com data-target fecha o modal
  document.querySelectorAll(".modal-close, .btn-cancel").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.target;
      if (!id) return;
      closeModal(id);
      onClose?.(id);
    });
  });

  // Clique no fundo escurecido (overlay) também fecha o modal
  document.querySelectorAll("[id$='-modal-overlay']").forEach((overlay) => {
    overlay.addEventListener("click", (e) => {
      // Só fecha se o clique foi mesmo no overlay (não no conteúdo)
      if (e.target === overlay) {
        closeModal(overlay.id);
        onClose?.(overlay.id);
      }
    });
  });
}
