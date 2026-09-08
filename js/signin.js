document.addEventListener("DOMContentLoaded", function () {

  const passwordInput = document.getElementById("password");
  const togglePassword = document.getElementById("togglePassword");

  if (togglePassword && passwordInput) {
    togglePassword.addEventListener("click", function () {

      const type =
        passwordInput.getAttribute("type") === "password"
          ? "text"
          : "password";

      passwordInput.setAttribute("type", type);

      this.classList.toggle("bi-eye");
      this.classList.toggle("bi-eye-slash");

    });
  }

});