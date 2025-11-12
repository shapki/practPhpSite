const messageContent = document.getElementById('messageContent');
const messageModal = document.getElementById('messageModal');

// Функция открытия/закрытия окна
function openModal(modal) {
    document.getElementById(modal).style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal(modal) {
    document.getElementById(modal).style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Функция показа сообщения
function showMessage(message, isError = false, redirectUrl = null) {
    console.log(isError ? 'Ошибка:' : 'Успех:', message);
    const messageContent = document.getElementById('messageContent');
    const messageHeader = document.querySelector('#messageModal .modal-header');
    
    if (messageContent && messageHeader) {
        messageContent.innerHTML = `<p class="${isError ? 'error' : 'success'}">${message}</p>`;
        messageHeader.textContent = isError ? 'Ошибка' : 'Успех';
        openModal('messageModal');
        
        if (redirectUrl) {
            const messageOkBtn = document.getElementById('messageOkBtn');
            const originalText = messageOkBtn.textContent;
            messageOkBtn.textContent = 'Перейти';
            
            const newOkBtn = messageOkBtn.cloneNode(true);
            messageOkBtn.parentNode.replaceChild(newOkBtn, messageOkBtn);
            
            newOkBtn.addEventListener('click', function() {
                closeModal('messageModal');
                window.location.href = redirectUrl;
            });
            
            setTimeout(() => {
                closeModal('messageModal');
                window.location.href = redirectUrl;
            }, 100);
        } else {
            const messageOkBtn = document.getElementById('messageOkBtn');
            const newOkBtn = messageOkBtn.cloneNode(true);
            messageOkBtn.parentNode.replaceChild(newOkBtn, messageOkBtn);
            
            newOkBtn.addEventListener('click', function() {
                closeModal('messageModal');
            });
        }
    }
}

/*
document.getElementById('messageOkBtn').addEventListener('click', function() {
    closeModal('messageModal');
});*/