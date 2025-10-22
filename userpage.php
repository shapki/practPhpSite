<?php
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit();
    }

    include 'site_modules/db_connect.php';

    $requested_user_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : $_SESSION['user_id'];
    if ($requested_user_id === false) {
        $requested_user_id = $_SESSION['user_id'];
    }

    $access_error = null;
    if ($_SESSION['user_id'] != $requested_user_id && $_SESSION['is_admin'] != 1) {
        $access_error = "У вас нет прав для просмотра этой страницы";
    }

    $stmt = $mysqli->prepare("SELECT id, login, first_name, last_name, company, e_mail, foto, date_of_creation, administrator FROM user WHERE id = ?");
    $stmt->bind_param("i", $requested_user_id);
    $stmt->execute();
    $stmt->bind_result($id, $login, $first_name, $last_name, $company, $e_mail, $foto, $date_of_creation, $administrator);
    
    if (!$stmt->fetch()) {
        $stmt->close();
        $access_error = "Пользователь не найден";
    }
    else {
        $stmt->close();
    }

    if ($access_error) {
        $user = [
            'id' => '',
            'first_name' => '??',
            'last_name' => '?',
            'login' => '',
            'company' => '',
            'e_mail' => '',
            'foto' => '',
            'date_of_creation' => '',
            'administrator' => 0
        ];
    } else {
        $user = [
            'id' => $id,
            'login' => $login,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'company' => html_entity_decode($company, ENT_QUOTES, 'UTF-8'),
            'e_mail' => $e_mail,
            'foto' => $foto,
            'date_of_creation' => $date_of_creation,
            'administrator' => $administrator
        ];
    }

    function formatDate($date) {
        $months = [
            '01' => 'января', '02' => 'февраля', '03' => 'марта', '04' => 'апреля',
            '05' => 'мая', '06' => 'июня', '07' => 'июля', '08' => 'августа',
            '09' => 'сентября', '10' => 'октября', '11' => 'ноября', '12' => 'декабря'
        ];
        
        $dateParts = explode(' ', $date);
        $dateOnly = explode('-', $dateParts[0]);
        
        return $dateOnly[2] . ' ' . $months[$dateOnly[1]] . ' ' . $dateOnly[0] . ' года';
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCM | Профиль <?php echo htmlspecialchars($user['first_name'] . ' ' . mb_substr($user['last_name'], 0, 1, 'UTF-8') . '.'); ?></title>
    <link rel="stylesheet" href="/styles/styles.css">
    <link rel="stylesheet" href="/styles/modalWindowStyles.css">
    <link rel="stylesheet" href="/styles/userpage.css">
</head>
<body>
    <?php
        include 'site_modules/page_header.php';
    ?>
    <div class="main-content">
        <div class="userpage-container">
            <?php if ($access_error): ?>
                <div class="profile-card">
                    <div class="profile-info">
                        <div class="info-item">
                            <div class="info-label">Ошибка:</div>
                            <div class="info-value"><?php echo htmlspecialchars($access_error ?? ''); ?></div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="profile-card">
                    <svg class="settings-icon" viewBox="0 0 24 24" onclick="openModal('settingsModal')">
                        <path fill="#8c5c3f" d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z" />
                    </svg>
                    
                    <div class="profile-header">
                        <div class="profile-avatar" onclick="openModal('avatarModal')">
                            <?php if (!empty($user['foto'])): ?>
                                <img src="<?php echo htmlspecialchars($user['foto']); ?>" alt="Аватар">
                            <?php else: ?>
                                <?php echo mb_substr($user['first_name'], 0, 1, 'UTF-8') . mb_substr($user['last_name'], 0, 1, 'UTF-8'); ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="info-item" onclick="openEditModal('name')">
                                <h2 class="profile-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                                <svg class="edit-icon" viewBox="0 0 24 24">
                                    <path fill="#8c5c3f" d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-info">
                        <div class="info-item" onclick="openEditModal('email')">
                            <div class="info-label">Почта:</div>
                            <div class="info-value"><?php echo !empty($user['e_mail']) ? htmlspecialchars($user['e_mail']) : 'Не указан'; ?></div>
                            <svg class="edit-icon" viewBox="0 0 24 24">
                                <path fill="#8c5c3f" d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z" />
                            </svg>
                        </div>
                        
                        <div class="info-item" onclick="openEditModal('company')">
                            <div class="info-label">Компания:</div>
                            <div class="info-value"><?php echo !empty($user['company']) ? htmlspecialchars($user['company']) : 'Не указана'; ?></div>
                            <svg class="edit-icon" viewBox="0 0 24 24">
                                <path fill="#8c5c3f" d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z" />
                            </svg>
                        </div>
                    </div>
                    
                    <div class="profile-footer">
                        Профиль создан: <?php echo formatDate($user['date_of_creation']); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php
            include 'modals/modals_userpage.php';
        ?>
    </div>

    <script src="/scripts/main.js"></script>
    <?php
        include 'scripts/script_userpage.php';
    ?>
</body>
</html>