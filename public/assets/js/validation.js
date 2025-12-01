// Función para validar solo el campo Nombre
function validarNombre(inputName) {
    const nombre = document.getElementById(inputName).value.trim();
    const errorElement = document.getElementById('errorNombre');
    let esValido = true;

    if (nombre === '') {
        errorElement.textContent = 'El nombre es obligatorio.';
        esValido = false;
    } else if (nombre.length < 3) {
        errorElement.textContent = 'El nombre debe tener al menos 3 caracteres.';
        esValido = false;
    } else {
        errorElement.textContent = ''; // Limpiar si es válido
    }

    return esValido;
}

function validarLocation(inputName, numCaracteres) {
    const nombre = document.getElementById(inputName).value.trim();
    const errorElement = document.getElementById('errorTextLocation');
    let esValido = true;

    if (nombre === '') {
        errorElement.textContent = 'El campo es obligatorio.';
        esValido = false;
    } else if (nombre.length < numCaracteres) {
        errorElement.textContent = 'El campo debe tener al menos ' + numCaracteres + ' caracteres.';
        esValido = false;
    } else {
        errorElement.textContent = ''; // Limpiar si es válido
    }

    return esValido;
}

function validarText(inputName, numCaracteres, errorElementId) {
    const text = document.getElementById(inputName).value.trim();
    const errorElement = document.getElementById(errorElementId);
    let esValido = true;

    if (text === '') {
        errorElement.textContent = 'El campo es obligatorio.';
        esValido = false;
    } else if (text.length < numCaracteres) {
        errorElement.textContent = 'El campo debe tener al menos ' + numCaracteres + ' caracteres.';
        esValido = false;
    } else {
        errorElement.textContent = ''; // Limpiar si es válido
    }

    return esValido;
}

// Función para validar solo el campo Email
function validarEmail(inputName) {
    const email = document.getElementById(inputName).value.trim();
    const errorElement = document.getElementById('errorEmail');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    let esValido = true;

    if (email === '') {
        errorElement.textContent = 'El email es obligatorio.';
        esValido = false;
    } else if (!emailRegex.test(email)) {
        errorElement.textContent = 'Por favor, ingrese un email válido.';
        esValido = false;
    } else {
        errorElement.textContent = ''; // Limpiar si es válido
    }

    return esValido;
}

// Función para validar solo el campo Teléfono
function validarTelefono(inputName) {
    const telefono = document.getElementById(inputName).value.trim();
    const errorElement = document.getElementById('errorTelefono');
    let esValido = true;

    // RegEx para números de 7 a 15 dígitos.
    // Permite espacios, guiones, paréntesis y un signo '+' inicial opcional.
    // Es flexible para formatos internacionales y locales.
    const telefonoRegex = /^\+?(\d[\s-]?){7,15}$/;

    if (telefono === '') {
        errorElement.textContent = 'El teléfono es obligatorio.';
        esValido = false;
    } else if (!telefonoRegex.test(telefono)) {
        errorElement.textContent = 'Ingrese un número de teléfono válido (7 a 15 dígitos).';
        esValido = false;
    } else {
        errorElement.textContent = ''; // Limpiar si es válido
    }

    return esValido;
}


// Función principal: valida todos los campos para el envío final
function validarFormulario() {
    // Llama y combina los resultados de la validación de cada campo
    const nombreValido = validarNombre();
    const emailValido = validarEmail();

    // 💡 NUEVO: Llama a la validación del teléfono
    const telefonoValido = validarTelefono();

    // El formulario solo se envía si TODOS los campos son válidos
    return nombreValido && emailValido && telefonoValido;
}