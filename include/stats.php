<?php

require_once("connectdb.php");

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

function getTrendingCourses($limit = 4) {
    $db = connectDB();
    $trendingCourses = [];
    
    try {
        $sql = "SELECT c.id, c.nom, c.illustration_url, c.description, cat.nom as categorie_nom, 
                COUNT(i.id) as nombre_inscrits
                FROM Cours c
                LEFT JOIN Inscriptions i ON c.id = i.cours_id
                LEFT JOIN Categories cat ON c.categorie_id = cat.id
                GROUP BY c.id
                ORDER BY nombre_inscrits DESC, c.date_creation DESC
                LIMIT :limit";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $trendingCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        if (DEBUG) {
            error_log("Erreur lors de la récupération des cours tendances: " . $e->getMessage());
        }
    }
    
    return $trendingCourses;
}