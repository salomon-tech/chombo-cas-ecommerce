<?php
// chombo_cas/admin/categories.php

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

check_admin_auth();

// Gérer l'ajout/modification/suppression de catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $category_name = $_POST['category_name'] ?? '';
        if (empty($category_name)) {
            set_message('error', 'Le nom de la catégorie ne peut pas être vide.');
        } elseif (add_category($category_name)) {
            set_message('success', 'Catégorie ajoutée avec succès !');
        } else {
            // Le message d'erreur est déjà défini par add_category si la catégorie existe
        }
    } elseif (isset($_POST['update_category'])) {
        $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
        $new_name = $_POST['new_name'] ?? '';
        if (!$category_id || empty($new_name)) {
            set_message('error', 'ID de catégorie ou nom invalide.');
        } elseif (update_category($category_id, $new_name)) {
            set_message('success', 'Catégorie mise à jour avec succès !');
        } else {
            set_message('error', 'Erreur lors de la mise à jour de la catégorie.');
        }
    }
    redirect('categories.php');
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $category_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($category_id && delete_category($category_id)) {
        set_message('success', 'Catégorie supprimée avec succès !');
    } else {
        set_message('error', 'Erreur lors de la suppression de la catégorie ou ID invalide.');
    }
    redirect('categories.php');
}

$categories = get_all_categories();

include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories - Admin</title>
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
        
        .admin-page h2 {
            color: #2c3e50;
            margin: 30px 0 20px;
            font-size: 24px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        /* Styles du formulaire */
        .add-category-form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #495057;
            font-size: 16px;
        }
        
        .form-group input {
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            border-color: #80bdff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        /* Styles des boutons */
        .button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .button-success {
            background-color: #28a745;
            color: white;
        }
        
        .button-success:hover {
            background-color: #218838;
        }
        
        .button-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .button-secondary:hover {
            background-color: #5a6268;
        }
        
        .button-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .button-danger:hover {
            background-color: #c82333;
        }
        
        .button-small {
            padding: 6px 12px;
            font-size: 14px;
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
        }
        
        .admin-table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .admin-table tr:last-child td {
            border-bottom: none;
        }
        
        .admin-table input[type="text"] {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            min-width: 200px;
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
            .admin-table {
                display: block;
                overflow-x: auto;
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

            <h1>Gestion des Catégories</h1>

            <h2>Ajouter une nouvelle catégorie</h2>
            <form action="categories.php" method="POST" class="add-category-form">
                <div class="form-group">
                    <label for="category_name">Nom de la catégorie:</label>
                    <input type="text" id="category_name" name="category_name" required>
                </div>
                <button type="submit" name="add_category" class="button button-success">Ajouter la catégorie</button>
            </form>

            <h2>Catégories existantes</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)) : ?>
                        <?php foreach ($categories as $category) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['id']); ?></td>
                                <td>
                                    <form action="categories.php" method="POST" style="display:inline-block;">
                                        <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category['id']); ?>">
                                        <input type="text" name="new_name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                        <button type="submit" name="update_category" class="button button-secondary button-small">Modifier</button>
                                    </form>
                                </td>
                                <td>
                                    <a href="categories.php?action=delete&id=<?php echo htmlspecialchars($category['id']); ?>" class="button button-danger button-small" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Tous les produits associés seront "non classés".');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="3">Aucune catégorie trouvée.</td>
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