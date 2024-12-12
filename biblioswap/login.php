<?php
session_start();

// Configuration de la base de données
$host = '127.0.0.1';
$db = 'biblioswap';       
$user = 'root';           
$pass = '';      

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $error = '';
    $success = isset($_GET['success']) ? $_GET['success'] : '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if (empty($email) || empty($password)) {
            $error = "Veuillez remplir tous les champs.";
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: index.php');
                exit();
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        }
    }
} catch(PDOException $e) {
    $error = "Erreur de connexion : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - BiblioSwap</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #0ea5e9;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #0f172a;
            --text-light: #64748b;
            --danger: #ef4444;
            --success: #22c55e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(14, 165, 233, 0.05) 0%, transparent 50%);
        }

        .header {
            width: 100%;
            background: var(--surface);
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
        }

        .nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .login-container {
            max-width: 400px;
            width: 100%;
            margin-top: 100px;
            background: var(--surface);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            animation: slideUp 0.5s ease-out;
        }

        .title {
            font-size: 1.75rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            color: var(--text);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text);
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--background);
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-light);
        }

        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .success-message {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .login-container {
                padding: 1.5rem;
            }

            .nav {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <a href="index.php" class="logo">📚 BiblioSwap</a>
        </nav>
    </header>

    <div class="login-container">
        <h1 class="title">Connexion</h1>

        <?php if (!empty($error)): ?>
            <div class="message error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="message success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="submit-btn">Se connecter</button>
        </form>

        <div class="register-link">
            Pas encore de compte ? <a href="register.php">S'inscrire</a>
        </div>
    </div>
</body>
</html>
