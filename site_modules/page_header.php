<div class="page-header">
    <div class="site-logo">MCM</div>
    <div class="user-info">
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Если пользователь авторизован -->
            <div class="user-avatar">
                <?php if (!empty($_SESSION['foto'])): ?>
                    <img src="<?php echo htmlspecialchars($_SESSION['foto']); ?>" alt="Аватар">
                <?php else: ?>
                    <?php echo mb_substr($_SESSION['first_name'] ?? '', 0, 1, 'UTF-8') . mb_substr($_SESSION['last_name'] ?? '', 0, 1, 'UTF-8'); ?>
                <?php endif; ?>
            </div>
            <div class="user-name">
                <?php echo htmlspecialchars(($_SESSION['first_name'] ?? '') . ' ' . mb_substr($_SESSION['last_name'] ?? '', 0, 1, 'UTF-8') . '.'); ?>
            </div>
            <div class="dropdown-menu">
                <a href="userpage.php?id=<?php echo $_SESSION['user_id']; ?>">Открыть профиль</a>
                <a href="site_modules/logout.php">Выход</a>
            </div>
        <?php else: ?>
            <!-- Если пользователь не авторизован -->
            <div class="auth-buttons">
                <button type="button" class="style3-btn" href="index.php">
                    Вход/Регистрация
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>