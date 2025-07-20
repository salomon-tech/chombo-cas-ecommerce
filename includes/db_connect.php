// includes/db_connect.php
<?php
$db_host = 'localhost';
$db_name = 'chombo_cas';
$db_user = 'root'; // !! À changer pour des identifiants sécurisés en production !!
$db_pass = '';     // !! À changer pour un mot de passe fort en production !!

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Pour afficher les erreurs PDO en développement
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Récupère les résultats en tableau associatif par défaut
} catch (PDOException $e) {
    // En production, ne pas afficher l'erreur brute à l'utilisateur, mais la logger
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>