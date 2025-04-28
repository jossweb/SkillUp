<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
require_once("../include/connectdb.php");
require_once("../include/secureCheck.php");
header('Content-Type: application/json');

    if(isset($_POST['token']) && isset($_POST["cours"]) && isset($_POST["chapter"])){
        if (CheckToken($_POST['token'])) {
            AddInLog(true);
            $path = "../md_files/". $_POST["cours"] . "/" . $_POST["chapter"] . ".md";
            if(file_exists($path)){
                $content = file_get_contents($path);
                echo json_encode([
                    "success" => true,
                    "content" => $content
                ]);
                http_response_code(200);
                exit;
            }else{
                echo json_encode(["error" => "Can't found this chapter"]);
                http_response_code(401); 
                exit;
            }
        } else {
            echo json_encode(["error" => ""]);
            http_response_code(401); 
            exit;
        }
    }else{
        echo json_encode(["error" => "Syntax error in request. Please check the documentation and try again."]);
        http_response_code(401); 
        exit;
    }
?>