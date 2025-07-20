<?php
// chombo_cas/product.php

session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// --- Logique PHP pour la page de détail produit ---

$product_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

$product = null;
if ($product_id) {
    $product = get_product_details($product_id); // Fonction de functions.php
}

if (!$product) {
    set_message('error', 'Produit introuvable ou non disponible.');
    redirect('/index.php'); // Redirige si l'ID est invalide ou le produit n'existe pas
}

// Gérer l'ajout au panier depuis la page de détail
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);

    if ($quantity && $quantity > 0) {
        add_to_cart($product['id'], $quantity); // Fonction de functions.php
    } else {
        set_message('error', 'Quantité invalide.');
    }
    // Rediriger pour éviter le problème de re-soumission du formulaire
    redirect('../phone shop/product.php?id=' . $product['id']);
}

// --- Fin de la logique PHP ---

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du produit</title>
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
        
        /* Conteneur principal */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        /* Mise en page produit */
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 40px;
            align-items: center;
        }
        
        /* Image du produit */
        .product-image {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 400px;
        }
        
        .product-image img {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
            transition: transform 0.3s;
        }
        
        .product-image:hover img {
            transform: scale(1.03);
        }
        
        /* Informations produit */
        .product-info {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .product-info h1 {
            color: #2c3e50;
            font-size: 32px;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .category {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .price {
            color: #28a745;
            font-size: 28px;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .description {
            color: #495057;
            margin-bottom: 25px;
            line-height: 1.7;
            white-space: pre-line;
        }
        
        .stock {
            font-weight: 600;
            margin-bottom: 30px;
            color: <?php echo ($product['stock'] > 0) ? '#28a745' : '#dc3545'; ?>;
        }
        
        /* Formulaire d'ajout au panier */
        .add-to-cart-form {
            margin-top: auto;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        
        .add-to-cart-form label {
            font-weight: 600;
            color: #495057;
            margin-right: 10px;
        }
        
        .quantity-input {
            width: 80px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 16px;
            text-align: center;
        }
        
        .add-to-cart-button {
            flex: 1;
            padding: 14px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            min-width: 200px;
        }
        
        .add-to-cart-button:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .add-to-cart-button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        /* Messages flash */
        .flash-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
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
        @media (max-width: 992px) {
            .product-detail {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .product-image {
                min-height: 300px;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            .product-detail {
                padding: 30px;
            }
            
            .product-info h1 {
                font-size: 28px;
            }
            
            .price {
                font-size: 24px;
            }
        }
        
        @media (max-width: 576px) {
            .product-detail {
                padding: 20px;
                box-shadow: none;
                border-radius: 0;
            }
            
            .add-to-cart-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .quantity-input {
                width: 100%;
            }
            
            .product-image {
                min-height: 250px;
            }
        }
    </style>
</head>
<body>
    <main>
        <div class="container product-detail">
            <?php display_message(); ?>

            <div class="product-image">
                <img src="uploads/<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="category">Catégorie: <?php echo htmlspecialchars($product['category_name'] ?? 'Non classé'); ?></p>
                <p class="price"><?php echo htmlspecialchars(number_format($product['price'], 2, ',', ' ')); ?> USD</p>
                <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <p class="stock">Stock disponible: <?php echo htmlspecialchars($product['stock']); ?></p>

                <form action="product.php?id=<?php echo htmlspecialchars($product['id']); ?>" method="POST" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                    <label for="quantity">Quantité:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo htmlspecialchars($product['stock']); ?>" class="quantity-input" <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                    <button type="submit" name="add_to_cart" class="button add-to-cart-button" <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                        <?php echo ($product['stock'] <= 0) ? 'Rupture de stock' : 'Ajouter au panier'; ?>
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>

<?php
include 'includes/footer.php';
?>