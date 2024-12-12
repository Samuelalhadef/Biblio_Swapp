<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=biblioswap;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $error = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $description = trim($_POST['description']);
        $file = $_FILES['file'];
        $coverImage = $_FILES['cover_image'];

        if (empty($title) || empty($author) || empty($description) || empty($file['name']) || empty($coverImage['name'])) {
            $error = "Tous les champs sont obligatoires.";
        } elseif ($file['type'] !== 'application/pdf') {
            $error = "Seuls les fichiers PDF sont acceptés.";
        } elseif ($file['size'] > 2000000) {
            $error = "Le fichier PDF est trop volumineux. Maximum 2 Mo.";
        } elseif (!in_array($coverImage['type'], ['image/jpeg', 'image/png'])) {
            $error = "Seules les images JPEG et PNG sont acceptées pour la couverture.";
        } elseif ($coverImage['size'] > 1000000) {
            $error = "L'image de couverture est trop volumineuse. Maximum 1 Mo.";
        } else {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $filePath = $targetDir . uniqid() . '_' . basename($file['name']);
            $coverImagePath = $targetDir . uniqid() . '_' . basename($coverImage['name']);

            if (move_uploaded_file($file['tmp_name'], $filePath) && move_uploaded_file($coverImage['tmp_name'], $coverImagePath)) {
                $stmt = $pdo->prepare("INSERT INTO books (user_id, title, author, description, file_path, cover_image) VALUES (?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$_SESSION['user_id'], $title, $author, $description, $filePath, $coverImagePath])) {
                    $success = "Le livre a été ajouté avec succès.";
                } else {
                    $error = "Une erreur est survenue lors de l'ajout du livre.";
                }
            } else {
                $error = "Échec du téléchargement du fichier ou de l'image.";
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
    <title>Ajouter un Livre - BiblioSwap</title>
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

        .form-container {
            max-width: 600px;
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

        input, textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--background);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .file-input-container {
            margin-bottom: 1.5rem;
        }

        .file-input-label {
            display: block;
            padding: 0.75rem 1rem;
            background: var(--background);
            border: 2px dashed #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .file-input {
            display: none;
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

        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: var(--text-light);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: var(--primary);
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

            .form-container {
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

    <div class="form-container">
        <h1 class="title">Ajouter un nouveau livre</h1>

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

        <form method="post" enctype="multipart/form-data" action="add_book.php">
            <div class="form-group">
                <label for="title">Titre du livre</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="author">Auteur</label>
                <input type="text" id="author" name="author" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="file-input-container">
                <label for="file" class="file-input-label">
                    Sélectionner le fichier PDF du livre (max 2 Mo)
                </label>
                <input type="file" id="file" name="file" class="file-input" accept=".pdf" required>
            </div>

            <div class="file-input-container">
                <label for="cover_image" class="file-input-label">
                    Sélectionner l'image de couverture (max 1 Mo)
                </label>
                <input type="file" id="cover_image" name="cover_image" class="file-input" accept=".jpg,.jpeg,.png" required>
            </div>

            <button type="submit" class="submit-btn">Ajouter le livre</button>
        </form>

        <a href="index.php" class="back-link">← Retour à la bibliothèque</a>
    </div>

    <script>
        // Mise à jour du texte des labels de fichiers
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', function() {
                const label = this.previousElementSibling;
                const fileName = this.files[0]?.name || '';
                label.textContent = fileName || (this.name === 'file' ? 
                    'Sélectionner le fichier PDF du livre (max 2 Mo)' : 
                    'Sélectionner l\'image de couverture (max 1 Mo)');
            });
        });
    </script>
</body>
</html>