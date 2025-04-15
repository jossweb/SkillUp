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
    if($result['role'] != 'professeur'){
        header('Location:  ../');
        exit();
    }
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
    //Check if the user connected is the teacher who own this course and get infos on course
    $sql_check = "SELECT Cours.nom, Cours.illustration_url, Cours.description, Cours.categorie_id FROM Cours WHERE Cours.prof_id = :id AND Cours.id = :cid";
    $request_check = $db->prepare($sql_check);
    $request_check->bindParam(":id", $result['id']);
    $request_check->bindParam(":cid", $_GET['cours']);
    $request_check->execute();
    $result_check = $request_check->fetch(PDO::FETCH_ASSOC);
    if (!$result_check) {
       header("Location: dashboard.php");
       exit();
    }

    $sql_chapter = "SELECT Chapitres.titre, Chapitres.fichier_url FROM Chapitres WHERE Chapitres.cours_id = :cid;";
    $request_chapter = $db->prepare($sql_chapter);
    $request_chapter->bindParam(":cid", $_GET['cours']);
    $request_chapter->execute();

    $results_chapters = $request_chapter->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/addCourses.css"> 
    <title><?php echo htmlspecialchars($titre);?></title>
<body>
    <section class="left-bar">
        <div class="cours-primary-infos">
            <img src="<?php echo $result_check['illustration_url']?>"/>
            <div>
                <form method="POST">
                    <input class="f-name" type="text" maxlength="255" value="<?php echo $result_check['nom']?>">
                    <button type="submit" name="name">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <div class="description">
            <form>
                <div class="description-head">
                    <h2 for="description">Description du cours</h2>
                    <button type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
                    </button>
                </div>
                <textarea type="textfield" name="description"><?php echo $result_check['description']?></textarea>
            </form>
        </div>
        <div class="chapter">
            <div class="description-head">
                <h2 for="description">Chapitres</h2>
                <button type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-plus-icon lucide-circle-plus"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>                </button>
            </div>
            <div class="chapter-container">
                <?php
                    foreach ($results_chapters as $chapter) {
                        echo "<button>".$chapter['titre']. "</button>";
                    }   
                ?>
            </div>
        </div>
    </section>
    <section class="main-part">

    </section>
</body>