<?php

/**
 * Page d'accueil du site de vente de téléphones
 * 
 * Cette page affiche les derniers produits disponibles et permet aux utilisateurs
 * d'ajouter des produits à leur panier.
 * 
 */
// chombo_cas/index.php

session_start(); // Toujours au début pour utiliser les sessions (panier, messages)
require_once 'includes/db_connect.php'; // Inclut la connexion à la base de données ($pdo)
require_once 'includes/functions.php';  // Inclut toutes vos fonctions utilitaires et BDD

// --- Logique PHP pour la page d'accueil ---

// Récupérer les produits à afficher sur la page d'accueil
// Vous pouvez ajouter des paramètres comme la limite, l'ordre, etc.
$products = get_all_products(); // Fonction définie dans functions.php

// Gérer les actions du panier directement ici si le formulaire d'ajout est sur index.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);

    if ($product_id && $quantity && $quantity > 0) {
        add_to_cart($product_id, $quantity); // Fonction de functions.php
    } else {
        set_message('error', 'Quantité ou produit invalide.');
    }
    // Rediriger pour éviter le problème de re-soumission du formulaire
    redirect('../phone shop/index.php');
}

// --- Fin de la logique PHP ---

// Inclure l'en-tête HTML
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos téléphones</title>
    <style>
        /* Reset et styles de base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #212529;
            line-height: 1.6;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
            overflow-x: hidden; /* Empêche le défilement horizontal global */
        }
        
        /* Styles pour la page */
        main {
            padding: 40px 0;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
            font-size: 32px;
            position: relative;
        }
        
        h1::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: #007bff;
            margin: 15px auto 0;
        }
        
        /* Liste de produits horizontale */
        .product-list {
            display: flex;
            gap: 25px;
            padding: 20px 10px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scrollbar-width: thin;
            scrollbar-color: #007bff #f1f1f1;
            margin: 30px -10px; /* Compensation pour le padding */
        }
        
        /* Style de la barre de défilement */
        .product-list::-webkit-scrollbar {
            height: 8px;
        }
        
        .product-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .product-list::-webkit-scrollbar-thumb {
            background-color: #007bff;
            border-radius: 10px;
        }
        
        /* Carte produit horizontale */
        .product-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            min-width: 200px;
            max-width: 200px;
            scroll-snap-align: start;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }
        
        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }
        
        .product-card > div {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        
        .product-card h2 {
            color: #2c3e50;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .product-card .price {
            color: #28a745;
            font-weight: bold;
            font-size: 20px;
            margin: 10px 0;
        }
        
        .product-card p {
            color: #6c757d;
            margin-bottom: 20px;
            flex-grow: 1;
        }
        
        /* Boutons */
        .button {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .product-card .button {
            background-color: #007bff;
            color: white;
            margin-bottom: 15px;
        }
        
        .product-card .button:hover {
            background-color: #0069d9;
        }
        
        /* Formulaire d'ajout au panier */
        .add-to-cart-form {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }
        
        .quantity-input {
            width: 60px;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            text-align: center;
        }
        
        .add-to-cart-button {
            flex-grow: 1;
            background-color: #28a745;
            color: white;
            border: none;
        }
        
        .add-to-cart-button:hover {
            background-color: #218838;
        }
        
        .add-to-cart-button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        
        /* Message aucun produit */
        .product-list > p {
            text-align: center;
            width: 100%;
            padding: 40px;
            color: #6c757d;
            font-size: 18px;
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .product-card {
                min-width: 250px;
                max-width: 250px;
            }
            
            h1 {
                font-size: 28px;
                margin-bottom: 25px;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding: 15px;
            }
            
            .product-card {
                min-width: 220px;
                max-width: 220px;
            }
            
            .add-to-cart-form {
                flex-direction: column;
                gap: 10px;
            }
            
            .quantity-input {
                width: 100%;
            }
            
            .product-card img {
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <main>
        <div class="container">
            <?php display_message(); // Affiche les messages (succès, erreur, etc.) ?>

            <h1>Nos derniers téléphones</h1>

            <div class="product-list">
                <?php if (!empty($products)) : ?>
                    <?php foreach ($products as $product) : ?>
                        <div class="product-card">
                            <img src="uploads/<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div>
                                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                                <p class="price"><?php echo htmlspecialchars(number_format($product['price'], 2, ',', ' ')); ?> USD</p>
                                <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                                <a href="product.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="button">Voir détails</a>
                                
                                <form action="index.php" method="POST" class="add-to-cart-form">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo htmlspecialchars($product['stock']); ?>" class="quantity-input">
                                    <button type="submit" name="add_to_cart" class="button add-to-cart-button" <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                                        <?php echo ($product['stock'] <= 0) ? 'Rupture de stock' : 'Ajouter au panier'; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>Aucun produit disponible pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>

<?php
// Inclure le pied de page HTML
include 'includes/footer.php';
?>