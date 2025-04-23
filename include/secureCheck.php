<?php
    require_once("../include/connectdb.php");

    function LogsCheck() {
        $ip = GetIP() ;

        if (!empty($ip)) {
            $db =  connectDB();
            $dateH_check = (new DateTime('-30 minutes'))->format('Y-m-d H:i:s');
            $sql = 'SELECT COUNT(ApiLogs.id) AS nb_denied FROM ApiLogs WHERE ApiLogs.date_heure > :dateheure AND ApiLogs.succes = FALSE AND ip = :ip;';
            $request = $db->prepare($sql);
            $request->bindParam(':dateheure', $dateH_check );
            $request->bindParam(':ip', $ip);
            $request->execute();
            $result = $request->fetch(PDO::FETCH_ASSOC);
            if ($result['nb_denied'] < 10) {
                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }
    }
    function AddInLog($state){
        $db = connectDB();
        $ip = GetIP() ;
        $sql = 'INSERT INTO ApiLogs (ApiLogs.ip, ApiLogs.succes) VALUES (:ip, :success);';
        $request = $db->prepare($sql);
        $request->bindParam(':ip', $ip);
        $request->bindParam(':success', $state);
        $request->execute();
    }
    function generateToken() {

        $exists = 1;
        while ($exists > 0){
            $db = connectDB();
            $token = bin2hex(random_bytes(32)); 
            $sql = 'SELECT COUNT(*) FROM KeyTable WHERE token = :token';
            $request = $db->prepare($sql);
            $request->bindParam(':token', $token );
            $request->execute();
            $exists = $request->fetchColumn();
        } 
    
        return $token;
    }
    function GetIP(){
        $ip = $_SERVER['REMOTE_ADDR']; 
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $ip;
    }
    function CheckToken($token){
        $db =  connectDB();
        $sql = 'SELECT Utilisateurs.id FROM Utilisateurs INNER JOIN KeyTable ON KeyTable.key_id = Utilisateurs.key_id WHERE KeyTable.token = :token';
        $request = $db->prepare($sql);
        $request->bindParam(':token',  $token);
        $request->execute();
        $user = $request->fetch(PDO::FETCH_ASSOC);
        if($user){
            return true;
        }else{
            return false;
        }
    }
?>