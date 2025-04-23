<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

    require_once("../include/connectdb.php");
    require_once("../include/secureCheck.php");
    header('Content-Type: application/json');
    
    if(LogsCheck()){
        if(isset($_POST["token"]) && isset($_POST['image']) && isset($_POST['cours']) && isset($_POST['chapter'])){
            if (CheckToken($_POST['token'])) {
                AddInLog(true);
                $path = "../md_files/". $_POST["cours"] . "/images/" . $_POST['chapter']; 
                if (!is_dir($path)) {
                    if (!mkdir($path, 0777, true)) {
                        echo json_encode([
                            'success' => true,
                            "error" => "Can't create images dir in your cours"]);
                        http_response_code(401); 
                        exit;
                    }
                }
                $db =  connectDB();
                $sql = 'INSERT INTO Images (Images.id_chapitre) VALUES (:idc);';
                $request = $db->prepare($sql);
                $request->bindParam(':idc', $_POST['chapter']);
                $request->execute();
                $imgId = $db->lastInsertId(); 
                $imageMarkdown = trim($_POST['image']);

                if (preg_match('/^!\[.*\]\(data:image\/([a-zA-Z]*);base64,([A-Za-z0-9+\/=_-]+)\)$/', $imageMarkdown, $regexResult)) {
                    $base64Data = $regexResult[2];  
                    $imageData = base64_decode($base64Data);
                    $filePath = $path . "/" . $imgId . ".png";
                    if (file_put_contents($filePath, $imageData)) {
                        http_response_code(200);
                        echo json_encode([
                            'success' => true,
                            'file' => $filePath
                        ]);
                        exit;
                    } else {
                        http_response_code(401);
                        echo json_encode([
                            'success' => false,

                            'error' => "Error can't save image, check cours and chapter values !"
                        ]);
                        exit;
                    }
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'error' => "Format base64 markdown invalide"
                    ]);

                    exit;
                }
    
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => "Invalid token"
                ]);
                http_response_code(401); 
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