<?php // includes/header.php ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chombo Cas - Votre boutique de téléphones</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    </head>
  <style>
/* Style de base */
header {
    background: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);
    color: #ffffff;
    padding: 1rem 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
    font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Logo */
.logo a {
    color: #ffffff;
    font-size: 1.8rem;
    font-weight: 700;
    text-decoration: none;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    background: linear-gradient(to right, #ffffff, #f3f3f3);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.logo a:hover {
    text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

/* Navigation */
nav ul {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 1.5rem;
    align-items: center;
}

nav li {
    position: relative;
}

nav a {
    color: #ffffff;
    text-decoration: none;
    font-weight: 500;
    font-size: 1rem;
    transition: all 0.3s ease;
    padding: 0.5rem 0;
    display: block;
}

nav a:hover {
    color: #f8d56b;
}

/* Effet de soulignement */
nav li:not(:last-child)::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background-color: #f8d56b;
    transition: width 0.3s ease;
}

nav li:not(:last-child):hover::after {
    width: 100%;
}

/* Style spécifique pour le panier */
nav a[href="/cart.php"] {
    background-color: rgba(255, 255, 255, 0.1);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    transition: all 0.3s ease;
}

nav a[href="/cart.php"]:hover {
    background-color: rgba(248, 213, 107, 0.2);
    transform: translateY(-2px);
}

/* Style pour l'état connecté */
nav li:not(:first-child):not(:last-child) {
    font-size: 0.9rem;
    opacity: 0.9;
}

/* Boutons de connexion/déconnexion */
nav a[href="/login.php"],
nav a[href="/register.php"],
nav a[href="/logout.php"] {
    background-color: #f8d56b;
    color: #2b5876;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

nav a[href="/login.php"]:hover,
nav a[href="/register.php"]:hover,
nav a[href="/logout.php"]:hover {
    background-color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Style admin */
nav a[href="/admin/dashboard.php"] {
    background-color: #e74c3c;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        flex-direction: column;
        gap: 1rem;
    }
    
    nav ul {S
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="../phone shop/index.php">Chombo Cas</a>
            </div>
            <nav>
                <ul>
                    <li><a href="../phone shop/index.php">Accueil</a></li>
                    <li><a href="../phone shop/cart.php">Panier (<?php echo htmlspecialchars(count($_SESSION['cart'] ?? [])); ?>)</a></li>
                    <?php if (is_logged_in()) : ?>
                        <?php if (is_admin_logged_in()) : ?>
                            <li><a href="../phone shop/admin/dashboard.php">Admin</a></li>
                        <?php endif; ?>
                        <li>Bonjour, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilisateur'); ?></li>
                        <li><a href="../phone shop/logout.php">Déconnexion</a></li>
                    <?php else : ?>
                        <li><a href="../phone shop/login.php">Connexion</a></li>
                        <li><a href="../phone shop/register.php">Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    </body>
</html><?php
// includes/header.php