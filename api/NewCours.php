<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
require_once("../include/connectdb.php");
require_once("../include/secureCheck.php");
require_once("../include/tools.php");
header('Content-Type: application/json');
if(LogsCheck()){
    if (isset($_POST["token"]) && isset($_POST["name"]) && isset($_POST['prompt']) && isset($_POST['categorie'])) {
        if (CheckToken($_POST['token'])) {
            $id = GetProfId($_POST['token']);

            if(!$id){
                echo json_encode([
                    "success" => false,
                    "message" => "Can't get user id, genere new token and try again"
                ]);
                http_response_code(401);
                exit;
            }
            $urlimg = getAvatar();

            switch ($_POST['categorie']) {
                case "Développement Web":
                    $categorieId = 1;
                    break;
                case "Cybersécurité":
                    $categorieId = 2;
                    break;
                case "Intelligence Artificielle":
                    $categorieId = 3;
                    break;
                case "Réseaux Informatiques":
                    $categorieId = 4;
                    break;
                case "Systèmes d’Exploitation":
                    $categorieId = 5;
                    break;
                case "Base de Données":
                    $categorieId = 6;
                    break;
                case "Développement Mobile":
                    $categorieId = 7;
                    break;
                case "Programmation Orientée Objet":
                    $categorieId = 8;
                    break;
                case "Architecture Logicielle":
                    $categorieId = 9;
                    break;
                case "Cloud Computing":
                    $categorieId = 10;
                    break;
                case "DevOps":
                    $categorieId = 11;
                    break;
                case "Algorithmique":
                    $categorieId = 12;
                    break;
                case "Informatique Théorique":
                    $categorieId = 13;
                    break;
                case "UX/UI Design":
                    $categorieId = 14;
                    break;
                case "Robotique":
                    $categorieId = 15;
                    break;
                default:
                    echo json_encode([
                        "success" => false,
                        "message" => "Error, unknown categorie, try again"
                    ]);
                    http_response_code(401);
                    exit;
            }
            if($_POST['description']){
                $description = $_POST['description'];
            }else{
                $description = null;
            }
            if(!$urlimg){
                $urlimg = "https://remyweb.fr/images/1361465141845032960.webp";
            }

            $db =  connectDB();
            $sql = "INSERT INTO Cours (Cours.nom, Cours.illustration_url, Cours.description, Cours.categorie_id, Cours.prof_id) VALUES (:nom, :illustration, :description, :cat, :pid);";
            $request = $db->prepare($sql);
            $request->bindParam(':nom', $_POST["name"]);
            $request->bindParam(':illustration', $urlimg);
            $request->bindParam(':description', $_POST["description"]); //can be null 
            $request->bindParam(':cat', $categorieId);
            $request->bindParam(':pid', $id);
            $success = $request->execute();
            $id = $db->lastInsertId();

            if($success){
                echo json_encode([
                    "success" => true,
                    "cours_id" => "$id"
                ]);
                http_response_code(200);
                exit;
            }else{
                echo json_encode([
                    "success" => false,
                    "cours_id" => "$id"
                ]);
                http_response_code(401);
                exit;
            }
        }else{
            echo json_encode([
            "success" => false,
            "message" => "ERROR : Invalid token"
        ]);
        http_response_code(401);
        exit;
    }
    }else if(!isset($_POST["name"])) { 
        echo json_encode([
        "success" => false,
        "message" => "ERROR : name is missing"
    ]);

    }
    else if(!isset($_POST["prompt"])) { 
        echo json_encode([
        "success" => false,
        "message" => "ERROR : prompt is missing"
    ]);
    }
    else if(!isset($_POST["categorie"])) { 
        echo json_encode([
        "success" => false,
        "message" => "ERROR : cateegorie is missing"
    ]);}
    else if(!isset($_POST["token"])) { 
        echo json_encode([
        "success" => false,
        "message" => "ERROR : token is missing"
    ]);}

    else { echo json_encode([
        "success" => false,
        "message" => "ERROR : one or more parameters are missing"
    ]);
    http_response_code(401);
    exit;
    }

}
?>