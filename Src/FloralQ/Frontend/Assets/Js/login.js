document
  .getElementById("login-form")
  .addEventListener("submit", function (event) {
    event.preventDefault();

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const errorMsg = document.getElementById("error-msg");

    fetch("../../Backend/Auth/login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, password }),
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (data.success) {
          window.location.href = "dashboard.html";
        } else {
          errorMsg.textContent = data.message;
        }
      })
      .catch(function () {
        errorMsg.textContent = "Could not connect to server.";
      });
  });
