<?php
// app/functions.php

// 1. Fonctions de configuration et de connexion à la base de données
// (Bien que la connexion soit dans db_connect.php, les fonctions d'interaction y seront ici)

// La variable $pdo est disponible globalement après l'inclusion de db_connect.php
// Il est préférable de la passer en argument aux fonctions pour une meilleure isolation,
// mais pour une structure très simple, l'utilisation de `global $pdo;` est acceptable.

// Exemple : Obtenir la connexion PDO (si non passée directement)
function get_db_connection() {
    global $pdo; // Assurez-vous que $pdo est initialisé dans includes/db_connect.php
    return $pdo;
}

// 2. Fonctions d'aide (Helpers)
// -----------------------------

/**
 * Redirige l'utilisateur vers une URL donnée.
 * @param string $url L'URL de redirection.
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Nettoie et sécurise les données d'entrée utilisateur.
 * Protège contre les injections XSS de base.
 * @param string $data Les données à nettoyer.
 * @return string Les données nettoyées et échappées.
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Affiche un message flash stocké en session.
 */
function display_message() {
    if (isset($_SESSION['message'])) {
        $type = htmlspecialchars($_SESSION['message']['type']);
        $text = htmlspecialchars($_SESSION['message']['text']);
        echo '<div class="alert alert-' . $type . '">' . $text . '</div>';
        unset($_SESSION['message']); // Supprime le message après l'affichage
    }
}

/**
 * Définit un message flash à afficher.
 * @param string $type Le type de message (e.g., 'success', 'error', 'warning', 'info').
 * @param string $text Le texte du message.
 */
function set_message($type, $text) {
    $_SESSION['message'] = ['type' => $type, 'text' => $text];
}


// 3. Fonctions d'authentification et de gestion des utilisateurs
// -------------------------------------------------------------

/**
 * Hache un mot de passe de manière sécurisée.
 * @param string $password Le mot de passe en clair.
 * @return string Le mot de passe haché.
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Vérifie un mot de passe haché.
 * @param string $password Le mot de passe en clair soumis par l'utilisateur.
 * @param string $hashed_password Le mot de passe haché stocké en base de données.
 * @return bool Vrai si les mots de passe correspondent, faux sinon.
 */
function verify_password($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}

/**
 * Tente de connecter un utilisateur.
 * @param string $email L'email de l'utilisateur.
 * @param string $password Le mot de passe en clair.
 * @return bool Vrai si la connexion est réussie, faux sinon.
 */
function login_user($email, $password) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT id, username, email, password, is_admin FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user && verify_password($password, $user['password'])) {
        // Régénérer l'ID de session pour prévenir la fixation de session
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_admin'] = $user['is_admin'];
        set_message('success', 'Connexion réussie !');
        return true;
    } else {
        set_message('error', 'Email ou mot de passe incorrect.');
        return false;
    }
}

/**
 * Déconnecte l'utilisateur actuel.
 */
function logout_user() {
    $_SESSION = array(); // Vide toutes les variables de session
    session_destroy();    // Détruit la session
    set_message('info', 'Vous avez été déconnecté.');
    redirect('../phone shop/login.php'); // Redirige vers la page de connexion
}

/**
 * Vérifie si un utilisateur est connecté.
 * @return bool Vrai si l'utilisateur est connecté, faux sinon.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur connecté est un administrateur.
 * @return bool Vrai si l'utilisateur est un admin, faux sinon.
 */
function is_admin_logged_in() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 1;
}

/**
 * Vérifie l'authentification admin et redirige si non autorisé.
 */
function check_admin_auth() {
    if (!is_logged_in() || !is_admin_logged_in()) {
        set_message('error', 'Accès non autorisé à la zone d\'administration.');
        // Change la redirection pour pointer vers le nouveau fichier de connexion admin
        redirect('../phone shop/admin/admin_login.php');
    }
}

