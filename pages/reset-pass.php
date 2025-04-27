<?php 
session_start();
require_once("../include/connectdb.php"); 
require_once("../include/sessionManager.php");
if(!IsConnected()){
    header('Location:  connection.php');
    exit();
}
$message = null;
if (isset($_POST['reset'])){
    $pass = $_POST['pass'];
    $newPass = $_POST['new-pass'];
    $newPassCheck = $_POST['new-pass-check'];
    if(isset($pass) && isset($newPass) && isset($newPassCheck)){
        if($newPass == $newPassCheck){
            if($newPass != $pass){
                $db = connectDB();
                $sql = "SELECT Utilisateurs.mot_de_passe FROM Utilisateurs 
                        INNER JOIN sessions ON Utilisateurs.id = sessions.user_id 
                        WHERE sessions.token = :token";
                $request = $db->prepare($sql);
                $request->bindParam(':token', $_COOKIE['user_token']);
                $request->execute();
                $user = $request->fetch(PDO::FETCH_ASSOC);
                if($user['mot_de_passe']){
                    if (password_verify($pass, $user['mot_de_passe'])) {                        
                        $passNewHash = password_hash($newPass, PASSWORD_DEFAULT);
                        $sql = 'UPDATE Utilisateurs 
                                INNER JOIN sessions ON Utilisateurs.id = sessions.user_id 
                                SET Utilisateurs.mot_de_passe = :newpass 
                                WHERE sessions.token = :token';
                        $request = $db->prepare($sql);
                        $request->bindParam(':newpass', $passNewHash);
                        $request->bindParam(':token', $_COOKIE['user_token']);
                        $request->execute();
                        header('Location: profile.php');
                        exit();
                    } else {
                        $message = 'Erreur: Votre mot de passe actuel est incorrect.';
                    }
                } else {
                    $message = 'Erreur: impossible de vérifier votre mot de passe.';
                }
            } else {
                $message = 'Vous ne pouvez pas mettre le même mot de passe.';
            }
        } else {
            $message = 'Les mots de passe doivent correspondre.';
        }
    }
}
$titre = SITE_NAME . ' - Réinitialisation';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/jossua.css">
    <title><?php echo $titre; ?></title>
</head>
<body id="reset">
    <?php if ($message): ?>
                <div class="print-message"><?php echo $message; ?></div>
    <?php endif; ?> 
    <section class="reset">
        <button onclick="location.href='profile.php'" class="cross">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-arrow-left-icon lucide-circle-arrow-left"><circle cx="12" cy="12" r="10"/><path d="M16 12H8"/><path d="m12 8-4 4 4 4"/></svg>
        </button>
        <h1>Réinitialiser le mot de passe</h1>
        <form method="POST">
            <input type="password" name="pass" placeholder="Mot de passe" required>
            <input type="password" name="new-pass" placeholder="Nouveau mot de passe" required>
            <input type="password" name="new-pass-check" placeholder="Nouveau mot de passe" required>
            <button type="submit" name="reset">Réinitialiser</button>
        </form>
    </section>
</body>
</html>