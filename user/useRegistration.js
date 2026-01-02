document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById('registerForm');

  form.addEventListener('submit', function (event) {
      const phone = document.getElementById('phone').value;
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;

      // Phone number validation (only numbers, length 10)
      if (!/^\d{10}$/.test(phone)) {
          alert("Phone number must be 10 digits.");
          event.preventDefault();
      }

      // Password validation (at least 6 characters)
      if (password.length < 6) {
          alert("Password must be at least 6 characters long.");
          event.preventDefault();
      }

      // Confirm Password validation (matching passwords)
      if (password !== confirmPassword) {
          alert("Passwords do not match.");
          event.preventDefault();
      }
  });
});