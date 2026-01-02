document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('registerForm').addEventListener('submit', function(event) {
      let password = document.getElementById('dr_password').value;
      let confirmPassword = document.getElementById('confirm_password').value;

      if (password !== confirmPassword) {
          event.preventDefault();
          alert("Passwords do not match.");
      }
  });
});