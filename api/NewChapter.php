<?php
require_once("../include/connectdb.php");
require_once("../include/secureCheck.php");
header('Content-Type: application/json');

if(LogsCheck()){
    if (isset($_POST["token"]) && isset($_POST["name"]) && isset($_POST["cours_id"])) {
        if (CheckToken($_POST['token'])) {
            $db = connectDB();
            $sql_chapter = 'INSERT INTO Chapitres (titre, cours_id) VALUES (:name, :cours_id)';
            $request_chapter = $db->prepare($sql_chapter);
            $request_chapter->bindParam(':name', $_POST["name"]);
            $request_chapter->bindParam(':cours_id', $idCours);
            $request_chapter->execute();
            $chapter_id = $db->lastInsertId();
            $dir_path = '../md_files/' . $_POST["cours_id"];
            $file_path = $dir_path . '/' . $chapter_id . '.md';
            
            if (!file_exists($dir_path)) {
                mkdir($dir_path, 0777, true); 
            }
            
            if (!file_exists($file_path)) {
                if($_POST['md']){
                    $init_content = $_POST['md'];
                }else{
                    $init_content = "# Chapitre 1 : \n \n à vous de jouer !";
                }
                file_put_contents($file_path, $init_content); 
            }
    
            $sqlUpdate = "UPDATE Chapitres SET fichier_url = :url WHERE id = :id";
            $request_update = $db->prepare($sqlUpdate);
            $request_update->bindParam(":url", $file_path);
            $request_update->bindParam(":id", $chapter_id);
            $request_update->execute();

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'chapter_id' => $chapter_id
            ]);
            exit;
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => "Invalid token"
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => "Missing parameters"
        ]);
        http_response_code(401); 
        exit;
    }
}
?>