<?php
// chombo_cas/admin/admin_login.php

session_start();
require_once '../includes/db_connect.php'; // Chemin relatif depuis admin/
require_once '../includes/functions.php'; // Chemin relatif depuis admin/

// Si l'utilisateur est déjà connecté ET qu'il est administrateur, rediriger vers le tableau de bord admin
if (is_logged_in() && is_admin_logged_in()) {
    redirect('../phone shop/admin/dashboard.php'); // Redirige vers le tableau de bord admin
}

// Initialiser les variables du formulaire
$email = '';
$password = '';

// Gérer la soumission du formulaire de connexion admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Tenter la connexion avec la fonction existante
    if (login_user($email, $password)) {
        // La connexion a réussi, maintenant vérifier si c'est un administrateur
        if (is_admin_logged_in()) {
            set_message('success', 'Connexion administrateur réussie !');
            redirect('../phone shop/admin/dashboard.php'); // Redirige vers le tableau de bord admin
        } else {
            // L'utilisateur est connecté mais n'est PAS un admin.
            // Le déconnecter et l'informer.
            logout_user(); // Cela videra la session et redirigera vers login.php ou l'accueil public
            set_message('error', 'Vous n\'avez pas les droits d\'administrateur pour accéder à cette zone.');
            redirect('../phone shop/admin/admin_login.php'); // Redirige de nouveau vers cette page de connexion admin
        }
    } else {
        // login_user a déjà défini un message d'erreur pour 'Email ou mot de passe incorrect.'
        // Pas de redirection ici, le formulaire affichera le message
    }
}

// Inclure l'en-tête (potentiellement un en-tête spécifique pour l'administration)
// Pour l'instant, utilisons le même en-tête mais ajustez si nécessaire
include '../includes/header.php'; // Chemin relatif depuis admin/
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administrateur</title>
    <style>
        /* Reset et styles de base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Styles pour la page d'authentification */
        .auth-page {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 40px 0;
        }
        
        .auth-page .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        
        .auth-page h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
            font-size: 28px;
        }
        
        /* Styles du formulaire */
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 16px;
        }
        
        .form-group input {
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        /* Styles du bouton */
        .button {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        
        .button-primary {
            background-color: #3498db;
            color: white;
        }
        
        .button-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .button-primary:active {
            transform: translateY(0);
        }
        
        /* Messages flash */
        .flash-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .flash-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .flash-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <main class="auth-page">
        <div class="container">
            <?php display_message(); // Affiche les messages flash ?>

            <h2>Connexion Administrateur</h2>
            <form action="admin_login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Admin :</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe Admin :</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="button button-primary">Se connecter (Admin)</button>
            </form>
        </div>
    </main>
</body>
</html>

<?php
include '../includes/footer.php'; // Chemin relatif depuis admin/
?>