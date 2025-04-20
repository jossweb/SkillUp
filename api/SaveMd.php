<?php 
    require_once("../include/connectdb.php");
    require_once("../include/secureCheck.php");

    if(LogsCheck()){
        if (isset($_POST["token"]) && isset($_POST["markdown"]) && isset($_POST["chapter"])) {
            $db =  connectDB();
            $sql = 'SELECT Utilisateurs.id FROM Utilisateurs INNER JOIN KeyTable ON KeyTable.key_id = Utilisateurs.key_id WHERE KeyTable.token = :token';
            $request = $db->prepare($sql);
            $request->bindParam(':token',  $_POST["token"]);
            $request->execute();
            $user = $request->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                AddInLog(true);
                $path = "../md_files". $user['id'] . "/" . $_POST["chapter"] . ".md";
                if(file_exists($path)){
                    file_put_contents($path,$_POST["markdown"]);
                    echo json_encode(["Success"]);
                    http_response_code(200); 
                    exit;
                }else{
                    echo json_encode(["error" => "Invalid chapter"]);
                    http_response_code(401); 
                    exit;
                }
            } 
            else {
                AddInLog(state: false);
                echo json_encode(["error" => "Invalid or expired token"]);
                http_response_code(401); 
                exit;
            }
        } else {
            echo json_encode(["error" => "Syntax error in request. Please check the documentation and try again."]);
            http_response_code(401); 
            exit;
        }
    }else{
        echo json_encode(["error" => "Invalid or expired token"]);
        http_response_code(401); 
        exit;
    }
?>