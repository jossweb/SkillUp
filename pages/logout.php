<?php
    session_start();
    require_once("../include/connectdb.php"); 
    require_once("../include/sessionManager.php");
    if(!IsConnected()){
        header('Location:  connection.php');
        exit();
    }else{
        $db =  connectDB();
        $sql = 'DELETE FROM sessions WHERE sessions.token = :token';
        $request = $db->prepare($sql);
        $request->bindParam(':token', $_COOKIE['user_token']);
        $request->execute();
        setcookie('token_api', '', time() - 3600, '/');
        setcookie('user_token', '', time() - 3600, '/');
        session_destroy();
        header("Location: connection.php");
        exit;
    }

?>