<?php
/*ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);*/

require_once("../include/connectdb.php");
require_once("../include/secureCheck.php");
require_once("../include/tools.php");
header('Content-Type: application/json');
$db =  connectDB();
if(false){ //you can't use this endpoint in prod
    if(LogsCheck()){
        if (isset($_POST["firstname"]) && isset($_POST["email"]) && isset($_POST["pass"])) {
            $db =  connectDB();
            $currentDateTime = date('Y-m-d H:i:s');
            $hashedPassword = password_hash($_POST["pass"], PASSWORD_DEFAULT);
            $sql = "INSERT INTO Utilisateurs (Utilisateurs.nom, Utilisateurs.prenom, Utilisateurs.e_mail, Utilisateurs.mot_de_passe, Utilisateurs.date_creation, Utilisateurs.role) VALUES ('bot', :prenom, :email, :mot_de_passe, :date_creation, 'etudiant')";
            $request = $db->prepare($sql);
            $request->bindParam(':prenom', $_POST["firstname"]);
            $request->bindParam(':email', $_POST["email"]);
            $request->bindParam(':mot_de_passe', $hashedPassword);
            $request->bindParam(':date_creation', $currentDateTime);
            $request->execute();
     
            echo json_encode([
                "success" => true
            ]);
        }
    
        /*
        if (isset($_POST["firstname"]) && isset($_POST["email"]) && isset($_POST["pass"])) {
            $db =  connectDB();
            $currentDateTime = date('Y-m-d H:i:s');
            $hashedPassword = password_hash($_POST["pass"], PASSWORD_DEFAULT);
            $sql = "INSERT INTO Utilisateurs (Utilisateurs.nom, Utilisateurs.prenom, Utilisateurs.e_mail, Utilisateurs.mot_de_passe, Utilisateurs.date_creation, Utilisateurs.role) VALUES ('bot', :prenom, :email, :mot_de_passe, :date_creation, 'professeur')";
            $request = $db->prepare($sql);
            $request->bindParam(':prenom', $_POST["firstname"]);
            $request->bindParam(':email', $_POST["email"]);
            $request->bindParam(':mot_de_passe', $hashedPassword);
            $request->bindParam(':date_creation', $currentDateTime);
            $request->execute();
            $id = $db->lastInsertId();
    
            $token = generateToken();
            while(CheckToken($token)){
                $token = generateToken();
            }
            $currentDate = date('Y-m-d H:i:s');
            $sql = "INSERT INTO KeyTable (actif, token, date_creation) VALUES (true, :token, :date)";
            $request = $db->prepare($sql);
            $request->bindParam(':token', $token);
            $request->bindParam(':date', $currentDate);
            $request->execute();
    
            $keyId = $db->lastInsertId();
    
            $sql = 'UPDATE Utilisateurs SET Utilisateurs.key_id = :kid WHERE Utilisateurs.id = :id';
            $request = $db->prepare($sql);
            $request->bindParam(':kid', $keyId);
            $request->bindParam(':id', $id);
            $success = $request->execute();
    
            if($success){
                echo json_encode([
                    "success" => true,
                    "token" => $token
                ]);
                http_response_code(200);
                exit;
            }else{
                echo json_encode([
                    "success" => false,
                    "message" => "Error, can't generate this user, try again !"
                ]);
                http_response_code(401);
                exit;
            }
    
        }
        else{
            echo json_encode([
                "success" => false,
                "message" => "2"
            ]);
            http_response_code(401);
            exit;
        }
    
    }
    else{
        echo json_encode([
            "success" => false,
            "message" => "1"
        ]);
        http_response_code(401);
        exit;*/
    }
}

?>