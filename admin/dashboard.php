<?php
// chombo_cas/admin/dashboard.php

session_start();
require_once '../includes/db_connect.php'; // Chemin relatif
require_once '../includes/functions.php';

// --- Vérification de l'authentification admin ---
check_admin_auth(); // Fonction de functions.php qui redirige si non admin
// --- Fin de la vérification ---

// --- Logique PHP pour le tableau de bord ---
$stats = get_admin_dashboard_stats(); // Fonction de functions.php

// --- Fin de la logique PHP ---

include '../includes/header.php'; // Chemin relatif
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Admin</title>
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
            overflow-x: auto; /* Permet le défilement horizontal si nécessaire */
        }
        
        /* Styles pour la page admin */
        .admin-page {
            padding: 40px 0;
            min-width: fit-content; /* S'adapte au contenu */
        }
        
        .admin-page h1 {
            color: #2c3e50;
            margin-bottom: 40px;
            font-size: 32px;
            text-align: center;
        }
        
        .admin-page h2 {
            color: #2c3e50;
            margin: 40px 0 20px;
            font-size: 24px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        /* Conteneurs horizontaux */
        .horizontal-scroll-container {
            display: flex;
            gap: 20px;
            padding-bottom: 20px; /* Espace pour l'ombre */
            margin-bottom: 40px;
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: #007bff #f1f1f1;
        }
        
        /* Style de la barre de défilement */
        .horizontal-scroll-container::-webkit-scrollbar {
            height: 8px;
        }
        
        .horizontal-scroll-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .horizontal-scroll-container::-webkit-scrollbar-thumb {
            background-color: #007bff;
            border-radius: 10px;
        }
        
        /* Cartes de statistiques horizontales */
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            min-width: 200px;
            flex-shrink: 0;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card h3 {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .stat-card p {
            color: #2c3e50;
            font-size: 28px;
            font-weight: 700;
        }
        
        /* Actions rapides horizontales */
        .admin-actions-grid {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            overflow-x: auto;
            padding-bottom: 20px;
        }
        
        /* Styles des boutons */
        .button {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .button-success {
            background-color: #28a745;
            color: white;
        }
        
        .button-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .button {
            background-color: #007bff;
            color: white;
        }
        
        .button:hover {
            background-color: #0069d9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
            .stat-card {
                min-width: 180px;
                padding: 20px;
            }
            
            .button {
                padding: 10px 15px;
                font-size: 14px;
            }
            
            .admin-page h1 {
                font-size: 28px;
            }
            
            .admin-page h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <main class="admin-page">
        <div class="container">
            <?php display_message(); ?>

            <h1>Tableau de Bord Administrateur</h1>

            <div class="horizontal-scroll-container">
                <div class="stat-card">
                    <h3>Total Produits</h3>
                    <p><?php echo htmlspecialchars($stats['total_products']); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Utilisateurs</h3>
                    <p><?php echo htmlspecialchars($stats['total_users']); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Commandes</h3>
                    <p><?php echo htmlspecialchars($stats['total_orders']); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Commandes en Attente</h3>
                    <p><?php echo htmlspecialchars($stats['pending_orders']); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Revenu Total (Livré)</h3>
                    <p><?php echo htmlspecialchars(number_format($stats['total_revenue'], 2, ',', ' ')); ?> USD</p>
                </div>
            </div>

            <h2>Actions rapides</h2>
            <div class="admin-actions-grid">
                <a href="product_add.php" class="button button-success">Ajouter un produit</a>
                <a href="products.php" class="button">Gérer les produits</a>
                <a href="orders.php" class="button">Voir les commandes</a>
                <a href="categories.php" class="button">Gérer les catégories</a>
            </div>
        </div>
    </main>
</body>
</html>

<?php
include '../includes/footer.php';
?>