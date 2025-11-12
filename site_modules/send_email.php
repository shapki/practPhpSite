<?php
    function sendEmail($email, $type, $additional_data = [], $mysqli = null) {
        $login = $additional_data['login'] ?? '';
        $username = $additional_data['formatted_username'] ?? ($login ? htmlspecialchars($login) : 'Уважаемый пользователь');
        
        $year = date("Y");
        $styles = "
        <style type='text/css'>
            * {
                font-family: 'Courier New', monospace;
                color: #523a28;
                line-height: 1.4;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #f5e8c8;
                border: 8px double #8c5c3f;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            }
            .email-header {
                background: linear-gradient(135deg, #8c5c3f 0%, #523a28 100%);
                padding: 15px 20px;
                text-align: center;
                border-bottom: 5px solid #d4b37c;
            }
            .logo {
                font-family: 'Arial Black', sans-serif;
                font-size: 2.8rem;
                color: #f5e8c8 !important;
                text-shadow: 3px 3px 0 #523a28 !important;
                letter-spacing: 4px;
            }
            .sublogo-text {
                margin-top: -35px;
            }
            .sublogo-text p {
                font-size: 0.9rem;
                line-height: 1.6;
                text-align: center;
                color: #f5e8c8 !important;
            }
            .email-content {
                padding: 25px;
                background-color: #f5e8c8;
            }
            .email-footer {
                padding: 12px 20px;
                text-align: center;
            }
            .email-footer p {
                color: #f5e8c8 !important;
                margin: 3px 0;
                line-height: 1.3;
                font-size: 0.9rem;
            }
            .decoration {
                height: 15px;
                background: repeating-linear-gradient(
                    45deg,
                    #8c5c3f,
                    #8c5c3f 10px,
                    #d4b37c 10px,
                    #d4b37c 20px
                );
                margin: 20px 0;
            }
            .button {
                display: inline-block;
                padding: 12px 25px;
                background: linear-gradient(to bottom, #8c5c3f, #523a28);
                color: #f5e8c8 !important;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                margin: 10px 0;
                border: none;
                cursor: pointer;
            }
            .warning {
                background-color: #ffeaa7;
                border: 2px solid #fdcb6e;
                padding: 15px;
                border-radius: 5px;
                margin: 15px 0;
            }
            .success {
                background-color: #d4edda;
                border: 2px solid #c3e6cb;
                padding: 15px;
                border-radius: 5px;
                margin: 15px 0;
            }
            h1, h2, h3 {
                margin: 10px 0;
                line-height: 1.2;
            }
            p {
                margin: 8px 0;
                line-height: 1.4;
            }
            a p strong {
                color:#004483 !important;
            }
        </style>
        ";
        
        switch($type) {
            case 'register_success':
                $subject = 'MCM | 🎉 Регистрация успешно завершена!';
                $content = "
                    <div class='success'>
                        <h2>🎉 Регистрация успешно завершена!</h2>
                    </div>
                    <p>Здравствуйте, <strong>{$username}</strong>!</p>
                    <p>Рады приветствовать вас в сообществе Мастерской Костюмов Мюррея. Ваша регистрация была успешно обработана, и теперь вы член нашего сообщества.</p>

                    <div class='decoration'></div>
                    
                    <p><em>Вы получили полный доступ ко всем функциям нашего сервиса, включая персональную скидку 20% на ваш первый заказ.</em></p>
                ";
                break;

            case 'forgot_password':
                $reset_link = $additional_data['reset_link'] ?? '#';
                $subject = 'MCM | 🔐 Запрос на восстановление пароля';
                $content = "
                    <div class='warning'>
                        <h2>🔐 Запрос на восстановление пароля</h2>
                    </div>
                    <p>Здравствуйте, <strong>{$username}</strong>!</p>
                    <p>Мы получили запрос на восстановление пароля для вашего аккаунта. Если это были вы, пожалуйста, используйте ссылку ниже для сброса пароля.</p>
                    
                    <div class='decoration'></div>
                    
                    <p><strong>Важные моменты:</strong></p>
                    <p>• Ссылка для сброса пароля действительна в течение 1 часа<br>
                    • После сброса вам будет предложено установить новый надежный пароль<br>
                    • Рекомендуем использовать комбинацию букв, цифр и специальных символов</p>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='{$reset_link}' class='button'>Сбросить пароль</a>
                    </div>
                    
                    <a href='{$reset_link}'><p><strong>Или перейдите по ссылке, если кнопка не работает</strong></p></a>
                    
                    <p><em>Если вы не запрашивали восстановление пароля, пожалуйста, проигнорируйте это письмо. Ваш аккаунт остается в безопасности.</em></p>
                ";
                break;
                
            default:
                return false;
        }

        $message = "
        <!DOCTYPE html>
        <html lang='ru'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$subject}</title>
            {$styles}
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <div class='logo'>MCM</div>
                    <div class='sublogo-text'>
                        <p>Мастерская Костюмов Мюррея</p>
                    </div>
                </div>
                
                <div class='email-content'>
                    {$content}
                </div>
                
                <div class='decoration'></div>
                
                <div class='email-header'> <!-- Изменено на email-header для фона как у заголовка -->
                    <div class='email-footer'>
                        <p>MCM Systems © 1970-{$year}</p>
                        <p>Телефон: (800) 555-35-35</p>
                        <p>Адрес: г. Ураган, ул. Ветренная, д. 10</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: MCM Systems <admin@pr-shapkin.xn--80ahdri7a.site>\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        return mail($email, $subject, $message, $headers);
    }
?>