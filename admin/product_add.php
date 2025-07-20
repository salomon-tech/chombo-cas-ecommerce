<?php
// chombo_cas/admin/product_add.php

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// --- Vérification de l'authentification admin ---
check_admin_auth(); // Fonction de functions.php qui redirige si non autorisé
// --- Fin de la vérification ---

$categories = get_all_categories(); // Récupère les catégories pour le formulaire

// Initialiser les variables pour que les champs du formulaire conservent leurs valeurs après une erreur
$product_name = $_POST['name'] ?? '';
$product_description = $_POST['description'] ?? '';
$product_price = $_POST['price'] ?? '';
$product_stock = $_POST['stock'] ?? '';
$product_category_id = $_POST['category_id'] ?? '';
$product_is_active = isset($_POST['is_active']) ? 1 : 1; // Par défaut à 1 (coché) si le formulaire n'a pas été soumis, ou prend la valeur soumise

// --- Logique de traitement du formulaire d'ajout de produit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_data = [];
    $errors = [];

    // Nettoyage et validation des entrées
    $product_data['name'] = clean_input($_POST['name'] ?? '');
    $product_data['description'] = clean_input($_POST['description'] ?? '');
    $product_data['price'] = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $product_data['stock'] = filter_var($_POST['stock'] ?? 0, FILTER_VALIDATE_INT);
    $product_data['category_id'] = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
    $product_data['is_active'] = isset($_POST['is_active']) ? 1 : 0; // Checkbox

    // Mettre à jour les variables pour réafficher les valeurs dans le formulaire en cas d'erreur
    $product_name = $product_data['name'];
    $product_description = $product_data['description'];
    $product_price = $_POST['price'] ?? ''; // Utiliser la valeur brute pour l'affichage si la validation échoue
    $product_stock = $_POST['stock'] ?? ''; // Utiliser la valeur brute pour l'affichage si la validation échoue
    $product_category_id = $product_data['category_id'];
    $product_is_active = $product_data['is_active'];


    // Validation des données (simple, à renforcer)
    if (empty($product_data['name'])) { $errors[] = "Le nom du produit est requis."; }
    if ($product_data['price'] === false || $product_data['price'] <= 0) { $errors[] = "Le prix doit être un nombre positif."; }
    if ($product_data['stock'] === false || $product_data['stock'] < 0) { $errors[] = "Le stock doit être un nombre entier positif ou nul."; }
    if ($product_data['category_id'] === false || !in_array($product_data['category_id'], array_column($categories, 'id'))) {
        $errors[] = "Catégorie invalide.";
    }

    $image_name = null;
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/'; // Le répertoire uploads/ est à la racine de chombo_cas/
        $image_name = handle_image_upload($_FILES['main_image'], $upload_dir); // Fonction de functions.php
        if (!$image_name) {
            $errors[] = "Erreur lors du téléchargement de l'image.";
        }
    } else {
        $errors[] = "Une image principale est requise.";
    }
    $product_data['main_image'] = $image_name;


    if (empty($errors)) {
        if (add_product($product_data)) { // Fonction de functions.php
            set_message('success', 'Produit ajouté avec succès !');
            redirect('products.php'); // Redirige vers la liste des produits
        } else {
            set_message('error', 'Erreur lors de l\'ajout du produit à la base de données.');
        }
    } else {
        // Les messages d'erreur sont déjà définis par set_message dans la boucle ci-dessous
        foreach ($errors as $error) {
            set_message('error', $error); // Redéfinir les messages pour qu'ils s'affichent
        }
    }
}

// --- Fin de la logique PHP ---

include '../includes/header.php'; // Notez le chemin relatif
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Produit - Admin</title>
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
            max-width: 800px;
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
        
        /* Styles du formulaire */
        form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="file"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #80bdff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        /* Checkbox personnalisé */
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
            transform: scale(1.3);
        }
        
        /* Styles des boutons */
        .button {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .button-primary {
            background-color: #007bff;
            color: white;
            display: block;
            width: 100%;
            margin-top: 20px;
        }
        
        .button-primary:hover {
            background-color: #0069d9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Style pour le champ fichier */
        input[type="file"] {
            padding: 8px;
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
                padding: 15px;
            }
            
            .admin-page h1 {
                font-size: 28px;
            }
            
            form {
                padding: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .form-group input[type="text"],
            .form-group input[type="number"],
            .form-group input[type="file"],
            .form-group select,
            .form-group textarea {
                padding: 10px 12px;
                font-size: 15px;
            }
            
            .button {
                padding: 10px 20px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <main class="admin-page">
        <div class="container">
            <?php display_message(); ?>

            <h1>Ajouter un nouveau produit</h1>

            <form action="product_add.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Nom du produit:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($product_description); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Prix (USD):</label>
                    <input type="number" id="price" name="price" step="0.01" min="0.01" value="<?php echo htmlspecialchars($product_price); ?>" required>
                </div>
                <div class="form-group">
                    <label for="stock">Stock:</label>
                    <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($product_stock); ?>" required>
                </div>
                <div class="form-group">
                    <label for="main_image">Image principale:</label>
                    <input type="file" id="main_image" name="main_image" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label for="category_id">Catégorie:</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Sélectionnez une catégorie --</option>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?php echo htmlspecialchars($category['id']); ?>"
                                <?php echo ($product_category_id == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        <?php echo ($product_is_active == 1) ? 'checked' : ''; ?>>
                    <label for="is_active">Produit actif</label>
                </div>
                <button type="submit" class="button button-primary">Ajouter le produit</button>
            </form>
        </div>
    </main>
</body>
</html>

<?php
include '../includes/footer.php';
?>