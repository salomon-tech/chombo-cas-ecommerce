<?php
session_start(); // Assurez-vous que session_start() est bien au tout début du fichier
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Initialise le panier si ce n'est pas déjà fait
initialize_cart();

// --- Logique PHP pour le panier ---

// Gérer les actions du formulaire du panier (mise à jour quantité, suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
            foreach ($_POST['quantities'] as $product_id => $quantity) {
                $product_id = filter_var($product_id, FILTER_VALIDATE_INT);
                $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
                if ($product_id && $quantity !== false) {
                    update_cart_quantity($product_id, $quantity); // Fonction de functions.php
                } else {
                    set_message('error', 'Quantité ou produit invalide pour la mise à jour.');
                }
            }
            // set_message('success', 'Votre panier a été mis à jour.'); // Le message est déjà défini dans update_cart_quantity
        } else {
            set_message('error', 'Aucune quantité à mettre à jour.');
        }
    } elseif (isset($_POST['remove_item']) && isset($_POST['product_id_to_remove'])) {
        $product_id_to_remove = filter_var($_POST['product_id_to_remove'], FILTER_VALIDATE_INT);
        if ($product_id_to_remove) {
            remove_from_cart($product_id_to_remove); // Fonction de functions.php
        }
    }
    redirect('cart.php'); // Rediriger pour éviter la re-soumission
}

// Récupérer les détails des articles du panier pour l'affichage
$cart_items = get_cart_items_details(); // Fonction de functions.php
$cart_total = get_cart_total();         // Fonction de functions.php

// --- Fin de la logique PHP ---

// Inclusion du header (qui contient souvent le HTML de début et la navigation)
include 'includes/header.php';
?>

<style>
    /* Reset et styles de base */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8f9fa;
    color: #212529;
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.my-4 {
    margin-top: 24px;
    margin-bottom: 24px;
}

.mb-4 {
    margin-bottom: 24px;
}

/* Titre */
h1 {
    color: #2c3e50;
    font-size: 32px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 30px;
}

/* Tableau */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
    background-color: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.table-bordered th,
.table-bordered td {
    border: 1px solid #dee2e6;
    padding: 16px;
    vertical-align: middle;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.02);
}

.table th {
    background-color: #343a40;
    color: white;
    font-weight: 600;
    text-align: left;
}

/* Image produit */
.img-thumbnail {
    padding: 4px;
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

/* Champs de formulaire */
.form-control {
    display: block;
    width: 100%;
    padding: 8px 12px;
    font-size: 16px;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 4px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Boutons */
.btn {
    display: inline-block;
    font-weight: 500;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    user-select: none;
    border: 1px solid transparent;
    padding: 10px 20px;
    font-size: 16px;
    line-height: 1.5;
    border-radius: 4px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 14px;
}

.btn-secondary {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

.btn-info {
    color: #fff;
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-info:hover {
    background-color: #138496;
    border-color: #117a8b;
}

.btn-success {
    color: #fff;
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

.btn-danger {
    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

/* Alerte */
.alert {
    position: relative;
    padding: 16px 20px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-info {
    color: #0c5460;
    background-color: #d1ecf1;
    border-color: #bee5eb;
}

.alert-link {
    font-weight: 600;
    color: #062c33;
    text-decoration: underline;
}

.alert-link:hover {
    color: #041a1f;
}

/* Flex utilities */
.d-flex {
    display: flex;
}

.align-items-center {
    align-items: center;
}

.justify-content-between {
    justify-content: space-between;
}

.me-3 {
    margin-right: 16px;
}

.text-end {
    text-align: right;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding: 15px;
    }
    
    h1 {
        font-size: 28px;
    }
    
    .table-responsive {
        display: block;
        width: 100%;
        overflow-x: auto;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 15px;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 10px;
    }
}

@media (max-width: 576px) {
    .table th, 
    .table td {
        padding: 10px;
        font-size: 14px;
    }
    
    .img-thumbnail {
        width: 60px;
        height: 60px;
    }
    
    .form-control {
        width: 70px;
    }
}
</style>

<main class="container my-4">
    <?php display_message(); // Affiche les messages (succès, erreur, info) ?>

    <h1 class="mb-4">Votre Panier</h1>

    <?php if (!empty($cart_items)): ?>
        <form action="cart.php" method="POST">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th scope="col">Produit</th>
                        <th scope="col">Prix Unitaire</th>
                        <th scope="col">Quantité</th>
                        <th scope="col">Sous-total</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($item['main_image'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($item['main_image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-thumbnail me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                                </div>
                            </td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <input type="number" name="quantities[<?php echo htmlspecialchars($item['id']); ?>]" 
                                       value="<?php echo htmlspecialchars($item['quantity']); ?>" 
                                       min="1" 
                                       max="<?php echo htmlspecialchars($item['available_stock']); ?>" 
                                       class="form-control" style="width: 80px;">
                            </td>
                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                            <td>
                                <button type="submit" name="remove_item" class="btn btn-danger btn-sm" value="true">
                                    <input type="hidden" name="product_id_to_remove" value="<?php echo htmlspecialchars($item['id']); ?>">
                                    Retirer
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total du Panier:</strong></td>
                        <td colspan="2"><strong>$<?php echo number_format($cart_total, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary">Continuer vos achats</a>
                <div>
                    <button type="submit" name="update_cart" class="btn btn-info">Mettre à jour le panier</button>
                    <a href="../phone shop/checkout.php" class="btn btn-success">Passer la commande</a>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-info text-center" role="alert">
            Votre panier est actuellement vide. <a href="../phone shop/index.php" class="alert-link">Commencez vos achats !</a>
        </div>
    <?php endif; ?>

</main>

<?php
// Inclusion du footer
include 'includes/footer.php';
?>