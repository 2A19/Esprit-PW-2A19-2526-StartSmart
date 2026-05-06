<?php
require_once 'config/Auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email === 'client@gmail.com' && $password === 'client123') {
        session_regenerate_id(true);
        $_SESSION['user_role'] = 'CLIENT';
        $_SESSION['user_id'] = 2;
        $_SESSION['user_name'] = 'Client';
        $_SESSION['user_email'] = 'client@gmail.com';
        header('Location: index.php');
        exit;
    } elseif ($email === 'client2@gmail.com' && $password === 'client123') {
        session_regenerate_id(true);
        $_SESSION['user_role'] = 'CLIENT';
        $_SESSION['user_id'] = 4;
        $_SESSION['user_name'] = 'Nouveau Client';
        $_SESSION['user_email'] = 'client2@gmail.com';
        header('Location: index.php');
        exit;
    } elseif ($email === 'admin@gmail.com' && $password === 'admin123') {
        session_regenerate_id(true);
        $_SESSION['user_role'] = 'ADMIN';
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Administrateur';
        $_SESSION['user_email'] = 'admin@gmail.com';
        // Redirect Admin to the frontend so they can access the main page and the forum backend
        header('Location: index.php');
        exit;
    } elseif ($email === 'admin2@gmail.com' && $password === 'admin123') {
        session_regenerate_id(true);
        $_SESSION['user_role'] = 'ADMIN';
        $_SESSION['user_id'] = 3;
        $_SESSION['user_name'] = 'Administrateur 2';
        $_SESSION['user_email'] = 'admin2@gmail.com';
        // Redirect Admin to the frontend so they can access the main page and the forum backend
        header('Location: index.php');
        exit;
    } else {
        $error = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - StartSmart</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(-45deg, #0f2027, #203a43, #2c5364);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
            overflow: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.4;
            filter: blur(60px);
            z-index: 1;
            animation: float 8s ease-in-out infinite;
        }
        
        .circle-1 {
            width: 400px;
            height: 400px;
            background: #ff5e62;
            top: -150px;
            left: -150px;
        }
        
        .circle-2 {
            width: 300px;
            height: 300px;
            background: #36d1dc;
            bottom: -50px;
            right: -50px;
            animation-delay: -4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.1); }
        }

        .login-glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 50px 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
            text-align: center;
            position: relative;
            z-index: 10;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-glass h2 {
            font-size: 36px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 5px;
            background: linear-gradient(to right, #ffffff, #a1c4fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-glass p {
            font-size: 15px;
            color: #d1d5db;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 22px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #e5e7eb;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            box-sizing: border-box;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-group input:focus {
            border-color: #36d1dc;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 15px rgba(54, 209, 220, 0.3);
            transform: scale(1.02);
        }

        .error-message {
            background: rgba(255, 94, 98, 0.2);
            border-left: 4px solid #ff5e62;
            color: #ffb8b8;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 25px;
            text-align: left;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(90deg, #36d1dc, #5b86e5);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            box-shadow: 0 8px 20px rgba(54, 209, 220, 0.4);
            position: relative;
            overflow: hidden;
        }

        .login-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: 0.5s;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(54, 209, 220, 0.5);
        }

        .login-btn:hover::after {
            left: 100%;
        }

        .login-btn:active {
            transform: translateY(1px);
        }

        @media (max-width: 480px) {
            .login-glass {
                padding: 40px 20px;
                border-radius: 15px;
                margin: 0 15px;
            }
        }
    </style>
</head>
<body>

<div class="circle circle-1"></div>
<div class="circle circle-2"></div>

<div class="login-glass">
    <h2>StartSmart</h2>
    <p>Accédez à votre espace en toute sécurité</p>
    
    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <div class="form-group">
            <label for="email">Adresse E-mail</label>
            <input type="email" id="email" name="email" placeholder="Ex: client@gmail.com" required>
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="Votre mot de passe secret" required>
        </div>
        <button type="submit" class="login-btn">Se Connecter</button>
    </form>
</div>

</body>
</html>
