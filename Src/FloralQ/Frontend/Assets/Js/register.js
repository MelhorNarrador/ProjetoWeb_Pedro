document
  .getElementById("register-form")
  .addEventListener("submit", function (event) {
    event.preventDefault();

    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const repeatPassword = document.getElementById("repeat-password").value;
    const errorMsg = document.getElementById("error-msg");

    // Verifica se as passwords coincidem antes de enviar ao servidor
    if (password !== repeatPassword) {
      errorMsg.textContent = "Passwords do not match.";
      return;
    }

    fetch("../../Backend/Auth/register.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ name, email, password }),
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (data.success) {
          window.location.href = "login.html";
        } else {
          errorMsg.textContent = data.message;
        }
      })
      .catch(function () {
        errorMsg.textContent = "Could not connect to server.";
      });
  });
