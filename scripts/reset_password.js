document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.textContent;
    
    // Показываем индикатор загрузки
    submitBtn.textContent = 'Загрузка...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch('reset_password.php', {
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
            showMessage(data.message, false, data.redirect);
        } else {
            showMessage(data.message, true);
            // Возвращаем кнопку в исходное состояние при ошибке
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showMessage('Произошла ошибка при сбросе пароля: ' + error.message, true);
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

const newPassword = document.getElementById('new_password');
const confirmPassword = document.getElementById('confirm_password');

function validatePasswords() {
    if (newPassword.value && confirmPassword.value) {
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Пароли не совпадают');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
}

newPassword.addEventListener('input', validatePasswords);
confirmPassword.addEventListener('input', validatePasswords);