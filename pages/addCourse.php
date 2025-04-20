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
    $sql = 'SELECT Utilisateurs.id, Utilisateurs.prenom, Utilisateurs.nom, Utilisateurs.e_mail, Utilisateurs.avatar_url, Utilisateurs.role, KeyTable.token FROM sessions INNER JOIN Utilisateurs ON Utilisateurs.id = sessions.user_id INNER JOIN KeyTable ON KeyTable.key_id = Utilisateurs.key_id WHERE sessions.token = :token';
    $request = $db->prepare($sql);
    $request->bindParam(':token', $_COOKIE['user_token']);
    $request->execute();
    $result = $request->fetch(PDO::FETCH_ASSOC);
    if($result['role'] != 'professeur'){
        header('Location:  ../');
        exit();
    }
    setcookie("token_api", $result['token'], time() + 3600*24);
    $_COOKIE['token_api'] = $result['token'];
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
    function ChangeName($name) {
        global $db;
        $sql = "UPDATE Cours SET Cours.nom = :name WHERE Cours.id = :id";
        $request = $db->prepare($sql);
        $request->bindParam(":name", $name);
        $request->bindParam(":id", $_GET['cours']);
        $request->execute() ;
    }
    function ChangeDescription($description) {
        global $db;
        $sql = "UPDATE Cours SET Cours.description = :des WHERE Cours.id = :id";
        $request = $db->prepare($sql);
        $request->bindParam(":des", $description);
        $request->bindParam(":id", $_GET['cours']);
        $request->execute() ;
    }
    function AddChapter() {
        global $db;
        $sql_chapter = 'INSERT INTO Chapitres (titre, cours_id) VALUES ("Nouveau Chapitre", :cours_id)';
        $request_chapter = $db->prepare($sql_chapter);
        $request_chapter->bindParam(':cours_id', $_GET['cours']);
        $request_chapter->execute();
        $chapter_id = $db->lastInsertId();
        $dir_path = '../md_files/' . $_GET['cours'];
        $file_path = $dir_path . '/' . $chapter_id . '.md';
        
        if (!file_exists($dir_path)) {
            mkdir($dir_path, 0777, true); 
        }
        
        if (!file_exists($file_path)) {
            $init_content = "# Nouveau chapitre : \n \n à vous de jouer !";
            file_put_contents($file_path, $init_content); 
        }

        $sqlUpdate = "UPDATE Chapitres SET fichier_url = :url WHERE id = :id";
        $request_update = $db->prepare($sqlUpdate);
        $request_update->bindParam(":url", $file_path);
        $request_update->bindParam(":id", $chapter_id);
        $request_update->execute();
    }
    function UpdateMd($md) {
        $file_path = "../md_files/" . $_GET['cours'] . "/" . $_COOKIE['activeChapId'] . ".md";
        if (file_exists($file_path)) {
            file_put_contents($file_path, $md);
        } else {
            header("Location: dashboard.php");
            exit;
        }
    }

    function ChangeChapName($chapName) {
        global $db;
        $sql = "UPDATE Chapitres SET Chapitres.titre = :newt WHERE Chapitres.id = :id";
        $request = $db->prepare($sql);
        $request->bindParam(":newt", $chapName);
        $request->bindParam(":id", $_COOKIE['activeChapId']);
        $request->execute() ;
    }
    function DeleteChap(){
        global $db;
        $sql = "DELETE FROM Chapitres WHERE id = :id ";
        $request = $db->prepare($sql);
        $request->bindParam(":id", $_COOKIE['activeChapId']);
        $request->execute();
        $path = "../md_files/". $_GET['cours'] . '/'. $_COOKIE['activeChapId'] . '.md';
        if (file_exists($path)) {
            if (!unlink($path)) {
                header('Location: dashboard.php');
            }
        }
        //remove cookie
        setcookie("activeChapId", "", time() - 3600, "./");
        header('Location: addCourse.php?cours=' . $_GET['cours']);
        exit();
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["change-name"])) {
            ChangeName($_POST["name"]);
        }
        if (isset($_POST["description-b"])) {
            ChangeDescription($_POST["description"]);
        }
        if (isset($_POST["add-chapter"])) {
            AddChapter();
        }
        if (isset($_POST["save-md"])) {
            UpdateMd($_POST["md-editor"]);
        }
        if (isset($_POST["chap-name"])) {
            ChangeChapName($_POST["new-name"]);
        }
        if (isset($_POST["delete-chap"])){
            DeleteChap() ;
        }
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
    function FindLineById(array $chapitres, int $idRecherche): ?array {
        foreach ($chapitres as $chapitre) {
            if ($chapitre['id'] == $idRecherche) {
                return $chapitre;
            }
        }
        return null; 
    }

    $sql_chapter = "SELECT Chapitres.id, Chapitres.titre, Chapitres.fichier_url FROM Chapitres WHERE Chapitres.cours_id = :cid;";
    $request_chapter = $db->prepare($sql_chapter);
    $request_chapter->bindParam(":cid", $_GET['cours']);
    $request_chapter->execute();

    $results_chapters = $request_chapter->fetchAll(PDO::FETCH_ASSOC);

    //chapters infos in cookies
    if((!isset($_COOKIE['activeChapId']))) {
        //set 1h cookies
        setcookie("activeChapId", $results_chapters[0]['id'], time() + 3600);
        $_COOKIE['activeChapId'] = $results_chapters[0]['id'];
        setcookie('activeChapTitle', $results_chapters[0]['titre'], time() + 3600);
        $_COOKIE['activeChapTitle'] = $results_chapters[0]['titre'];
        if (file_exists($results_chapters[0]["fichier_url"])) {
            $markdownContent = file_get_contents($results_chapters[0]["fichier_url"]);
            setcookie('activeChapMd', $markdownContent, time() + 3600);
        } else {
            header('Location: dashboard.php');
            exit();
        }
    }else{
        $chapter = FindLineById($results_chapters, $_COOKIE['activeChapId']);
        if($chapter != NULL){
            setcookie('activeChapTitle', $chapter['titre'], time() + 3600);
            $_COOKIE['activeChapTitle'] = $chapter['titre'];
            
            if (file_exists($chapter["fichier_url"])) {
                $markdownContent = file_get_contents($chapter["fichier_url"]);
                setcookie('activeChapMd', $markdownContent, time() + 3600);
                $_COOKIE['activeChapMd'] = $markdownContent;
            } else {
                header('Location: dashboard.php');
                exit();
            }
        }else{
            header('Location: dashboard.php');
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/addCourses.css"> 
    <title><?php echo htmlspecialchars($titre);?></title>
</head>
<body>
    <section class="left-bar">
        <div class="cours-primary-infos">
            <img src="<?php echo $result_check['illustration_url']?>"/>
            <div>
                <form method="POST">
                    <input class="f-name" name="name" type="text" maxlength="255" value="<?php echo $result_check['nom']?>" required>
                    <button type="submit" name="change-name">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil">
                            <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                            <path d="m15 5 4 4"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        <div class="description">
            <form method="POST">
                <div class="description-head">
                    <h2 for="description">Description du cours</h2>
                    <button type="submit" name="description-b">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
                    </button>
                </div>
                <textarea type="textfield" name="description"><?php echo $result_check['description']?></textarea>
            </form>
        </div>
        <div class="chapter">
            <div class="description-head">
                <h2 for="description">Chapitres</h2>
                <form method="POST">
                    <button type="submit" name="add-chapter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-plus-icon lucide-circle-plus"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>                
                    </button>
                </form>
            </div>
            <div class="chapter-container">
                <?php
                    $nbChap = 0;
                    foreach ($results_chapters as $chapter) {
                        echo "<button onclick='ChangeActiveChapter(" . $chapter["id"] . ")'>" . $chapter['titre'] . "</button>";
                        $nbChap ++ ;
                    }   
                ?>
            </div>
        </div>
    </section>
    <section class="main-part">
        <div class="head">
            <form method="POST">
                <input type="text" name="new-name" value="<?php echo $_COOKIE['activeChapTitle'] ?>">
                <button type="submit" name="chap-name" class="save-name-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil">
                        <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                        <path d="m15 5 4 4"/>
                    </svg>   
                 </button>
            </form>
            <?php
                if ($nbChap > 1) {
                    echo '<form class="deleteForm" method="POST">
                        <button name="delete-chap" class="delete">Supprimer</button>
                    </form>';
                }
            ?>

        </div>
        <div class="toggle-bar">
            <div class="toggle">
                <button id="to-edit" onclick="ShowEdit()">Modifer</button>
                <button id="to-preview" onclick="ShowPreview()">Aperçu</button>
            </div>
        </div>
        <div class="edit" id="edit">
            <div class="tool-bar">
                <div class="button-container">
                    <button onclick="AddToMd('**Gras**')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bold-icon lucide-bold"><path d="M6 12h9a4 4 0 0 1 0 8H7a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h7a4 4 0 0 1 0 8"/></svg>
                    </button>
                    <button onclick="AddToMd('__Italic__')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-italic-icon lucide-italic"><line x1="19" x2="10" y1="4" y2="4"/><line x1="14" x2="5" y1="20" y2="20"/><line x1="15" x2="9" y1="4" y2="20"/></svg>
                    </button>
                    <button onclick="AddToMd('# ')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heading1-icon lucide-heading-1"><path d="M4 12h8"/><path d="M4 18V6"/><path d="M12 18V6"/><path d="m17 12 3-2v8"/></svg>
                    </button>
                    <button onclick="AddToMd('## ')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heading2-icon lucide-heading-2"><path d="M4 12h8"/><path d="M4 18V6"/><path d="M12 18V6"/><path d="M21 18h-4c0-4 4-3 4-6 0-1.5-2-2.5-4-1"/></svg>
                    </button>
                </div>
            </div>
            <div class="text-editor">
                <form method="POST">
                    <button name="save-md" onclick="save()">Enregistrer</button>
                    <textarea id="md-editor" name="md-editor"></textarea>
                    <div id="md" value=""></div>
                </form>
            </div>
        </div>
        <div id="preview" class="preview">
            <div id="md-output">

            </div>
        </div>

    </section>
</body>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="../<?php echo JS_PATH; ?>/texteditor.js"></script>
</html>