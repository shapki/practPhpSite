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
function showMessage(message, isError = false) {
    console.log(isError ? 'Ошибка:' : 'Успех:', message);
    const messageContent = document.getElementById('messageContent');
    const messageHeader = document.querySelector('#messageModal .modal-header');
    
    if (messageContent && messageHeader) {
        messageContent.innerHTML = `<p class="${isError ? 'error' : 'success'}">${message}</p>`;
        messageHeader.textContent = isError ? 'Ошибка' : 'Успех';
        openModal('messageModal');
    }
}

document.getElementById('messageOkBtn').addEventListener('click', function() {
    closeModal('messageModal');
});