<?php
session_start();

// Configuration de la base de données
$config = [
    'host' => '127.0.0.1',
    'dbname' => 'biblioswap',
    'username' => 'root',
    'password' => ''
];

try {
    // Connexion PDO
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Vérification du rôle administrateur
    $is_admin = false;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        $is_admin = ($user && $user['role'] === 'admin');
    }

    // Gestion de la recherche
    $searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';
    
    if (!empty($searchQuery)) {
        $sql = "SELECT * FROM books WHERE title LIKE :query OR author LIKE :query OR genre LIKE :query";
        $stmt = $pdo->prepare($sql);
        $searchParam = "%{$searchQuery}%";
        $stmt->bindParam(':query', $searchParam);
    } else {
        $sql = "SELECT * FROM books";
        $stmt = $pdo->prepare($sql);
    }
    
    $stmt->execute();
    $books = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioSwap - Bibliothèque de partage</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
        }

        /* Header et Navigation */
        .header {
            background: var(--surface);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        /* Conteneur principal */
        .main-container {
            max-width: 1200px;
            margin: 80px auto 0;
            padding: 2rem;
        }

        /* Barre de recherche */
        .search-container {
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .search-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Grille de livres */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            padding: 1rem 0;
        }

        .book-card {
            background: var(--surface);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
        }

        .book-image-container {
            position: relative;
            width: 100%;
            padding-top: 140%;
            background: #f1f5f9;
        }

        .book-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .book-info {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .book-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text);
        }

        .book-author {
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        .button-container {
            margin-top: auto;
            display: grid;
            gap: 0.5rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            border: none;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.3s ease;
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
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Messages */
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .message-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .message-success {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav {
                padding: 1rem;
            }

            .main-container {
                padding: 1rem;
            }

            .books-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <a href="index.php" class="logo">📚 BiblioSwap</a>
            <div class="nav-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="add_book.php" class="btn btn-primary">Ajouter un livre</a>
                    <a href="logout.php" class="btn">Déconnexion</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">Connexion</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="main-container">
        <?php if (isset($_GET['message'])): ?>
            <div class="message message-success">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="message message-error">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="search-container">
            <form method="get" action="index.php">
                <input 
                    type="text" 
                    name="query" 
                    class="search-input" 
                    placeholder="Rechercher un livre..."
                    value="<?php echo htmlspecialchars($searchQuery); ?>"
                >
            </form>
        </div>

        <div class="books-grid">
            <?php if (!empty($books)): ?>
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <a href="book_details.php?id=<?php echo $book['id']; ?>" class="book-image-container">
                            <?php if (!empty($book['cover_image']) && file_exists($book['cover_image'])): ?>
                                <img 
                                    class="book-image"
                                    src="<?php echo htmlspecialchars($book['cover_image']); ?>"
                                    alt="<?php echo htmlspecialchars($book['title']); ?>"
                                    onerror="this.style.display='none'"
                                >
                            <?php endif; ?>
                        </a>
                        <div class="book-info">
                            <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="book-author">par <?php echo htmlspecialchars($book['author']); ?></p>
                            <div class="button-container">
                                <a href="<?php echo htmlspecialchars($book['file_path']); ?>" 
                                   class="btn btn-primary" 
                                   download>
                                    Télécharger
                                </a>
                                <?php if ($is_admin): ?>
                                    <form method="post" action="delete_book.php">
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
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun livre trouvé.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
<?php $pdo = null; ?>
