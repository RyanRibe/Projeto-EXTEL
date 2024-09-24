const password = document.getElementById('new_password');
const icon = document.getElementById('icon');
const currentPassword = document.getElementById('current_password');
const icon1 = document.getElementById('icon1');

function showHide(input, iconElement) {
    if (input.type === 'password') {
        input.setAttribute('type', 'text');
        iconElement.classList.add('hide');
    } else {
        input.setAttribute('type', 'password');
        iconElement.classList.remove('hide');
    }
}

// Adicione eventos para os ícones
icon.addEventListener('click', () => showHide(password, icon));
icon1.addEventListener('click', () => showHide(currentPassword, icon1));
