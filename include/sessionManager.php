<?php
require_once("../include/connectdb.php"); 

function IsConnected() : bool {
    $db = connectDB();
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
    $now = new DateTime();
    if ($expires_at > $now) {
        return true;  
    }
    $sql = "DELETE FROM sessions WHERE expires_at < :now";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':now', $now->format('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->execute();

    return false; 
}
?>