/**
 * Enregistre un nouvel utilisateur.
 * @param string $username
 * @param string $email
 * @param string $password_raw Le mot de passe en clair.
 * @return bool Vrai si l'inscription est réussie, faux sinon.
 */
function register_user($username, $email, $password_raw) {
    $pdo = get_db_connection();

    // Nettoyage et validation (simplifié, à renforcer)
    $username = clean_input($username);
    $email = clean_input($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_message('error', 'Format d\'email invalide.');
        return false;
    }
    // Vérifier si l'email ou le nom d'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email OR username = :username");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        set_message('error', 'Cet email ou nom d\'utilisateur est déjà utilisé.');
        return false;
    }

    $hashed_password = hash_password($password_raw);
    return create_user_db($username, $email, $hashed_password); // APPEL CORRIGÉ ICI
}

// 4. Fonctions d'interaction avec la base de données (CRUD pour les entités)
// -----------------------------------------------------------------------

// --- Fonctions Utilisateurs ---
/**
 * Insère un nouvel utilisateur dans la base de données.
 * @param string $username
 * @param string $email
 * @param string $hashed_password
 * @return bool
 */
function create_user_db($username, $email, $hashed_password) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    return $stmt->execute();
}

/**
 * Récupère un utilisateur par son ID.
 * @param int $id
 * @return array|false L'utilisateur ou faux si non trouvé.
 */
function get_user_by_id($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, username, email, is_admin FROM users WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Récupère tous les utilisateurs.
 * @return array
 */
function get_all_users() {
    global $pdo;
    $stmt = $pdo->query("SELECT id, username, email, is_admin, created_at FROM users");
    return $stmt->fetchAll();
}

// --- Fonctions Produits ---
/**
 * Récupère tous les produits, éventuellement avec pagination, et une option pour inclure les inactifs.
 * @param int $limit Le nombre de produits à récupérer (pour la pagination).
 * @param int $offset L'offset à partir duquel commencer la récupération (pour la pagination).
 * @param bool $include_inactive Si vrai, inclut les produits marqués comme inactifs.
 * @return array
 */
function get_all_products($limit = null, $offset = null, $include_inactive = false) { // <-- MODIFIÉ ICI !
    global $pdo;
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id";

    // Ajoute la clause WHERE si on ne veut PAS inclure les produits inactifs
    if (!$include_inactive) {
        $sql .= " WHERE p.is_active = TRUE";
    }

    $sql .= " ORDER BY p.created_at DESC"; // Assurez-vous que l'ORDER BY est toujours là

    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT :limit OFFSET :offset";
    }
    $stmt = $pdo->prepare($sql);
    if ($limit !== null && $offset !== null) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Récupère les détails d'un produit spécifique.
 * @param int $product_id
 * @return array|false
 */
function get_product_details($product_id) {
    global $pdo;
    // La clause `p.is_active = TRUE` est maintenue ici car la vue publique ne devrait pas voir les produits inactifs
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = :id AND p.is_active = TRUE");
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Ajoute un nouveau produit.
 * @param array $data Les données du produit (name, description, price, etc.)
 * @return bool
 */
function add_product($data) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, main_image, category_id, is_active) VALUES (:name, :description, :price, :stock, :main_image, :category_id, :is_active)");
    $stmt->bindValue(':name', clean_input($data['name']));
    $stmt->bindValue(':description', clean_input($data['description']));
    $stmt->bindValue(':price', $data['price']); // Valider ce champ avant
    $stmt->bindValue(':stock', $data['stock'], PDO::PARAM_INT); // Valider ce champ avant
    $stmt->bindValue(':main_image', clean_input($data['main_image']));
    $stmt->bindValue(':category_id', $data['category_id'], PDO::PARAM_INT);
    $stmt->bindValue(':is_active', isset($data['is_active']) ? 1 : 0, PDO::PARAM_INT); // Checkbox
    return $stmt->execute();
}

/**
 * Met à jour un produit existant.
 * @param int $product_id
 * @param array $data Les nouvelles données du produit.
 * @return bool
 */
