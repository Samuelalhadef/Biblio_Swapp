<?php

session_start();

// Connexion à la base de donée
$host = '127.0.0.1';     
$db = 'biblioswap';       
$user = 'root';           
$pass = '';               

$conn = new mysqli($host, $user, $pass, $db);

// je vérifie la connexion
if ($conn->connect_error) {
    die("Échec de connexion à la base de données : " . $conn->connect_error);
}

// je vérifie si l'uttilisateur est un administrateur
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = false;

$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $is_admin = $user['role'] === 'admin';
}


if ($is_admin && isset($_POST['book_id'])) {
    $book_id = intval($_POST['book_id']);

    // on récupére les info su le livre à supprimer
    $stmt = $conn->prepare("SELECT file_path, cover_image FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $book = $result->fetch_assoc();

        // là on supprime le fichier pdf 
        if (!empty($book['file_path']) && file_exists($book['file_path'])) {
            unlink($book['file_path']);
        }

        // là on supprime le fichier image 
        if (!empty($book['cover_image']) && file_exists($book['cover_image'])) {
            unlink($book['cover_image']);
        }

        //là on supprime le fichier livre de la base de donnée
        $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param("i", $book_id);
        if ($stmt->execute()) {
            header("Location: index.php?message=Livre supprimé avec succès");
        } else {
            header("Location: index.php?error=Erreur lors de la suppression du livre");
        }
    } else {
        header("Location: index.php?error=Livre introuvable");
    }
    exit();
} else {
    header("Location: index.php?error=Permission refusée");
    exit();
}
