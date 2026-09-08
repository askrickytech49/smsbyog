const passwordInput = document.getElementById('new_password');
const passwordInput2 = document.getElementById('confirm_password');
  const togglePassword = document.getElementById('togglePassword');
  const togglePassword2 = document.getElementById('togglePassword2');

  togglePassword.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    this.classList.toggle('bi-eye');
    this.classList.toggle('bi-eye-slash');
  });
togglePassword2.addEventListener('click', function () {
    const type = passwordInput2.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput2.setAttribute('type', type);
    
    this.classList.toggle('bi-eye');
    this.classList.toggle('bi-eye-slash');
  });  