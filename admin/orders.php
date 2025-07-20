<?php
// chombo_cas/admin/orders.php

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

check_admin_auth();

// Initialisation des variables pour éviter les erreurs "Undefined variable"
$orders = [];
$order_details = null; // Sera rempli si une commande spécifique est visualisée
$order_items = [];    // Sera rempli si une commande spécifique est visualisée

// Gérer la mise à jour du statut de commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
    // Note: Le nom du champ dans le formulaire doit être 'new_status' pour correspondre à la logique que j'ai fournie précédemment.
    // Si votre formulaire envoie 'status', changez-le ou utilisez $_POST['status'] ici.
    $new_status = clean_input($_POST['new_status'] ?? $_POST['status'] ?? ''); // Utilise 'new_status' ou 'status'

    // Valider que le nouveau statut est une valeur autorisée par l'ENUM de la BDD
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if ($order_id && in_array($new_status, $allowed_statuses)) {
        if (update_order_status($order_id, $new_status)) {
            set_message('success', 'Statut de la commande mis à jour avec succès.');
        } else {
            set_message('error', 'Erreur lors de la mise à jour du statut.');
        }
    } else {
        set_message('error', 'Données de statut de commande invalides.');
    }
    redirect('orders.php'); // Redirige après la soumission du formulaire POST
} 
// Logique pour afficher les détails d'une commande spécifique
elseif (isset($_GET['view']) && filter_var($_GET['view'], FILTER_VALIDATE_INT)) {
    $order_id_to_view = filter_var($_GET['view'], FILTER_VALIDATE_INT);
    $order_details = get_order_details($order_id_to_view); // Fonction pour récupérer les détails d'une commande
    if ($order_details) {
        $order_items = get_order_items($order_id_to_view); // Fonction pour récupérer les articles de cette commande
    } else {
        set_message('error', 'Commande introuvable.');
        redirect('orders.php'); // Redirige vers la liste si la commande n'existe pas
    }
} else {
    // Par défaut, afficher toutes les commandes
    $orders = get_all_orders(); // Fonction pour récupérer toutes les commandes
}

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commandes - Admin</title>
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
            margin-bottom: 30px;
            font-size: 32px;
            text-align: center;
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
        
        .button {
            background-color: #007bff;
            color: white;
        }
        
        .button:hover {
            background-color: #0069d9;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        /* Styles du sélecteur de statut */
        select {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        select:focus {
            border-color: #80bdff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        /* Styles des statuts */
        option[value="pending"] { color: #ffc107; font-weight: 600; }
        option[value="processing"] { color: #17a2b8; font-weight: 600; }
        option[value="shipped"] { color: #007bff; font-weight: 600; }
        option[value="delivered"] { color: #28a745; font-weight: 600; }
        option[value="cancelled"] { color: #dc3545; font-weight: 600; }
        
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
                padding: 15px;
                overflow-x: auto;
            }
            
            .admin-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .admin-page h1 {
                font-size: 28px;
            }
        }
        
        @media (max-width: 576px) {
            .admin-table th,
            .admin-table td {
                padding: 12px 8px;
                font-size: 14px;
            }
            
            select {
                padding: 6px 8px;
                font-size: 13px;
            }
            
            .button-small {
                padding: 4px 8px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<main class="admin-page">
    <div class="container">
        <?php display_message(); ?>

        <h1>Gestion des Commandes</h1>

        <?php if ($order_details): // Si on affiche les détails d'une commande ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h2>Détails de la commande #<?php echo htmlspecialchars($order_details['id']); ?></h2>
                    <a href="orders.php" class="btn btn-secondary float-end">Retour à la liste des commandes</a>
                </div>
                <div class="card-body">
                    <p><strong>Utilisateur:</strong> <?php echo htmlspecialchars($order_details['username']); ?> (<?php echo htmlspecialchars($order_details['email']); ?>)</p>
                    <p><strong>Total:</strong> $<?php echo number_format($order_details['total'], 2); ?></p>
                    <p><strong>Date de la commande:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order_details['created_at']))); ?></p>
                    <p>
                        <strong>Statut actuel:</strong> <span class="badge bg-<?php echo get_status_color($order_details['status']); ?>"><?php echo htmlspecialchars(ucfirst($order_details['status'])); ?></span>
                    </p>

                    <h4 class="mt-4">Articles commandés:</h4>
                    <?php if (!empty($order_items)): ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th>Prix unitaire</th>
                                    <th>Sous-total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if ($item['main_image']): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($item['main_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="width: 50px; height: auto;">
                                            <?php else: ?>
                                                Pas d'image
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td>$<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Aucun article pour cette commande.</p>
                    <?php endif; ?>

                    <h4 class="mt-4">Modifier le statut:</h4>
                    <form action="orders.php" method="POST" class="d-flex align-items-center">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_details['id']); ?>">
                        <select name="new_status" class="form-select me-2" style="max-width: 200px;"> 
                            <option value="pending" <?php echo ($order_details['status'] == 'pending') ? 'selected' : ''; ?>>En attente</option>
                            <option value="processing" <?php echo ($order_details['status'] == 'processing') ? 'selected' : ''; ?>>En cours de traitement</option>
                            <option value="shipped" <?php echo ($order_details['status'] == 'shipped') ? 'selected' : ''; ?>>Expédiée</option>
                            <option value="delivered" <?php echo ($order_details['status'] == 'delivered') ? 'selected' : ''; ?>>Livrée</option>
                            <option value="cancelled" <?php echo ($order_details['status'] == 'cancelled') ? 'selected' : ''; ?>>Annulée</option>
                        </select>
                        <button type="submit" class="button">Mettre à jour</button>
                    </form>
                </div>
            </div>

        <?php else: // Si on affiche la liste de toutes les commandes ?>
            <?php if (!empty($orders)): ?>
                <table class="admin-table"> <thead>
                        <tr>
                            <th>ID Commande</th>
                            <th>Utilisateur</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td>$<?php echo number_format($order['total'], 2); ?></td>
                                <td><span class="badge bg-<?php echo get_status_color($order['status']); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))); ?></td>
                                <td>
                                    <a href="orders.php?view=<?php echo htmlspecialchars($order['id']); ?>" class="button button-small">Voir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">Aucune commande trouvée pour le moment.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
</body>
</html>

<?php
// Assurez-vous que le footer n'est inclus qu'une seule fois
// include '../includes/footer.php'; // Déjà inclus au début du fichier
?>

<?php
// Fonction d'aide pour les couleurs de statut (à ajouter à functions.php si elle n'existe pas)
// Il est préférable de la garder dans functions.php pour la réutiliser
if (!function_exists('get_status_color')) {
    function get_status_color($status) {
        switch ($status) {
            case 'pending':
                return 'warning'; // Jaune
            case 'processing':
                return 'info';    // Bleu
            case 'shipped':
                return 'primary'; // Bleu foncé
            case 'delivered':
                return 'success'; // Vert
            case 'cancelled':
                return 'danger';  // Rouge
            default:
                return 'secondary'; // Gris par défaut
        }
    }
}
?>