function update_product($product_id, $data) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price, stock = :stock, main_image = :main_image, category_id = :category_id, is_active = :is_active WHERE id = :id");
    $stmt->bindValue(':name', clean_input($data['name']));
    $stmt->bindValue(':description', clean_input($data['description']));
    $stmt->bindValue(':price', $data['price']);
    $stmt->bindValue(':stock', $data['stock'], PDO::PARAM_INT);
    $stmt->bindValue(':main_image', clean_input($data['main_image']));
    $stmt->bindValue(':category_id', $data['category_id'], PDO::PARAM_INT);
    $stmt->bindValue(':is_active', isset($data['is_active']) ? 1 : 0, PDO::PARAM_INT);
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Supprime un produit.
 * @param int $product_id
 * @return bool
 */
function delete_product($product_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    return $stmt->execute();
}

// --- Fonctions Catégories ---
/**
 * Récupère toutes les catégories.
 * @return array
 */
function get_all_categories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    return $stmt->fetchAll();
}

/**
 * Ajoute une nouvelle catégorie.
 * @param string $name Le nom de la catégorie.
 * @return bool
 */
function add_category($name) {
    global $pdo;
    $name = clean_input($name);
    // Vérifier l'existence avant d'ajouter
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = :name");
    $stmt_check->bindParam(':name', $name);
    $stmt_check->execute();
    if ($stmt_check->fetchColumn() > 0) {
        set_message('error', 'Cette catégorie existe déjà.');
        return false;
    }

    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
    $stmt->bindParam(':name', $name);
    return $stmt->execute();
}

/**
 * Met à jour une catégorie existante.
 * @param int $category_id
 * @param string $new_name
 * @return bool
 */
function update_category($category_id, $new_name) {
    global $pdo;
    $new_name = clean_input($new_name);
    $stmt = $pdo->prepare("UPDATE categories SET name = :name WHERE id = :id");
    $stmt->bindParam(':name', $new_name);
    $stmt->bindParam(':id', $category_id, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Supprime une catégorie.
 * @param int $category_id
 * @return bool
 */
function delete_category($category_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->bindParam(':id', $category_id, PDO::PARAM_INT);
    return $stmt->execute();
}

// --- Fonctions Panier (gestion en session) ---
/**
 * Initialise le panier si non existant.
 */
function initialize_cart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Ajoute un produit au panier.
 * @param int $product_id
 * @param int $quantity
 * @return bool
 */
function add_to_cart($product_id, $quantity) {
    initialize_cart();
    $quantity = (int)$quantity; // Assurer que la quantité est un entier

    // Récupérer le produit pour valider son existence et stock (optionnel mais recommandé)
    $product = get_product_details($product_id); // Réutilise la fonction existante
    if (!$product || $product['stock'] < $quantity) {
        set_message('error', 'Produit introuvable ou stock insuffisant.');
        return false;
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    set_message('success', 'Produit ajouté au panier !');
    return true;
}

/**
 * Met à jour la quantité d'un produit dans le panier.
 * @param int $product_id
 * @param int $quantity
 */
function update_cart_quantity($product_id, $quantity) {
    initialize_cart();
    $quantity = (int)$quantity;
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$product_id]);
        set_message('info', 'Produit retiré du panier.');
    } else {
        // Vérifier stock avant de mettre à jour
        $product = get_product_details($product_id);
        if ($product && $product['stock'] >= $quantity) {
            $_SESSION['cart'][$product_id] = $quantity;
            set_message('success', 'Quantité du panier mise à jour.');
        } else {
            set_message('error', 'Stock insuffisant pour cette quantité.');
            return false;
        }
    }
    return true;
}

/**
 * Supprime un produit du panier.
 * @param int $product_id
 */
function remove_from_cart($product_id) {
    initialize_cart();
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        set_message('info', 'Produit retiré du panier.');
    }
}

/**
 * Récupère les détails des articles du panier, y compris les informations produit actuelles.
 * @return array Tableau d'articles du panier avec leurs détails.
 */
