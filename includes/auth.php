<?php
// chombo_cas/register.php

session_start();
require_once 'includes/db_connect.php'; // Assurez-vous que le chemin est correct
require_once 'includes/functions.php'; // Contient register_user, set_message, redirect

// Si l'utilisateur est déjà connecté, le rediriger
if (is_logged_in()) {
    redirect('index.php');
}

$username = ''; // Initialiser
$email = '';
// $password n'est pas initialisé ici pour des raisons de sécurité

// Gérer la soumission du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyer les entrées utilisateur
    $username = clean_input($_POST['username'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Valider les champs côté serveur
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        set_message('error', 'Tous les champs sont obligatoires.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_message('error', 'Format d\'email invalide.');
    } elseif ($password !== $confirm_password) {
        set_message('error', 'Les mots de passe ne correspondent pas.');
    } elseif (strlen($password) < 6) { // Exemple de validation de longueur
        set_message('error', 'Le mot de passe doit contenir au moins 6 caractères.');
    } else {
        // Tenter l'inscription
        // La fonction register_user gère déjà la vérification d'existence de l'email/username
        if (register_user($username, $email, $password)) {
            // Inscription réussie, rediriger vers la page de connexion
            set_message('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
            redirect('login.php');
        } else {
            // La fonction register_user a déjà défini un message d'erreur
        }
    }
}

// Inclure l'en-tête et afficher le formulaire d'inscription
include 'includes/header.php';
?>

<main class="auth-page">
    <div class="container">
        <?php display_message(); // Affiche les messages de succès/erreur ?>

        <h2>Inscription</h2>
        <form action="register.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="button button-primary">S'inscrire</button>
            <p class="auth-link">Déjà un compte ? <a href="login.php">Se connecter</a>.</p>
        </form>
    </div>
</main>

<?php
include 'includes/footer.php';
?>