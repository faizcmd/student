
  document.addEventListener('DOMContentLoaded', function () {
    const fields = Array.from(document.querySelectorAll('input, select')).filter(el => el.name && el.type !== 'hidden');

    function validateField(el) {
      if (!el.checkValidity()) {
        el.classList.add('is-invalid');
      } else {
        el.classList.remove('is-invalid');
      }
    }

    const passwordField = document.querySelector('#password');
    if (passwordField) {
      passwordField.addEventListener('input', function () {
        validateField(passwordField);
      });
    }

    const mobileInput = document.querySelector('input[name="mobile"]');
    if (mobileInput) {
      mobileInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        validateField(this);
        if (this.value.length === 10) {
          const nextField = fields[fields.indexOf(this) + 1];
          if (nextField && nextField.name !== 'email') nextField.focus();
        }
      });
    }

    const aadhaarInput = document.querySelector('input[name="aadhaar_number"]');
    if (aadhaarInput) {
      aadhaarInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);
        validateField(this);
        if (this.value.length === 12) {
          const nextField = fields[fields.indexOf(this) + 1];
          if (nextField) nextField.focus();
        }
      });
    }

    const emailInput = document.querySelector('input[name="email"]');
    if (emailInput) {
      emailInput.addEventListener('input', function () {
        validateField(this);
      });
    }

    const togglePasswordBtn = document.querySelector('#togglePassword');
    if (togglePasswordBtn && passwordField) {
      togglePasswordBtn.addEventListener('click', function () {
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
      });
    }

    fields.forEach((el, index) => {
      if (['name', 'fee', 'email'].includes(el.name)) {
        el.addEventListener('keydown', function (e) {
          if ((e.key === 'Enter' || e.key === 'Tab') && el.checkValidity()) {
            e.preventDefault();
            if (index < fields.length - 1) {
              fields[index + 1].focus();
            }
          }
        });
      } else {
        el.addEventListener('input', function () {
          validateField(el);
          if (el.checkValidity() && index < fields.length - 1) {
            fields[index + 1].focus();
          }
        });
      }
    });

    // Bootstrap-like client-side form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }

        const passwordField = form.querySelector('input[name="password"]');
        if (passwordField && !passwordField.checkValidity()) {
          passwordField.classList.add('is-invalid');
        }

        form.classList.add('was-validated');
      }, false);
    });
  });

  function toggleMenu() {
    const nav = document.getElementById("navLinks");
    if (nav) {
      nav.style.display = nav.style.display === "flex" ? "none" : "flex";
      nav.style.flexDirection = "column";
      nav.style.gap = "10px";
    }
  }

