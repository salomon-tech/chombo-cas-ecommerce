<?php
// chombo_cas/orders.php

session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// --- Vérification de l'authentification utilisateur ---
// L'utilisateur doit être connecté pour voir ses commandes
if (!is_logged_in()) {
    set_message('error', 'Vous devez être connecté pour accéder à vos commandes.');
    redirect('login.php'); // Redirige vers la page de connexion
}
// --- Fin de la vérification ---

$user_id = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté
$user_orders = [];
$order_details = null;
$order_items = [];

// Logique pour afficher les détails d'une commande spécifique ou la liste
if (isset($_GET['view']) && filter_var($_GET['view'], FILTER_VALIDATE_INT)) {
    // Afficher les détails d'une commande spécifique
    $order_id_to_view = filter_var($_GET['view'], FILTER_VALIDATE_INT);
    
    // Récupérer les détails de la commande ET S'ASSURER QU'ELLE APPARTIENT À L'UTILISATEUR CONNECTÉ
    $stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = :order_id AND o.user_id = :user_id");
    $stmt->bindParam(':order_id', $order_id_to_view, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $order_details = $stmt->fetch();

    if ($order_details) {
        $order_items = get_order_items($order_id_to_view); // Cette fonction récupère déjà par order_id
    } else {
        set_message('error', 'Commande introuvable ou vous n\'avez pas la permission de la voir.');
        redirect('orders.php'); // Redirige vers la liste des commandes de l'utilisateur
    }
} else {
    // Afficher toutes les commandes de l'utilisateur par défaut
    $user_orders = get_user_orders($user_id);
}

include 'includes/header.php'; // Notez le chemin relatif
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Titre principal */
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 32px;
            text-align: center;
        }
        
        /* Carte de détails de commande */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .card-header h2 {
            color: #2c3e50;
            font-size: 24px;
            margin: 0;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Tableaux */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #dee2e6;
        }
        
        .table th {
            background-color: #343a40;
            color: white;
            font-weight: 600;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        /* Badges de statut */
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .bg-success {
            background-color: #28a745;
            color: white;
        }
        
        .bg-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .bg-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .bg-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .bg-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        /* Boutons */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .btn-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background-color: #138496;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 14px;
        }
        
        /* Alerte */
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        /* Utilitaires */
        .float-end {
            float: right;
        }
        
        .mb-4 {
            margin-bottom: 24px;
        }
        
        .mt-4 {
            margin-top: 24px;
        }
        
        /* Image produits */
        img {
            max-width: 100%;
            height: auto;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .float-end {
                float: none;
                align-self: flex-end;
            }
            
            .table {
                display: block;
                overflow-x: auto;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding: 15px;
            }
            
            h1 {
                font-size: 28px;
            }
            
            .card-header h2 {
                font-size: 20px;
            }
            
            .table th, .table td {
                padding: 8px 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <main class="user-orders-page">
        <div class="container">
            <?php display_message(); ?>

            <h1>Mes Commandes</h1>

            <?php if ($order_details): // Si on affiche les détails d'une commande spécifique ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h2>Détails de la commande #<?php echo htmlspecialchars($order_details['id']); ?></h2>
                        <a href="orders.php" class="btn btn-secondary float-end">Retour à mes commandes</a>
                    </div>
                    <div class="card-body">
                        <p><strong>Total:</strong> $<?php echo number_format($order_details['total'], 2); ?></p>
                        <p><strong>Date de la commande:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order_details['created_at']))); ?></p>
                        <p>
                            <strong>Statut:</strong> <span class="badge bg-<?php echo get_status_color($order_details['status']); ?>"><?php echo htmlspecialchars(ucfirst($order_details['status'])); ?></span>
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
                                                    <img src="uploads/<?php echo htmlspecialchars($item['main_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="width: 50px; height: auto;">
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
                    </div>
                </div>

            <?php else: // Si on affiche la liste de toutes les commandes de l'utilisateur ?>
                <?php if (!empty($user_orders)): ?>
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>ID Commande</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($user_orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td>$<?php echo number_format($order['total'], 2); ?></td>
                                    <td><span class="badge bg-<?php echo get_status_color($order['status']); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></td>
                                    <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))); ?></td>
                                    <td>
                                        <a href="orders.php?view=<?php echo htmlspecialchars($order['id']); ?>" class="btn btn-info btn-sm">Voir Détails</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">Vous n'avez pas encore passé de commande.</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

<?php
include 'includes/footer.php';

// Rappel: get_status_color() devrait être dans includes/functions.php
// pour une meilleure réutilisation.
// function get_status_color($status) { ... }
?>