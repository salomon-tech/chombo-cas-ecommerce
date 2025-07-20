<?php
// chombo_cas/logout.php

session_start();
require_once 'includes/db_connect.php'; // Inclut la connexion PDO
require_once 'includes/functions.php'; // Inclut la fonction logout_user()

logout_user(); // Fonction de functions.php
 // Redirige vers la page d'accueil après déconnexion

// Note: logout_user() contient déjà une redirection, donc le code après ne sera pas exécuté.
?>