// Onboarding tour usando driver.js
// Tour action-driven: cada step avança quando o user faz a ação real
// (em vez de carregar em Next), o que ensina o user a usar o site de verdade
import { driver } from "https://cdn.jsdelivr.net/npm/driver.js@1.3.0/+esm";

// Chave do localStorage que marca o tour como concluído (não voltar a mostrar)
const STORAGE_KEY = "onboardingDone";

// Instância partilhada do driver.js (criada na primeira chamada a startTour)
let driverObj = null;

// Definição dos passos do tour. Index = posição no array.
const STEPS = [
  {
    // 0
    element: "#settings-btn",
    popover: {
      title: "Open settings",
      description: "Click the gear icon to access settings.",
      showButtons: ["close"],
    },
    onHighlightStarted: (el) => attachClickToAdvance(el),
  },
  {
    // 1
    element: "#open-redeem",
    popover: {
      title: "Redeem your sensor",
      description:
        "Click here to associate your sensor with your account. Or skip if you already have one.",
      showButtons: ["next", "close"],
      nextBtnText: "Skip",
      onNextClick: () => {
        document.getElementById("settings-dropdown").classList.add("hidden");
        driverObj.moveTo(3);
      },
    },
    onHighlightStarted: (el) => {
      // Garantir que o dropdown está aberto
      document.getElementById("settings-dropdown").classList.remove("hidden");
      attachClickToAdvance(el);
    },
  },
  {
    // 2 avança via custom event "redeem-success"
    element: "#redeem-form",
    popover: {
      title: "Enter activation code",
      description:
        "Type the code that came with your sensor, then click Redeem.",
      showButtons: ["close"],
    },
  },
  {
    // 3
    element: "#open-add-plant",
    popover: {
      title: "Add your first plant",
      description: "Now associate a plant with your sensor.",
      showButtons: ["close"],
    },
    onHighlightStarted: (el) => attachClickToAdvance(el),
  },
  {
    // 4 — opcional, user pode saltar
    element: ".image-picker",
    popover: {
      title: "Plant image (optional)",
      description:
        "Optionally pick a photo of your plant. Click Next to skip.",
    },
  },
  {
    // 5
    element: "#plant-name",
    popover: {
      title: "Plant name",
      description: "Give your plant a friendly name.",
    },
  },
  {
    // 5
    element: "#plant-location",
    popover: {
      title: "Location",
      description: "Where will this plant live? (e.g., Living Room)",
    },
  },
  {
    // 6
    element: "#plant-type",
    popover: {
      title: "Plant type",
      description: "Pick the species, this sets the ideal moisture range.",
    },
  },
  {
    // 7
    element: "#plant-device",
    popover: {
      title: "Sensor",
      description: "Pick the device that will monitor this plant.",
    },
  },
  {
    // 8 opcional, user pode saltar
    element: ".checkbox-group",
    popover: {
      title: "Plant is grown (optional)",
      description:
        "Check this if your plant is already mature. Otherwise click Next to skip. It will affect the mascot on your FloralQ Home device screen",
    },
  },
  {
    // 9  avança via custom event "plant-created"
    element: "#add-plant-form .submit-btn",
    popover: {
      title: "Create plant",
      description: "Click Create Plant to save.",
      showButtons: ["close"],
    },
  },
  {
    // 10
    element: ".plant-card",
    popover: {
      title: "Your plant card",
      description:
        "Each card shows the current state. Click it to see full details.",
      showButtons: ["close"],
    },
    onHighlightStarted: (el) => attachClickToAdvance(el),
  },
  {
    // 11
    element: ".modal-moisture",
    popover: {
      title: "Current moisture",
      description:
        "The percentage shows how full the comfort range is, green good, red dry, blue overwatered.",
    },
  },
  {
    // 12
    element: ".modal-prediction",
    popover: {
      title: "Drying prediction",
      description:
        "We estimate when your plant will need water based on recent readings.",
    },
  },
  {
    // 13
    element: ".modal-confidence",
    popover: {
      title: "Confidence",
      description:
        "How reliable the prediction is. More readings = higher confidence.",
    },
  },
  {
    // 14
    element: "#settings-btn",
    popover: {
      title: "Configure alerts",
      description:
        "Open Account in settings to set alert thresholds, change password, and more.",
    },
  },
  {
    // 15 final
    popover: {
      title: "You're all set! 🌱",
      description:
        "Happy growing! Your sensor will start sending readings shortly.",
    },
  },
];

// Anexa um listener click que avança o tour, removível depois de disparar
function attachClickToAdvance(el) {
  const handler = () => {
    el.removeEventListener("click", handler);
    // Pequeno delay para a UI atualizar (ex.: dropdown abrir, modal abrir)
    setTimeout(() => driverObj?.moveNext(), 150);
  };
  el.addEventListener("click", handler);
}

// Cria a instância do driver.js com os steps + opções globais
function buildDriver() {
  driverObj = driver({
    showProgress: true,
    progressText: "{{current}} of {{total}}",
    nextBtnText: "Next",
    prevBtnText: "Back",
    doneBtnText: "Done",
    showButtons: ["next", "previous", "close"],
    allowClose: true,
    onDestroyed: () => {
      window.__tutorialActive = false;
      finishOnboarding();
    },
    steps: STEPS,
  });
}

// Marca o onboarding como concluído para não voltar a aparecer
function finishOnboarding() {
  localStorage.setItem(STORAGE_KEY, "true");
}

// Inicia o tour. Define a flag global usada pelo dashboard para evitar fechar dropdowns
function startTour() {
  if (!driverObj) buildDriver();
  window.__tutorialActive = true;
  driverObj.drive();
}

// Custom events para avançar steps que dependem de respostas do backend
document.addEventListener("redeem-success", () => {
  if (driverObj?.isActive() && driverObj.getActiveIndex() === 2) {
    setTimeout(() => driverObj.moveNext(), 250);
  }
});

document.addEventListener("plant-created", () => {
  if (driverObj?.isActive() && driverObj.getActiveIndex() === 10) {
    setTimeout(() => driverObj.moveNext(), 250);
  }
});

// Mostra o popup inicial com "Start tour" / "Skip" antes de começar o driver.js
function showWelcomeModal() {
  const overlay = document.getElementById("welcome-modal-overlay");
  overlay.classList.remove("hidden");

  document.getElementById("welcome-start-btn").onclick = () => {
    overlay.classList.add("hidden");
    startTour();
  };

  document.getElementById("welcome-skip-btn").onclick = () => {
    overlay.classList.add("hidden");
    finishOnboarding();
  };
}

// API pública

// Dispara o welcome popup só se for primeira visita E user sem plantas
export function maybeStartOnboarding(plantsCount) {
  if (localStorage.getItem(STORAGE_KEY)) return;
  if (plantsCount > 0) return;
  showWelcomeModal();
}

// Re-disparar manualmente via botão "Replay"
export function replayOnboarding() {
  showWelcomeModal();
}
