const registerModal = document.getElementById('registerModal');
const forgotModal = document.getElementById('forgotModal');
const registerBtn = document.getElementById('registerBtn');
const forgotBtn = document.getElementById('forgotBtn');
const cancelRegister = document.getElementById('cancelRegister');
const cancelForgot = document.getElementById('cancelForgot');
const messageOkBtn = document.getElementById('messageOkBtn');
const closeButtons = document.querySelectorAll('.modal-close');

// Формы
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const forgotForm = document.getElementById('forgotForm');

// Обработчики открытия окон
registerBtn.addEventListener('click', () => openModal('registerModal'));
forgotBtn.addEventListener('click', () => openModal('forgotModal'));

// Обработчики закрытия окон
cancelRegister.addEventListener('click', () => closeModal('registerModal'));
cancelForgot.addEventListener('click', () => closeModal('forgotModal'));

closeButtons.forEach(button => {
    button.addEventListener('click', function() {
        const modal = this.closest('.modal-overlay');
        closeModal(modal);
    });
});

// Валидация формы регистрации
const passwordInput = document.getElementById('reg_password');
const confirmInput = document.getElementById('confirm_password');
const loginInput = document.getElementById('reg_login');
const firstNameInput = document.getElementById('first_name');
const lastNameInput = document.getElementById('last_name');
const passwordError = document.getElementById('passwordError');
const confirmError = document.getElementById('confirmError');
const loginError = document.getElementById('loginError');
const fullNameError = document.getElementById('fullNameError');

// Функция для показа/скрытия индикатора загрузки
function showFormLoading(form, show = true) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.getAttribute('data-original-text') || submitBtn.textContent;
    
    if (show) {
        submitBtn.setAttribute('data-original-text', originalText);
        submitBtn.textContent = 'Загрузка...';
        submitBtn.disabled = true;
    } else {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
}

registerForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    let isValid = true;
    
    if (loginInput.value.length < 4) {
        loginError.textContent = 'Не менее 4 символов';
        loginError.style.display = 'block';
        isValid = false;
    } else {
        loginError.style.display = 'none';
    }
    
    if (passwordInput.value.length < 6) {
        passwordError.textContent = 'Не менее 6 символов';
        passwordError.style.display = 'block';
        isValid = false;
    } else {
        passwordError.style.display = 'none';
    }
    
    if (passwordInput.value !== confirmInput.value) {
        confirmError.textContent = 'Пароли не совпадают';
        confirmError.style.display = 'block';
        isValid = false;
    } else {
        confirmError.style.display = 'none';
    }
    
    if (firstNameInput.value.length < 1 || lastNameInput.value.length < 1) {
        fullNameError.textContent = 'Не должно быть пустым';
        fullNameError.style.display = 'block';
        isValid = false;
    } else {
        fullNameError.style.display = 'none';
    }
    
    if (isValid) {
        submitForm(registerForm);
    }
});

// Обработка отправки формы входа
loginForm.addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(loginForm);
});

// Обработка отправки формы восстановления пароля
forgotForm.addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(forgotForm);
});

// Отправка формы
function submitForm(form) {
    showFormLoading(form, true);
    
    const formData = new FormData(form);
    
    fetch('/site_modules/auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Ошибка сети: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (form.id === 'loginForm' || form.id === 'registerForm') {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.href = 'userpage.php';
                }
            } else {
                showMessage('Успех', data.message);
                if (form.id === 'registerForm') {
                    closeModal(registerModal);
                    form.reset();
                } else if (form.id === 'forgotForm') {
                    closeModal(forgotModal);
                    form.reset();
                }
            }
        } else {
            showMessage(data.message, true);
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showMessage('Произошла неизвестная ошибка при отправке формы', true);
    })
    .finally(() => {
        showFormLoading(form, false);
    });
}