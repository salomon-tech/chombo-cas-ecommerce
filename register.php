<?php
// chombo_cas/register.php

session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Rediriger si déjà connecté
if (is_logged_in()) {
    redirect('/index.php');
}

// --- Logique PHP pour l'inscription ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Simple validation (à renforcer avec des messages d'erreur spécifiques)
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
        set_message('error', 'Tous les champs sont requis.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_message('error', 'Veuillez entrer une adresse email valide.');
    } elseif ($password !== $password_confirm) {
        set_message('error', 'Les mots de passe ne correspondent pas.');
    } elseif (strlen($password) < 6) { // Minimum 6 caractères
        set_message('error', 'Le mot de passe doit contenir au moins 6 caractères.');
    } else {
        // Tenter d'enregistrer l'utilisateur via la fonction
        if (register_user($username, $email, $password)) {
            // L'utilisateur est maintenant créé, on peut tenter de le connecter directement
            if (login_user($email, $password)) {
                redirect('/index.php'); // Redirige vers l'accueil après inscription et connexion
            } else {
                set_message('warning', 'Inscription réussie, mais connexion automatique échouée. Veuillez vous connecter manuellement.');
                redirect('../phone shop/login.php');
            }
        }
        // Si register_user a échoué, un message d'erreur est déjà défini par la fonction
    }
}
// --- Fin de la logique PHP ---

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>
    <style>
   /* Reset de base */



</style>
</head>
<body>
    <main>
        <div class="container auth-form">
            <?php display_message(); ?>

            <h1>Créer un compte</h1>
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe:</label>
                    <input type="password" id="password" name="password" required>
                    <p class="password-hint">(8 caractères minimum, avec majuscule et chiffre)</p>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirmer le mot de passe:</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
                <button type="submit" class="button button-primary">S'inscrire</button>
            </form>
            <p>Déjà un compte ? <a href="login.php">Connectez-vous ici</a>.</p>
        </div>
    </main>
</body>
</html>

<?php
include 'includes/footer.php';
?>