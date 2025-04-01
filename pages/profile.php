<?php
session_start();
require_once("../include/connectdb.php"); 
require_once("../include/sessionManager.php");
if(!IsConnected($_SERVER['REMOTE_ADDR'])){
    header('Location:  connection.php');
    exit();
}

$titre = SITE_NAME . ' - profil';
$db =  connectDB();
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
    <section>
        <h1>Mon compte</h1>
        <div>
            <div class="headband">

            </div>
            <h2>Prénom</h2>
            <p>emaildetest@test.com</p>
            <button>Changer mon mot de passe</button>
        </div>
        <div>
            <h2>Mes infos</h2>
        </div>
        <div>
            <h2>Zone de danger</h2>
            <p>Cette action est permanente et ne pourra pas être annulée.</p>
            <button>Supprimer mon compte</button>
        </div>
    </section>
</body>
</html>