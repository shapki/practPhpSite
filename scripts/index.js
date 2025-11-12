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
const regEmailInput = document.getElementById('registr_email');
const passwordError = document.getElementById('passwordError');
const confirmError = document.getElementById('confirmError');
const loginError = document.getElementById('loginError');
const fullNameError = document.getElementById('fullNameError');
const regEmailError = document.getElementById('registr_email_error');

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

function validateEmail() {
    const email = regEmailInput.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email === '') {
        regEmailError.textContent = 'Почта не должена быть пустой';
        regEmailError.style.display = 'block';
        return false;
    } else if (!emailRegex.test(email)) {
        regEmailError.textContent = 'Введите корректный почтовый адрес';
        regEmailError.style.display = 'block';
        return false;
    } else {
        regEmailError.style.display = 'none';
        return true;
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

    if (!validateEmail()) {
        isValid = false;
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
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Невалидный JSON ответ:', text);
                if (text.includes('Fatal error') || text.includes('Parse error')) {
                    throw new Error('Ошибка на сервере');
                }
                throw new Error('Сервер вернул невалидный ответ');
            }
        });
    })
    .then(data => {        
        if (data.success) {
            if (data.redirect) {
                showMessage(data.message, false, data.redirect);
            } else {
                showMessage(data.message, false);
                
                if (form.id === 'forgotForm') {
                    setTimeout(() => {
                        closeModal('forgotModal');
                        form.reset();
                    }, 1500);
                } else if (form.id === 'registerForm') {
                    setTimeout(() => {
                        closeModal('registerModal');
                        form.reset();
                    }, 1500);
                }
            }
        } else {
            showMessage(data.message, true);
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showMessage('Произошла ошибка при отправке формы: ' + error.message, true);
    })
    .finally(() => {
        showFormLoading(form, false);
    });
}