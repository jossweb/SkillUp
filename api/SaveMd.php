<?php 
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
require_once("../include/connectdb.php");
require_once("../include/secureCheck.php");
header('Content-Type: application/json');

if(LogsCheck()){
    if (isset($_POST["token"]) && isset($_POST["markdown"]) && isset($_POST["chapter"]) && isset($_POST["cours"])) {
        if (CheckToken($_POST['token'])) {
            AddInLog(true);
            $path = "../md_files/". $_POST["cours"] . "/" . $_POST["chapter"] . ".md";
            if(file_exists($path)){
                file_put_contents($path,$_POST["markdown"]);
                echo json_encode(["success" => true]);
                http_response_code(200); 
                exit;
            }else{
                echo json_encode(["error" => "Can't found this chapter"]);
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