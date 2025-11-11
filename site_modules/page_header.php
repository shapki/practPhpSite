<div class="page-header" id="pageHeader">
    <div class="site-logo">MCM</div>
    <div class="user-info" id="userInfo">
        <div class="loading-spinner"></div>
    </div>
</div>

<script>
    function loadHeader() {
        fetch('site_modules/ajax_header.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сети: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                updateHeader(data);
            })
            .catch(error => {
                console.error('Ошибка загрузки header:', error);
                document.getElementById('userInfo').innerHTML = `
                    <div class="auth-buttons">
                        <button type="button" class="style3-btn" onclick="window.location.href='index.php'">
                            Вход/Регистрация
                        </button>
                    </div>
                `;
            });
    }

    function updateHeader(userData) {
        const userInfo = document.getElementById('userInfo');
        
        if (userData.logged_in) {
            // Пользователь авторизован
            const firstName = userData.first_name || '';
            const lastName = userData.last_name || '';
            const avatarInitials = (firstName.charAt(0) + (lastName.charAt(0) || '')).toUpperCase();
            const displayName = firstName + ' ' + (lastName.charAt(0) || '') + '.';
            
            userInfo.innerHTML = `
                <div class="user-avatar">
                    ${userData.foto ? 
                        `<img src="${userData.foto}" alt="Аватар" onerror="this.style.display='none'; this.parentNode.innerHTML='${avatarInitials}'">` : 
                        avatarInitials
                    }
                </div>
                <div class="user-name">${displayName}</div>
                <div class="dropdown-menu">
                    <a href="userpage.php?id=${userData.user_id}">Открыть профиль</a>
                    <a href="#" onclick="logoutUser(); return false;">Выход</a>
                </div>
            `;
            
            initDropdownMenu();
        } else {
            // Пользователь не авторизован
            userInfo.innerHTML = `
                <div class="auth-buttons">
                    <button type="button" class="style3-btn" onclick="window.location.href='index.php'">
                        Вход/Регистрация
                    </button>
                </div>
            `;
        }
    }

    function logoutUser() {
        fetch('site_modules/logout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'ajax=true'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                loadHeader();
                showMessage('Вы успешно вышли из системы', false);
                if (window.location.pathname !== '/index.php' && 
                    window.location.pathname !== '/' &&
                    !window.location.pathname.includes('index.php')) {
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 100);
                }
            } else {
                showMessage('Ошибка при выходе', true);
            }
        })
        .catch(error => {
            console.error('Ошибка выхода:', error);
            showMessage('Ошибка при выходе', true);
        });
    }

    function initDropdownMenu() {
        const userInfo = document.querySelector('.user-info');
        if (userInfo) {
            userInfo.addEventListener('mouseenter', function() {
                const dropdown = this.querySelector('.dropdown-menu');
                if (dropdown) {
                    dropdown.style.display = 'block';
                }
            });
            
            userInfo.addEventListener('mouseleave', function() {
                const dropdown = this.querySelector('.dropdown-menu');
                if (dropdown) {
                    setTimeout(() => {
                        if (!this.matches(':hover')) {
                            dropdown.style.display = 'none';
                        }
                    }, 200);
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadHeader();
    });

    function refreshHeader() {
        loadHeader();
    }
</script>