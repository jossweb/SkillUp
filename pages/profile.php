<?php
session_start();
require_once("../include/connectdb.php"); 
require_once("../include/sessionManager.php");
require_once("../include/tools.php");
if(!IsConnected()){
    header('Location:  connection.php');
    exit();
}
$titre = SITE_NAME . ' - profil';
$db =  connectDB();
$sql = 'SELECT Utilisateurs.id, Utilisateurs.prenom, Utilisateurs.nom, Utilisateurs.e_mail, Utilisateurs.avatar_url, Utilisateurs.role FROM sessions INNER JOIN Utilisateurs ON Utilisateurs.id = sessions.user_id WHERE sessions.token = :token';
$request = $db->prepare($sql);
$request->bindParam(':token', $_COOKIE['user_token']);
$request->execute();
$result = $request->fetch(PDO::FETCH_ASSOC);
$avatar = $result['avatar_url'];
if($avatar == null){
    $avatar = 'https://remyweb.fr/images/1356835268082008064.webp';
}
$sql_prof_requests = "SELECT DemandeProf.id FROM DemandeProf WHERE DemandeProf.id_utilisateur = :id";
$request = $db->prepare( $sql_prof_requests);
$request->bindParam(':id', $result['id']);
$request->execute();
$profRequest = $request->fetch(PDO::FETCH_ASSOC);

