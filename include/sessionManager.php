<?php
require_once("../include/connectdb.php"); 

function IsConnected($client_ip) :bool {
    $db = connectDB();
    $sql = "SELECT user_id, expires_at FROM sessions WHERE ip_address = :client_ip";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':client_ip', $client_ip);
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
    $sql = "DELETE FROM sessions WHERE ip_address = :client_ip";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':client_ip', $client_ip);
    $stmt->execute();

    return false;
}
?>