function get_cart_items_details() {
    initialize_cart();
    $cart_details = [];
    if (empty($_SESSION['cart'])) {
        return $cart_details;
    }

    $product_ids = array_keys($_SESSION['cart']);
    // Crée une chaîne de placeholders pour la requête SQL
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

    global $pdo;
    $stmt = $pdo->prepare("SELECT id, name, price, main_image, stock FROM products WHERE id IN ($placeholders) AND is_active = TRUE");
    // BindParam n'est pas pratique pour un nombre variable de paramètres. Utilisez execute avec un tableau.
    $stmt->execute($product_ids);
    
    // MODIFICATION APPLIQUÉE ICI : Récupérez d'abord tous les produits en associatif
    $products_raw = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    
    // Ensuite, réorganisez-les par ID pour un accès facile
    $products_in_cart = [];
    foreach ($products_raw as $product) {
        $products_in_cart[$product['id']] = $product;
    }

    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        if (isset($products_in_cart[$product_id])) {
            $product = $products_in_cart[$product_id];
            $cart_details[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'main_image' => $product['main_image'],
                'quantity' => $quantity,
                'subtotal' => $product['price'] * $quantity,
                'available_stock' => $product['stock'] // Utile pour la vérification
            ];
        } else {
            // Si le produit n'existe plus ou n'est plus actif, le retirer du panier
            unset($_SESSION['cart'][$product_id]);
            set_message('warning', 'Un produit a été retiré de votre panier car il n\'est plus disponible.');
        }
    }
    return $cart_details;
}

/**
 * Calcule le total du panier.
 * @return float Le total du panier.
 */
