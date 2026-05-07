export function openModal(id) {
  document.getElementById(id).classList.remove("hidden");
}

export function closeModal(id) {
  document.getElementById(id).classList.add("hidden");
}

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

  // Clique no fundo
  document.querySelectorAll("[id$='-modal-overlay']").forEach((overlay) => {
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) {
        closeModal(overlay.id);
        onClose?.(overlay.id);
      }
    });
  });
}
