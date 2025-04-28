<?php
require_once("connectdb.php"); 

function IsConnected(): bool {
    $db = connectDB();

    // Supprimer les sessions expirées à chaque appel
    $now = new DateTime();
    $nowFormatted = $now->format('Y-m-d H:i:s');

    $sql = "DELETE FROM sessions WHERE expires_at < :now";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':now', $nowFormatted);
    $stmt->execute();

    if (!isset($_COOKIE['user_token'])) {
        return false; 
    }
    $sql = "SELECT user_id, expires_at FROM sessions WHERE token = :token";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':token', $_COOKIE['user_token'], PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        return false;
    }

    $expires_at = new DateTime($result['expires_at']);
    if ($expires_at > $now) {
        return true;  
    }

    return false; 
}
?>