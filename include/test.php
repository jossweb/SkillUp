<?php

// Tester la connexion à la base de données

require_once("./config.php"); 

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connexion à la base de données réussie ! <br>";
    echo "Base de données : " . DB_NAME . "<br>";
    echo "Hôte : " . DB_HOST . ":" . DB_PORT;

} catch (PDOException $e) {
    echo "Échec de la connexion à la base de données : " . $e->getMessage();
}