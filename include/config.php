<?php
// Configuration globale du site
define('SITE_NAME', 'SkillUp');

// Configuration des chemins
define('ASSETS_PATH', 'assets');
define('IMG_PATH', ASSETS_PATH . '/images');
define('CSS_PATH', ASSETS_PATH . '/css');
define('JS_PATH', ASSETS_PATH . '/js');

// Configuration de la base de données
define('DB_HOST', 'localhost'); // Hôte de la base de données
define('DB_PORT', 3306);        // Port (3306 par défaut pour MySQL)
define('DB_USER', 'root');      // Nom d'utilisateur
define('DB_PASS', '');          // Mot de passe 'mamp = root' autres = '' (pas de mdp)
define('DB_NAME', 'skillup');  // Nom de la base de données

define('DEBUG', true); //Si true alors les erreurs s'afficheront sur les pages 
?>