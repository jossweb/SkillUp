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
    function ChangeName($name, $cat) {
        global $db;
        $sql = "UPDATE Cours SET Cours.nom = :name, Cours.categorie_id = :categorie WHERE Cours.id = :id";;
        $request = $db->prepare($sql);
        $request->bindParam(":name", $name);
        $request->bindParam(":categorie", $cat);
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
    function getAvatar(){
        global $new_avatar;
        if (!isset($_POST['prompt']) || empty($_POST['prompt'])) {
            echo "<h1>Erreur : Aucun prompt fourni.</h1>";
            return;
        }
    
        $prompt = $_POST['prompt'];
    
        $url = "https://remyweb.fr/emoji.php";
        $data = ['prompt' => $prompt];
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']); 
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            echo "<h1>Erreur cURL : " . curl_error($curl) . "</h1>";
            curl_close($curl);
            return;
        }
        curl_close($curl);
        $decodedResponse = json_decode($response, true);
    
        if (isset($decodedResponse['imagePath'])) {
            $imagePath = htmlspecialchars($decodedResponse['imagePath']);
            $new_avatar = 'https://remyweb.fr/' . $imagePath;
            $_SESSION['new_avatar'] = $new_avatar;
        } else {
            echo "<h1>Erreur : Réponse invalide</h1>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    }
    function SetNewAvatar() {
        if(isset($_SESSION['new_avatar']) && $_SESSION['new_avatar'] !== null){
            global $new_avatar, $result;
            $db = connectDB();
            $sql = "UPDATE Cours SET illustration_url = :avatarUrl WHERE id = :id";
            $request = $db->prepare($sql);
            $request->bindParam(':avatarUrl', $_SESSION['new_avatar'] );
            $request->bindParam(':id', $_GET['cours']);
            $request->execute();
            $_SESSION['new_avatar'] = null;
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["change-name"])) {
            ChangeName($_POST["name"], $_POST["categorie"]);
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
        if (isset($_POST['avatar-gen'])) {
            getAvatar();
        }
        if (isset($_POST['agreeAvatar'])) {
            SetNewAvatar();
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
    function FindLineById($chapitres, $idRecherche){
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
    //get chapters infos
    $sql_cat = "SELECT Categories.nom, Categories.id FROM Categories";
    $request = $db->prepare($sql_cat);
    $request->execute();
    $categories = $request->fetchAll(PDO::FETCH_ASSOC);
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
    <nav>
        <div class="logo" aria-label="SkillUp"></div>
        <button id="back" onclick="location.href='dashboard.php'">Dashboard</button>
        <button name="save-md" onclick="save()">Enregistrer</button>
    </nav>
    <div class="page-content">
        <section class="left-bar" id="left-bar">
            <button class="cross" onclick="ResponsiveSys(false)">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>            </button>
            <div class="cours-primary-infos">
                <button id="generatePopup" onclick="OpenAvatarPopup()">
                    <img src="<?php echo $result_check['illustration_url'] ?>" alt="Your profile picture" class="avatar"/>
                </button>
                <div>
                    <form method="POST">
                        <input class="f-name" name="name" type="text" maxlength="255" value="<?php echo $result_check['nom']?>" required>
                        <select name="categorie" id="categorie">
                        <?php
                            foreach ($categories as $category) {
                                echo '<option value="' . htmlspecialchars($category['id']) . '">'
                                    . htmlspecialchars($category['nom']) .
                                '</option>';
                            }
                        ?>
                        </select>
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
                    <form method="POST" id="add-chapter">
                        <button type="submit" name="add-chapter">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-plus-icon lucide-circle-plus"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>                
                        </button>
                    </form>
                </div>
                <div class="chapter-container">
                    <?php
                        $nbChap = 0;
                        foreach ($results_chapters as $chapter) {
                            echo "<button onclick='ChangeActiveChapter(" . htmlspecialchars($chapter["id"]) . ")'>" . htmlspecialchars($chapter['titre']) . "</button>";
                            $nbChap ++ ;
                        }   
                    ?>
                </div>
            </div>
        </section>
        <section class="main-part" id="main-part">
            <button class="burger" onclick="ResponsiveSys(true)">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-menu-icon lucide-menu"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
            </button>
            <div class="head">
                <form method="POST">
                    <input type="text" name="new-name" id="new-name" value="<?php echo htmlspecialchars($_COOKIE['activeChapTitle']) ?>">
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
            <div class="chapter-content">
                <div class="toggle-bar">
                    <div class="toggle">
                        <button id="to-edit" class="selected" onclick="ShowEdit()">Modifer</button>
                        <button id="to-preview" onclick="ShowPreview()">Aperçu</button>
                    </div>
                </div>
                <div class="edit" id="edit">
                    <div class="tool-bar">
                        <div class="button-container">
                            <button onclick="AddToMd('*Gras*')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bold-icon lucide-bold"><path d="M6 12h9a4 4 0 0 1 0 8H7a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h7a4 4 0 0 1 0 8"/></svg>
                            </button>
                            <button onclick="AddToMd('_Italic_')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-italic-icon lucide-italic"><line x1="19" x2="10" y1="4" y2="4"/><line x1="14" x2="5" y1="20" y2="20"/><line x1="15" x2="9" y1="4" y2="20"/></svg>
                            </button>
                            <button onclick="AddToMd('# ')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heading1-icon lucide-heading-1"><path d="M4 12h8"/><path d="M4 18V6"/><path d="M12 18V6"/><path d="m17 12 3-2v8"/></svg>
                            </button>
                            <button onclick="AddToMd('## ')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heading2-icon lucide-heading-2"><path d="M4 12h8"/><path d="M4 18V6"/><path d="M12 18V6"/><path d="M21 18h-4c0-4 4-3 4-6 0-1.5-2-2.5-4-1"/></svg>
                            </button>
                            <button onclick="AddToMd('### ')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heading3-icon lucide-heading-3"><path d="M4 12h8"/><path d="M4 18V6"/><path d="M12 18V6"/><path d="M17.5 10.5c1.7-1 3.5 0 3.5 1.5a2 2 0 0 1-2 2"/><path d="M17 17.5c2 1.5 4 .3 4-1.5a2 2 0 0 0-2-2"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="text-editor">
                        <textarea id="md-editor" name="md-editor"></textarea>
                        <div id="md" value=""></div>
                    </div>
                </div>
                <div id="preview" class="preview">
                    <div id="md-output">

                    </div>
                </div>
            </div>
        </section>
        <div class="blurred-bg" id="blurred-bg"></div>
        <div class="popup" id="new-pp">
            <button onclick="CloseAvatarPopup()" class="cross">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>            </button>
             </button>
            <h2>Changer mon avatar</h2>
            <div class="avatar-container">
                <img src="<?php echo  htmlspecialchars($result_check['illustration_url'])?>">
                <?php if(isset($_SESSION['new_avatar']) && $_SESSION['new_avatar'] !== null){echo "<p>&#x2794;</p><img src=". $_SESSION['new_avatar']  ." alt='nouveau avatar' />";}?>
            </div>
                <?php if(isset($_SESSION['new_avatar']) && $_SESSION['new_avatar'] !== null){echo "<form method='POST'><button name='agreeAvatar' id='agreeAvatar'>Accepter l'avatar</button></form>";}?>
            
            <form method="POST">
                <label for="prompt">Mon avatar doit ressembler à :</label>
                <input id="prompt" name="prompt" type="text">
                <button id="generate" name="avatar-gen" onclick="ShowLoading()">Générer mon avatar</button>
                <div class="loading-part" id="loading-emo">
                    <p>Chargement</p>
                    <div id="spinner"></div>
                </div>
            </form>
        </div>
    </div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.3/purify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="../<?php echo JS_PATH; ?>/texteditor.js"></script>
</html>