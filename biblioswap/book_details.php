<?php
session_start();

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=biblioswap;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification du rôle administrateur
    $is_admin = false;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $is_admin = ($user && $user['role'] === 'admin');
    }

    // Récupération des détails du livre
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: index.php?error=ID du livre invalide");
        exit();
    }

    $stmt = $pdo->prepare("
        SELECT books.*, users.username as uploader_name
        FROM books 
        LEFT JOIN users ON books.user_id = users.id 
        WHERE books.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        header("Location: index.php?error=Livre introuvable");
        exit();
    }

} catch(PDOException $e) {
    header("Location: index.php?error=Erreur de base de données");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title'] ?? 'Détails du livre'); ?> - BiblioSwap</title>
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
        }

        .header {
            background: var(--surface);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
        }

        .nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .main-container {
            max-width: 1200px;
            margin: 80px auto 0;
            padding: 2rem;
            animation: fadeIn 0.5s ease-out;
        }

        .book-container {
            background: var(--surface);
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .book-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 3rem 2rem;
            color: white;
        }

        .book-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .book-author {
            font-size: 1.25rem;
            opacity: 0.9;
        }

        .book-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            padding: 2rem;
        }

        .book-cover {
            width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .book-cover:hover {
            transform: scale(1.02);
        }

        .book-details {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .book-info {
            display: grid;
            gap: 1rem;
        }

        .info-item {
            color: var(--text-light);
        }

        .info-item strong {
            color: var(--text);
            display: inline-block;
            width: 120px;
        }

        .book-description {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text);
            margin: 1.5rem 0;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: auto;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--text-light);
            text-decoration: none;
            margin-bottom: 1rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: var(--primary);
        }

        @keyframes fadeIn {
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
            .book-content {
                grid-template-columns: 1fr;
            }

            .book-title {
                font-size: 2rem;
            }

            .main-container {
                padding: 1rem;
            }

            .nav {
                padding: 1rem;
            }

            .action-buttons {
                flex-direction: column;
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

    <main class="main-container">
        <a href="index.php" class="back-link">← Retour à la bibliothèque</a>

        <div class="book-container">
            <div class="book-header">
                <h1 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h1>
                <p class="book-author">par <?php echo htmlspecialchars($book['author']); ?></p>
            </div>

            <div class="book-content">
                <div class="book-image">
                    <?php if (!empty($book['cover_image']) && file_exists($book['cover_image'])): ?>
                        <img 
                            src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                            alt="Couverture de <?php echo htmlspecialchars($book['title']); ?>"
                            class="book-cover"
                        >
                    <?php endif; ?>
                </div>

                <div class="book-details">
                    <div class="book-info">
                        <p class="info-item">
                            <strong>Ajouté par:</strong>
                            <?php echo htmlspecialchars($book['uploader_name'] ?? 'Utilisateur inconnu'); ?>
                        </p>
                        <?php if (!empty($book['genre'])): ?>
                            <p class="info-item">
                                <strong>Genre:</strong>
                                <?php echo htmlspecialchars($book['genre']); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="book-description">
                        <?php echo nl2br(htmlspecialchars($book['description'] ?? '')); ?>
                    </div>

                    <div class="action-buttons">
                        <?php if (!empty($book['file_path']) && file_exists($book['file_path'])): ?>
                            <a href="<?php echo htmlspecialchars($book['file_path']); ?>" 
                               class="btn btn-primary" 
                               download>
                                Télécharger le PDF
                            </a>
                        <?php endif; ?>

                        <?php if ($is_admin): ?>
                            <form method="post" action="delete_book.php" style="display: inline;">
                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                <button type="submit" 
                                        class="btn btn-danger"
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livre ?')">
                                    Supprimer
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>