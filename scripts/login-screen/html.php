<?php namespace Metayota; include_once('../metayota/library.php');  
session_start();

$companySettings = $db->get('company_settings');

$appSettings = $db->get('app_settings');

$error = ''; // Initialize error variable
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= translate('secure_login_title') ?>
    </title>
    <link rel="icon" type="image/png" sizes="32x32" href="/call/image.resize?w=180&h=180&format=png&src=/uploads/favicons/<?= $appSettings['icon'] ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="/call/image.resize?w=180&h=180&format=png&src=/uploads/favicons/<?= $appSettings['icon'] ?>">

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/call/image.resize?w=180&h=180&format=png&src=/uploads/favicons/<?= $appSettings['icon'] ?>">

    <style>
        :root {
            --primary-color: #005eaa;
            --light-color-1: #d6e6f5;
            --component-color: white;
            --text-color: #2f476a;
            --bg-box: #EDEFEF;
            --error-color: #eeff00;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        #loginButton {
            border: 1px solid #1a76c0;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #859ead;
            font-family: Arial, sans-serif;
        }

        .logo-container {
            background: white;
            width: 100%;
            max-width: 400px;
            text-align: center;
            padding: 24px;
            border-radius: 8px 8px 0 0;
            box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.1);
            min-width: 400px;
            display: flex;
            justify-content: center;
            padding-top: 0px;
            padding-bottom: 0px;
        }

        .login-container {
            background: #5785a1;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            background: #285068;
        }

        h2 {
            color: white;
            margin-bottom: 20px;
        }

        label {
            display: block;
            text-align: left;
            margin-top: 15px;
            color: white;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border: 1px solid var(--light-color-1);
            border-radius: 4px;
            background: var(--component-color);
            color: var(--text-color);
            font-size: 16px;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0px 0px 5px var(--primary-color);
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            border: none;
            border-radius: 4px;
            background: var(--primary-color);
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 150ms ease-in-out;
        }

        button:active {
            transform: translate(3px, 3px);
        }

        .error {
            color: var(--error-color);
            font-size: 14px;
            margin-top: 12px;
            text-align: left;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: var(--text-color);
            width: 40px;
            margin-top: 5px;
        }

        .toggle-password img {
            width: 20px;
            height: 20px;
        }

        .toggle-password:hover {
            background: transparent;
        }

        .mainbox {
            box-shadow: 0px 0px 120px #4f6c7e, 0px 0px 5px #4f6c7ecf;
            border-radius: 10px;
        }

        .other-options {
            text-align: left;
            color: white;
            margin-top: 10px;
        }

        .other-options a {
            color: white;
            text-decoration: none;
        }

        .logo {
            max-width: 180px;
            max-height: 50px;
            width: auto;
            margin-top: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="mainbox">
        <div class="logo-container">
            <img class="logo" src="/uploads/logos/<?= $companySettings['logo'] ?>"
                alt="<?= translate('company_logo_alt') ?>" width="120">
        </div>
        <div class="login-container">
            <h2>
                <?= translate('secure_login_title') ?>
            </h2>
            <form id="loginForm">
                <label for="username">
                    <?= translate('username_label') ?>
                </label>
                <input type="text" id="username" name="username" required 
       autocomplete="off" spellcheck="false" autocapitalize="off" autocorrect="off">

                <label for="password">
                    <?= translate('password_label') ?>
                </label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <a type="button" class="toggle-password" id="togglePassword">
                        <img src="/images/icons/18-eye.svg" id="eyeIcon" />
                    </a>
                </div>

                <p id="errorMessage" class="error"></p>

                <button type="submit" id="loginButton">
                    <?= translate('login_button') ?>
                </button>

                <div class="other-options">
                    <a href="/register">
                        <?= translate('register_link') ?>
                    </a>
                    <span>&middot;</span>
                    <a href="/forgot-password">
                        <?= translate('forgot_password_link') ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.src = '/images/icons/18-eye-slash.svg';
            } else {
                passwordInput.type = 'password';
                eyeIcon.src = '/images/icons/18-eye.svg';
            }
        });

        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const errorMessage = document.getElementById('errorMessage');
            const loginButton = document.getElementById('loginButton');
            let remember_me = true;
            errorMessage.textContent = ''; // Clear previous errors

            fetch('/call/login/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password, remember_me })
            })
            .then(response => response.json())
            .then(data => {
                if (data.id) {
                    location.reload();
                    
                    window.location = '/' + window.location.pathname.split('/')[1] + '/home';

                } else {
                    errorMessage.textContent = data.message || '<?= translate('invalid_login_message') ?>';
                }
            })
            .catch(() => {
                errorMessage.textContent = '<?= translate('login_error_message') ?>';
            });

            loginButton.style.transform = 'translate(3px, 3px)';
            setTimeout(() => {
                loginButton.style.transform = 'translate(0, 0)';
            }, 150);
        });
    </script>
</body>

</html>