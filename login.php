<?php
// chombo_cas/login.php

session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Rediriger si déjà connecté
if (is_logged_in()) {
    redirect('../phone shop/index.php');
}

// --- Logique PHP pour la connexion ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // La validation des champs et la vérification se font dans login_user()
    if (login_user($email, $password)) { // Fonction de functions.php
        if (is_admin_logged_in()) {
            redirect('/admin/dashboard.php'); // Redirige vers le tableau de bord admin
        } else {
            redirect('../phone shop/index.php'); // Redirige vers l'accueil utilisateur
        }
    }
    // Le message d'erreur est déjà défini par login_user()
}
// --- Fin de la logique PHP ---

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Document</title>
</head>
<body>
    <main>
    <div class="container auth-form">
        <?php display_message(); ?>

        <h1>Connexion</h1>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="button button-primary">Se connecter</button>
        </form>
        <p>Pas encore de compte ? <a href="register.php">Inscrivez-vous ici</a>.</p>
    </div>
</main>
</body>
</html>

<?php
include 'includes/footer.php';
?>