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
            preg_match_all('/!\[[^\]]*\]\((?:.*\/)?([a-zA-Z0-9_-]+)\.png\)/', $_POST["markdown"], $regexResult);
            if(!empty($regexResult[1])){
                $i = 0;
                $imgKeep = [];
                $db = connectDB();
                $idsToKeep = [];
                foreach($regexResult[1] as $id){
                    $imgKeep[$i] = $id . ".png";
                    $idsToKeep[] = $id;
                    $i++;
                }
                $sql = "DELETE FROM Images WHERE id_chapitre = :cid";
                if (!empty($idsToKeep)) {
                    $sql .= " AND id NOT IN (" . implode(',', array_map(function($id) { return "'" . $id . "'"; }, $idsToKeep)) . ")";
                }    
                $request = $db->prepare($sql);
                $request->bindParam(':cid', $_POST['chapter']);
                $request->execute();
                $path = "../md_files/". $_POST["cours"] . "/images/" . $_POST['chapter'];
                
                $deletedCount = 0;
                if (is_dir($path)) {
                    foreach (glob($path . "/*.png") as $file) {
                        $name = basename($file);
                        
                        if (!in_array($name, $imgKeep)) {
                            unlink($file);
                            $deletedCount++;
                        }
                    }
                }
                
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => $i . " Images Kept, " . $deletedCount . " Images Deleted!"
                ]);
                exit;
            }
            else{
                $i = 0;
                $db = connectDB();
                
                $path = "../md_files/". $_POST["cours"] . "/images/" . $_POST['chapter'];
                $sql = "DELETE FROM Images WHERE id_chapitre = :cid";
                $request = $db->prepare($sql);
                $request->bindParam(':cid', $_POST['chapter']);
                $request->execute();
                if (is_dir($path)) {
                    foreach (glob($path . "/*.png") as $file) {
                        unlink($file);
                        $i++;
                    }
                }
                
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => $i > 0 ? $i . " Images deleted!" : "All images deleted!"
                ]); 
                exit;
            }
        }else {
            echo json_encode(["error" => "Invalid or expired token"]);
            http_response_code(401); 
            exit;
        }
    }else{
        echo json_encode(["error" => "Syntax error in request. Please check the documentation and try again."]);
        http_response_code(401); 
        exit;
    }
}
?>