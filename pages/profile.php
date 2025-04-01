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
$sql = 'SELECT Utilisateurs.prenom, Utilisateurs.nom, Utilisateurs.e_mail, Utilisateurs.avatar_url, Utilisateurs.role FROM sessions INNER JOIN Utilisateurs ON Utilisateurs.id = sessions.user_id WHERE sessions.token = :token';
$request = $db->prepare($sql);
$request->bindParam(':token', $_COOKIE['user_token']);
$request->execute();
$result = $request->fetch(PDO::FETCH_ASSOC);
$avatar = $result['avatar_url'];
if($avatar == null){
    $avatar = 'https://remyweb.fr/images/1356835268082008064.webp';
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
                <img src="<?php echo $avatar ?>" alt="Your profile picture" class="avatar"/>
            </div>

            <h2><?php echo $result['prenom']?></h2>
            <p><?php echo $result['e_mail']?></p>
            <button>Changer mon mot de passe</button>
        </div>
        <div class="change-info">
            <h2>Mes infos</h2>
            <form method="POST">
                <input type="test" placeholder="<?php echo $result['nom']?>">
                <input type="test" placeholder="<?php echo $result['prenom']?>">
                <button type="submit">Enregistrer</button>
            </form>
        </div>
        <div class="delete-account">
            <h2>Zone de danger</h2>
            <p>Cette action est permanente et ne pourra pas être annulée.</p>
            <button>Supprimer mon compte</button>
        </div>
    </section>
</body>
</html>