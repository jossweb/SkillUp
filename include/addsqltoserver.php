<?php
    require_once("connectdb.php");
    $db = connectDB();
    $sql = "CREATE TABLE sessions ( id SERIAL PRIMARY KEY, user_id INTEGER NOT NULL, token VARCHAR(255) UNIQUE NOT NULL, ip_address VARCHAR(45) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, expires_at TIMESTAMP NOT NULL, FOREIGN KEY (user_id) REFERENCES Utilisateurs(id) ON DELETE CASCADE );";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC); 
    echo $result;
?>