function get_cart_total() {
    $cart_details = get_cart_items_details();
    $total = 0;
    foreach ($cart_details as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

/**
 * Vide le panier.
 */
function clear_cart() {
    unset($_SESSION['cart']);
}

// --- Fonctions Commandes ---
/**
 * Traite la finalisation d'une commande.
 * Effectue une transaction atomique.
 * @param int $user_id L'ID de l'utilisateur qui passe la commande.
 * @param array $cart_details Les détails des articles du panier (provenant de get_cart_items_details()).
 * @return int|false L'ID de la commande si réussie, false sinon.
 */
function place_order($user_id, $cart_details) {
    global $pdo;

    if (empty($cart_details)) {
        set_message('error', 'Votre panier est vide.');
        return false;
    }

    $pdo->beginTransaction(); // Démarrer la transaction

    try {
        $total_order = 0;
        foreach ($cart_details as $item) {
            // Vérifier le stock une dernière fois avant de commander
            $product_current = get_product_details($item['id']);
            if (!$product_current || $product_current['stock'] < $item['quantity']) {
                $pdo->rollBack();
                set_message('error', 'Stock insuffisant pour ' . htmlspecialchars($item['name']) . '. Veuillez ajuster la quantité.');
                return false;
            }
            $total_order += $item['price'] * $item['quantity'];
        }

        // 1. Insérer la commande principale
        $stmt_order = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (:user_id, :total, 'pending')");
        $stmt_order->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_order->bindParam(':total', $total_order);
        $stmt_order->execute();
        $order_id = $pdo->lastInsertId();

        // 2. Insérer les articles de la commande et ajuster les stocks
        foreach ($cart_details as $item) {
            $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
            $stmt_item->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $stmt_item->bindParam(':product_id', $item['id'], PDO::PARAM_INT);
            $stmt_item->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $stmt_item->bindParam(':price', $item['price']); // Prix au moment de la commande
            $stmt_item->execute();

            // Mettre à jour le stock
            $stmt_stock = $pdo->prepare("UPDATE products SET stock = stock - :quantity WHERE id = :product_id");
            $stmt_stock->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $stmt_stock->bindParam(':product_id', $item['id'], PDO::PARAM_INT);
            $stmt_stock->execute();
        }

        $pdo->commit(); // Valider toutes les opérations si tout s'est bien passé
        clear_cart(); // Vider le panier après commande réussie
        set_message('success', 'Votre commande a été passée avec succès !');
        return $order_id;

    } catch (PDOException $e) {
        $pdo->rollBack(); // Annuler toutes les opérations en cas d'erreur
        error_log("Erreur de commande: " . $e->getMessage()); // Log l'erreur
        set_message('error', 'Une erreur est survenue lors de votre commande. Veuillez réessayer.');
        return false;
    }
}

/**
 * Récupère toutes les commandes (pour l'admin).
 * @return array
 */
function get_all_orders() {
    global $pdo;
    $stmt = $pdo->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
    return $stmt->fetchAll();
}

/**
 * Récupère les détails d'une commande spécifique.
 * @param int $order_id
 * @return array|false
 */
function get_order_details($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = :order_id");
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Récupère les articles d'une commande spécifique.
 * @param int $order_id
 * @return array
 */
function get_order_items($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.main_image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Met à jour le statut d'une commande.
 * @param int $order_id
 * @param string $new_status ('pending', 'processing', 'shipped', 'delivered', 'cancelled')
 * @return bool
 */
function update_order_status($order_id, $new_status) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $new_status);
    $stmt->bindParam(':id', $order_id, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Récupère les commandes d'un utilisateur spécifique.
 * @param int $user_id
 * @return array
 */
function get_user_orders($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}


// 5. Fonctions de gestion des fichiers (Uploads)
// ---------------------------------------------

/**
 * Gère le téléchargement sécurisé d'une image de produit.
 * @param array $file_input Le tableau $_FILES['input_name'] pour le fichier.
 * @param string $upload_dir Le répertoire de destination des uploads.
 * @return string|false Le chemin relatif de l'image si succès, false sinon.
 */
function handle_image_upload($file_input, $upload_dir) {
    if (!isset($file_input) || $file_input['error'] !== UPLOAD_ERR_OK) {
        set_message('error', 'Erreur lors du téléchargement du fichier.');
        return false;
    }

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_input['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_mimes)) {
        set_message('error', 'Seules les images JPEG, PNG et GIF sont autorisées.');
        return false;
    }

    // Vérifier si c'est bien une image
    if (!getimagesize($file_input['tmp_name'])) {
        set_message('error', 'Le fichier n\'est pas une image valide.');
        return false;
    }

    $max_file_size = 5 * 1024 * 1024; // 5 Mo
    if ($file_input['size'] > $max_file_size) {
        set_message('error', 'Le fichier est trop volumineux (max 5 Mo).');
        return false;
    }

    $extension = pathinfo($file_input['name'], PATHINFO_EXTENSION);
    $new_file_name = uniqid('img_', true) . '.' . strtolower($extension);
    $destination = $upload_dir . $new_file_name;

    if (move_uploaded_file($file_input['tmp_name'], $destination)) {
        return $new_file_name; // Retourne juste le nom du fichier pour la BDD
    } else {
        set_message('error', 'Impossible de déplacer le fichier téléchargé.');
        return false;
    }
}

// 6. Fonctions de Tableau de Bord Admin
// ------------------------------------

/**
 * Récupère des statistiques pour le tableau de bord admin.
 * @return array
 */
function get_admin_dashboard_stats() {
    global $pdo;
    $stats = [];

    // Total des produits
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $stats['total_products'] = $stmt->fetchColumn();

    // Total des utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();

    // Total des commandes
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $stats['total_orders'] = $stmt->fetchColumn();

    // Revenu total (simple, juste la somme des totaux de commande)
    $stmt = $pdo->query("SELECT SUM(total) FROM orders WHERE status = 'delivered'");
    $stats['total_revenue'] = $stmt->fetchColumn();
    $stats['total_revenue'] = $stats['total_revenue'] ? round($stats['total_revenue'], 2) : 0;


    // Commandes en attente
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $stats['pending_orders'] = $stmt->fetchColumn();

    return $stats;
}

?>