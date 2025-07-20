<?php
// chombo_cas/checkout.php

session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// --- Vérification si l'utilisateur est connecté ---
if (!is_logged_in()) {
    set_message('info', 'Vous devez être connecté pour passer une commande.');
    redirect('../phone shop/login.php');
}

// --- Logique PHP pour le paiement ---

$user_id = $_SESSION['user_id'];
$cart_items_details = get_cart_items_details();
$cart_total = get_cart_total();

// Rediriger si le panier est vide
if (empty($cart_items_details)) {
    set_message('info', 'Votre panier est vide. Ajoutez des produits avant de passer commande.');
    redirect('../phone shop/cart.php');
}

// Traitement du formulaire de commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Ici, vous pourriez ajouter des champs de formulaire pour l'adresse de livraison,
    // les informations de paiement (bien que pour un projet basique, on pourrait simplifier cela).

    // Pour l'exemple, nous allons directement appeler place_order()
    $order_id = place_order($user_id, $cart_items_details); // Fonction de functions.php

    if ($order_id) {
        set_message('success', 'Votre commande #' . htmlspecialchars($order_id) . ' a été passée avec succès !');
        redirect('../phone shop/index.php'); // Rediriger vers l'accueil ou une page de confirmation
    } else {
        // Le message d'erreur est déjà défini par place_order()
        // display_message() sera appelé dans le HTML
    }
}

// --- Fin de la logique PHP ---

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalisation de commande</title>
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        /* Styles pour la page */
        main {
            padding: 20px 0 40px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            text-align: center;
            font-size: 32px;
        }
        
        h2 {
            color: #495057;
            margin: 30px 0 20px;
            font-size: 24px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        /* Tableau récapitulatif */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 40px;
        }
        
        .cart-table th,
        .cart-table td {
            padding: 18px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .cart-table th {
            background-color: #343a40;
            color: white;
            font-weight: 600;
        }
        
        .cart-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .cart-table tfoot {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .text-right {
            text-align: right;
        }
        
        /* Formulaire de confirmation */
        .checkout-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            text-align: center;
        }
        
        .checkout-form p {
            margin-bottom: 25px;
            color: #6c757d;
            font-size: 16px;
        }
        
        /* Boutons */
        .button {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 16px;
        }
        
        .button-primary {
            background-color: #28a745;
            color: white;
        }
        
        .button-primary:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .large-button {
            padding: 15px 40px;
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
            .container {
                padding: 20px 15px;
            }
            
            h1 {
                font-size: 28px;
            }
            
            h2 {
                font-size: 22px;
            }
            
            .cart-table th,
            .cart-table td {
                padding: 14px 12px;
                font-size: 14px;
            }
            
            .checkout-form {
                padding: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .cart-table {
                display: block;
                overflow-x: auto;
            }
            
            .button {
                width: 100%;
                padding: 14px;
            }
            
            .checkout-form p {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <main>
        <div class="container">
            <?php display_message(); ?>

            <h1>Finaliser votre commande</h1>

            <h2>Récapitulatif de votre commande</h2>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items_details as $item) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($item['price'], 2, ',', ' ')); ?> USD</td>
                            <td><?php echo htmlspecialchars(number_format($item['subtotal'], 2, ',', ' ')); ?> USD</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total à payer:</strong></td>
                        <td><strong><?php echo htmlspecialchars(number_format($cart_total, 2, ',', ' ')); ?> USD</strong></td>
                    </tr>
                </tfoot>
            </table>

            <form action="checkout.php" method="POST" class="checkout-form">
                <p>En cliquant sur "Confirmer la commande", vous acceptez de finaliser votre achat.</p>
                <button type="submit" name="place_order" class="button button-primary large-button">Confirmer la commande</button>
            </form>
        </div>
    </main>
</body>
</html>

<?php
include 'includes/footer.php';
?>