function SetNewAvatar() {
    if(isset($_SESSION['new_avatar']) && $_SESSION['new_avatar'] !== null){
        global $new_avatar, $result;
        $db = connectDB();
        $sql = "UPDATE Utilisateurs SET avatar_url = :avatarUrl WHERE id = :id";
        $request = $db->prepare($sql);
        $request->bindParam(':avatarUrl', $_SESSION['new_avatar'] );
        $request->bindParam(':id', $result['id']);
        $request->execute();
        $_SESSION['new_avatar'] = null;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
function ChangeUserInfos() {
    global $db, $result;
    $name = $_POST['new-name'];
    $firstname = $_POST['new-firstname'];
    $sql="UPDATE Utilisateurs SET Utilisateurs.nom = :name, Utilisateurs.prenom = :firstname WHERE Utilisateurs.id = :id";
    $request = $db->prepare($sql);
    $request->bindParam(':name', $name );
    $request->bindParam(':firstname', $firstname );
    $request->bindParam(':id', $result['id']);
    $request->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
function DeleteUser(){
    global $db, $result;
    $sql="DELETE FROM Utilisateurs WHERE Utilisateurs.id = :id";
    $request = $db->prepare($sql);
    $request->bindParam(':id', $result['id'] );
    $request->execute();

    $sql="DELETE FROM sessions WHERE sessions.token = :token";
    $request = $db->prepare($sql);
    $request->bindParam(':token', $_COOKIE['user_token'] );
    $request->execute();

    setcookie('user_token', '', time(), '/');
    session_destroy();
    header("Location: /index.php");
    exit();
}
function CheckInTeacherTable(){
    global $db, $result;
    $sql= "SELECT DemandeProf.id FROM DemandeProf WHERE DemandeProf.id_utilisateur = :id";
    $request = $db->prepare($sql);
    $request->bindParam(':id', $result['id'] );
    $request->execute();
    $response = $request->fetch(PDO::FETCH_ASSOC);
    if ($response) {
        return true;
    } else {
        return false;
    }
}
function AddTeacherRequest($text){
    global $db, $result;
    $sql= "INSERT INTO DemandeProf (DemandeProf.id_utilisateur, DemandeProf.presentation) VALUES (:id, :text);";
    $request = $db->prepare($sql);
    $request->bindParam(':id', $result['id'] );
    $request->bindParam(':text', $text );
    $request->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['avatar-gen'])) {
        getAvatar();
    }
    if (isset($_POST['agreeAvatar'])) {
        SetNewAvatar();
    }
    if (isset($_POST['change-infos'])) {
        ChangeUserInfos();
    }
    if (isset($_POST['delete'])) {
        DeleteUser();
    }
    if (isset($_POST['teacher'])) {
        if(!CheckInTeacherTable()){
            AddTeacherRequest($_POST['cv']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/jossua.css">
    <title><?php echo htmlspecialchars($titre); ?></title>
</head>
<body id="profile">
    <section class="infos">
        <div class="user-info">
            <div class="headband">
                <button onclick="location.href='../'" class="cross">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-arrow-left-icon lucide-circle-arrow-left"><circle cx="12" cy="12" r="10"/><path d="M16 12H8"/><path d="m12 8-4 4 4 4"/></svg>
                </button>
                <button id="generatePopup" onclick="OpenAvatarPopup()">
                    <img src="<?php echo htmlspecialchars($avatar) ?>" alt="Your profile picture" class="avatar"/>
                </button>
            </div>

            <h2><?php echo htmlspecialchars($result['prenom'])?></h2>
            <p><?php echo htmlspecialchars($result['e_mail'])?></p>
            <button onclick="location.href='reset-pass.php'" class="hover">Changer mon mot de passe</button>
            
        </div>
        <div class="change-info">
            <h2>Mes infos</h2>
            <form method="POST">
                <input type="test" id="new-name" name="new-name" placeholder="<?php echo $result['nom']?>" required>
                <input type="test" id="new-firstname" name="new-firstname" placeholder="<?php echo $result['prenom']?>" required>
                <button class="hover" type="submit" id="change-infos" name='change-infos' onclick="OpenMessagePopup()">Enregistrer</button>
            </form>
        </div>
        <?php
            if($result['role'] == 'professeur'){
                echo "<div class='teacher-request'>
                <button class='teacher-button' onclick=\"location.href='dashboard.php'\">Accéder au dashboard prof</button>
                <button class='teacher-button' onclick=\"location.href='../api'\">API</button>
              </div>";
            }
            else{
                if ($profRequest) {
                    echo "<div class='teacher-request' id='inprogress'><h2>Votre demande est en cours de traitement !</h2></div>";
                } else {
                    echo "<div class='teacher-request'><h2>Devenir prof ?</h2>
                    <form method='POST'>
                    <input type='text' name='cv' placeholder='Parlez nous de vous'>
                    <button name='teacher' type='submit'>Devenir prof !</button>
                    </form></div>";
                }
            }?>
            <button id='logout' onclick="location.href='logout.php'">Deconnection</button>
        <div class="delete-account">
            <h2>Zone de danger</h2>
            <p>Cette action est permanente et ne pourra pas être annulée.</p>
            <button onclick="OpenDeleteCheck()">Supprimer mon compte</button>
        </div>
    </section>
    <div class="blurred-bg" id="blurred-bg"></div>
    <div class="popup" id="delete-check">
        <h2>Supprimer le compte ?</h2>
        <p>Cette ne peut pas être annulée</p>
        <div class="button-container">
            <button onclick="CloseDeleteCheck()">Annuler</button>
            <form method="POST">
                <button type="submit" id="delete" name="delete" onclick="CloseDeleteCheck()">Supprimer mon compte</button>
            </form>
        </div>
    </div>
    <div class="popup" id="message-pop">
        <button class="cross" onclick="CloseMessagePopup()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
        <h2>Message :</h2>
        <p>Vos informations ont été modifiées avec succès !</p>
        <button id="Okk" onclick="CloseMessagePopup()">D'accord !</button>
    </div>

    <div class="popup" id="new-pp">
        <button onclick="CloseAvatarPopup()" class="cross">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
        <h2>Changer mon avatar</h2>
        <div class="avatar-container">
            <img src="<?php echo $avatar ?>">
            <?php if(isset($_SESSION['new_avatar']) && $_SESSION['new_avatar'] !== null){echo "<p>&#x2794;</p><img src=". htmlspecialchars($_SESSION['new_avatar'])  ." alt='nouveau avatar' />";}?>
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
</body>
<script src="../<?php echo JS_PATH; ?>/jossua.js"></script>
<script>
    var blurredBg = document.getElementById('blurred-bg');
    var deleteMyAccount = document.getElementById('delete');
    var deletePopup = document.getElementById('delete-check');
    var okClosePopup = document.getElementById('Okk');
    var newPP = document.getElementById('new-pp');
    var loading = document.getElementById('loading-emo');
    var message = document.getElementById('message-pop');

    document.addEventListener("DOMContentLoaded", function () {
        if (localStorage.getItem('avatarPopupOpen') === 'true') {
            OpenAvatarPopup(); 
        }
        if(localStorage.getItem('messagePopOpen') === 'true'){
            OpenMessagePopup();
        }
        if(localStorage.getItem('deletePopup') === 'true'){
            OpenDeleteCheck();
        }
    });
</script>
</html>