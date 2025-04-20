<?php
    function LogsCheck() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $db =  connectDB();
            $dateH_check = (new DateTime('-30 minutes'))->format('Y-m-d H:i:s');
            $sql = 'SELECT COUNT(ApiLogs.id) AS nb_denied FROM ApiLogs WHERE ApiLogs.date_heure > :dateheure AND ApiLogs.succes = FALSE AND ip = :ip;';
            $request = $db->prepare($sql);
            $request->bindParam(':dateheure', $dateH_check );
            $request->bindParam(':ip', $_SERVER['HTTP_CLIENT_IP']);
            $request->execute();
            $result = $request->fetch(PDO::FETCH_ASSOC);
            if ($result['nb_denied'] > 10) {
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
        $sql = 'INSERT INTO ApiLogs (ApiLogs.ip, ApiLogs.succes) VALUES (:ip, :success);';
        $request = $db->prepare($sql);
        $request->bindParam(':ip', $dateH_check );
        $request->bindParam(':success', $_SERVER['HTTP_CLIENT_IP']);
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
?>