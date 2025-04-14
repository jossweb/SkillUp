<?php
    session_start();
    require_once("../include/connectdb.php"); 
    require_once("../include/sessionManager.php");
    if(!IsConnected()){
        header('Location:  connection.php');
        exit();
    }
    if(!isset($_GET['cours'])){
        header('Location: ../');
        exit();
    }
    $titre = SITE_NAME . ' - Dashboard prof';
    $db =  connectDB();
    $sql = 'SELECT Utilisateurs.id, Utilisateurs.prenom, Utilisateurs.nom, Utilisateurs.e_mail, Utilisateurs.avatar_url, Utilisateurs.role FROM sessions INNER JOIN Utilisateurs ON Utilisateurs.id = sessions.user_id WHERE sessions.token = :token';
    $request = $db->prepare($sql);
    $request->bindParam(':token', $_COOKIE['user_token']);
    $request->execute();
    $result = $request->fetch(PDO::FETCH_ASSOC);
    /*if($result['role'] != 'professeur'){
        header('Location:  ../');
        exit();
    }*/
    if($_GET['cours'] == "00000"){ //new course
        $sql_new = 'INSERT INTO Cours (nom, illustration_url, description, prof_id) 
        VALUES ("Nouveau cours", "https://remyweb.fr/images/1361465141845032960.webp", "Entrez votre description", :prof)';
        $request_new = $db->prepare($sql_new);
        $request_new->bindParam(':prof', $result['id']);
        $request_new->execute();
        $idCours = $db->lastInsertId(); 
        // add init chapter
        
        $sql_chapter = 'INSERT INTO Chapitres (titre, cours_id) VALUES ("Chapitre 1 : demo", :cours_id)';
        $request_chapter = $db->prepare($sql_chapter);
        $request_chapter->bindParam(':cours_id', $idCours);
        $request_chapter->execute();
        $chapter_id = $db->lastInsertId();
        $dir_path = '../md_files/' . $idCours;
        $file_path = $dir_path . '/' . $chapter_id . '.md';
        
        if (!file_exists($dir_path)) {
            mkdir($dir_path, 0777, true); 
        }
        
        if (!file_exists($file_path)) {
            $init_content = "# Chapitre 1 : \n \n à vous de jouer !";
            file_put_contents($file_path, $init_content); 
        }

        $sqlUpdate = "UPDATE Chapitres SET fichier_url = :url WHERE id = :id";
        $request_update = $db->prepare($sqlUpdate);
        $request_update->bindParam(":url", $file_path);
        $request_update->bindParam(":id", $chapter_id);
        $request_update->execute();

        header('Location:  addCourse.php?cours=' . $idCours);
        exit();
    }
    //Check if the user connected is the teacher who own this course
    /*$sql_check = "SELECT Utilisateurs.id FROM Utilisateurs INNER JOIN Cours ON Utilisateurs.id = Cours.prof_id WHERE Cours.id = :coursId;";
    $request_check = $db->prepare($sql_check);
    $request_check->bindParam(":coursId", $_COOKIE[""]);*/


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/addCourses.css"> 
    <title><?php echo htmlspecialchars($titre);?></title>
<body>
    <section class="">

    </section>
</body>