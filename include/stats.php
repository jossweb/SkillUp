<?php

require_once("./include/connectdb.php");

function getStats() {
    $db = connectDB();
    $stats = [
        'utilisateurs' => 0,
        'cours' => 0,
        'profs' => 0
    ];
    
    try {
        $sql = "SELECT COUNT(*) as total FROM Utilisateurs";
        $query = $db->query($sql);
        $stats['utilisateurs'] = $query->fetch(PDO::FETCH_ASSOC)['total'];
        
        $sql = "SELECT COUNT(*) as total FROM Cours";
        $query = $db->query($sql);
        $stats['cours'] = $query->fetch(PDO::FETCH_ASSOC)['total'];
        
        $sql = "SELECT COUNT(*) as total FROM Utilisateurs WHERE role = 'professeur'";
        $query = $db->query($sql);
        $stats['profs'] = $query->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (PDOException $e) {
        if (DEBUG) {
            error_log("Erreur lors de la récupération des stats: " . $e->getMessage());
        }
    }
    
    return $stats;
}