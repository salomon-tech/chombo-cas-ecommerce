<?php
// chombo_cas/admin/products.php

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

check_admin_auth();

// Gérer la suppression de produit
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $product_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($product_id && delete_product($product_id)) {
        set_message('success', 'Produit supprimé avec succès !');
    } else {
        set_message('error', 'Erreur lors de la suppression du produit ou ID invalide.');
    }
    redirect('products.php'); // Rediriger pour éviter le re-traitement GET
}

// CORRECTION À LA LIGNE 21 :
// Appelle get_all_products avec le troisième paramètre `true` pour inclure les produits inactifs.
// Assurez-vous d'avoir modifié la fonction get_all_products dans functions.php en conséquence (comme dans l'Option 1 ci-dessus).
$all_products = get_all_products(null, null, ); // Récupère tous les produits, y compris inactifs, pour l'admin

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits - Admin</title>
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
        }
        
        /* Styles pour la page admin */
        .admin-page {
            padding: 40px 0;
        }
        
        .admin-page h1 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 32px;
            text-align: center;
        }
        
        /* Bouton Ajouter */
        .admin-page > .container > p {
            margin-bottom: 30px;
            text-align: right;
        }
        
        /* Styles du tableau */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .admin-table th {
            background-color: #343a40;
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        .admin-table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .admin-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Image miniature */
        .admin-product-thumb {
            max-width: 60px;
            max-height: 60px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            object-fit: cover;
        }
        
        /* Styles des boutons */
        .button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .button-small {
            padding: 6px 12px;
            font-size: 13px;
        }
        
        .button-success {
            background-color: #28a745;
            color: white;
        }
        
        .button-success:hover {
            background-color: #218838;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .button-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .button-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .button-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .button-danger:hover {
            background-color: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        /* Style pour les statuts */
        .admin-table td:nth-child(6) {
            font-weight: 600;
            color: #28a745;
        }
        
        .admin-table td:nth-child(6):contains("Non") {
            color: #dc3545;
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
        @media (max-width: 992px) {
            .admin-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .admin-product-thumb {
                max-width: 50px;
                max-height: 50px;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .admin-page h1 {
                font-size: 28px;
            }
            
            .admin-table th,
            .admin-table td {
                padding: 12px 8px;
            }
        }
        
        @media (max-width: 576px) {
            .admin-page > .container > p {
                text-align: center;
            }
            
            .button {
                padding: 8px 12px;
                font-size: 13px;
                display: block;
                margin-bottom: 5px;
                width: 100%;
            }
            
            .admin-product-thumb {
                max-width: 40px;
                max-height: 40px;
            }
        }
    </style>
</head>
<body>
    <main class="admin-page">
        <div class="container">
            <?php display_message(); ?>

            <h1>Gestion des Produits</h1>
            <p><a href="product_add.php" class="button button-success">Ajouter un nouveau produit</a></p>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Actif</th>
                        <th>Catégorie</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_products)) : ?>
                        <?php foreach ($all_products as $product) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                <td><img src="../uploads/<?php echo htmlspecialchars($product['main_image']); ?>" alt="" class="admin-product-thumb"></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($product['price'], 2, ',', ' ')); ?></td>
                                <td><?php echo htmlspecialchars($product['stock']); ?></td>
                                <td><?php echo $product['is_active'] ? 'Oui' : 'Non'; ?></td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="product_edit.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="button button-secondary button-small">Modifier</a>
                                    <a href="products.php?action=delete&id=<?php echo htmlspecialchars($product['id']); ?>" class="button button-danger button-small" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8">Aucun produit trouvé.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>

<?php
include '../includes/footer.php';
?>