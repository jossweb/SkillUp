<?php
session_start();
require_once("../include/connectdb.php"); 
require_once("../include/sessionManager.php");
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/profile.css">
    <title><?php echo $titre; ?></title>
</head>
<body>
    <section class="infos">
        <div class="user-info">
            <div class="headband">
                <a href="../" class="cross">&crarr; </a>
                <button id="generatePopup" onclick="OpenAvatarPopup()">
                    <img src="<?php echo $avatar ?>" alt="Your profile picture" class="avatar"/>
                </button>
            </div>

            <h2><?php echo $result['prenom']?></h2>
            <p><?php echo $result['e_mail']?></p>
            <a href="reset-pass.php"><button>Changer mon mot de passe</button></a>
            
        </div>
        <div class="change-info">
            <h2>Mes infos</h2>
            <form method="POST">
                <input type="test" id="new-name" name="new-name" placeholder="<?php echo $result['nom']?>" required>
                <input type="test" id="new-firstname" name="new-firstname" placeholder="<?php echo $result['prenom']?>" required>
                <button type="submit" id="change-infos" name='change-infos' onclick="OpenMessagePopup()">Enregistrer</button>
            </form>
        </div>
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
        <button class="cross" onclick="CloseMessagePopup()">X</button>
        <h2>Message :</h2>
        <p>Vos informations ont été modifiées avec succès !</p>
        <button id="Okk" onclick="CloseMessagePopup()">D'accord !</button>
    </div>

    <div class="popup" id="new-pp">
        <button onclick="CloseAvatarPopup()" class="cross">X</button>
        <h2>Changer mon avatar</h2>
        <div class="avatar-container">
            <img src="<?php echo $avatar ?>">
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
</body>

<script>
    //get elements
    var blurredBg = document.getElementById('blurred-bg');
    var deleteMyAccount = document.getElementById('delete');
    var deletePopup = document.getElementById('delete-check');
    var okClosePopup = document.getElementById('Okk');
    var newPP = document.getElementById('new-pp');
    var loading = document.getElementById('loading-emo');
    var message = document.getElementById('message-pop')

    function ShowLoading(){
        loading.style.display = "block";
    }
    function OpenAvatarPopup() {
        blurredBg.style.opacity = "90%";
        newPP.style.opacity = "1";
        newPP.style.display = "block";
        localStorage.setItem('avatarPopupOpen', 'true'); 
    }
    function CloseAvatarPopup() {
        blurredBg.style.opacity = "0";
        newPP.style.opacity = "0";
        newPP.style.display = "none";
        localStorage.removeItem('avatarPopupOpen'); 
    }
    function CheckOpenCondition(){
        var nameValue = document.getElementById("new-name").value;
        var firstnameValue = document.getElementById("new-firstname").value;
        if(nameValue && firstnameValue){
            OpenMessagePopup();
        }
    }
    function OpenMessagePopup(){
        blurredBg.style.opacity = "90%";
        message.style.opacity = "1";
        message.style.display = "block";
        localStorage.setItem('messagePopOpen', 'true');

    }
    function CloseMessagePopup(){
        blurredBg.style.opacity = "0";
        message.style.opacity = "0";
        message.style.display = "none";
        localStorage.removeItem('messagePopOpen');
    }
    function OpenDeleteCheck(){
        blurredBg.style.opacity = "90%";
        deletePopup.style.opacity = "1";
        deletePopup.style.display = "block";
        localStorage.setItem('deletePopup', 'true');
    }
    function CloseDeleteCheck(){
        blurredBg.style.opacity = "0";
        deletePopup.style.opacity = "0";
        deletePopup.style.display = "none";
        localStorage.removeItem('deletePopup');
        }
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