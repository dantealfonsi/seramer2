/**
 *  Pages Authentication
 */

'use strict';
const formAuthentication = document.querySelector('#formAuthentication');

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    // Form validation for Login
    if (formAuthentication) {
      const fv = FormValidation.formValidation(formAuthentication, {
        fields: {
          username: {
            validators: {
              notEmpty: {
                message: 'Por favor ingrese su usuario'
              },
              stringLength: {
                min: 4,
                message: 'El usuario debe tener más de 4 caracteres'
              }
            }
          },
          password: {
            validators: {
              notEmpty: {
                message: 'Por favor ingrese su contraseña'
              },
              stringLength: {
                min: 6,
                message: 'La contraseña debe tener más de 6 caracteres'
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: '.mb-5'
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: instance => {
          instance.on('plugins.message.placed', function (e) {
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        }
      })
        .on('core.form.valid', function () {
          const submitButton = formAuthentication.querySelector('[type="submit"]');
          if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Iniciando...';
          }

          const formData = new FormData(formAuthentication);

          fetch(formAuthentication.action, {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                window.location.href = data.redirect;
              } else {
                if (submitButton) {
                  submitButton.disabled = false;
                  submitButton.innerHTML = 'Iniciar Sesión';
                }

                Swal.fire({
                  icon: 'error',
                  title: 'Error de autenticación',
                  text: data.message,
                  customClass: {
                    confirmButton: 'btn btn-primary'
                  },
                  buttonsStyling: false
                });
              }
            })
            .catch(error => {
              console.error('Error:', error);
              if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Iniciar Sesión';
              }

              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error inesperado al procesar la solicitud.',
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
              });
            });
        });

    }

    //  Two Steps Verification
    const numeralMask = document.querySelectorAll('.numeral-mask');

    // Verification masking
    if (numeralMask.length) {
      numeralMask.forEach(e => {
        new Cleave(e, {
          numeral: true
        });
      });
    }
  })();
});

