<?php
    session_start();
    require_once("../include/connectdb.php"); 
    require_once("../include/sessionManager.php");
    require_once("../include/secureCheck.php");
    if(!IsConnected()){
        header('Location:  connection.php');
        exit();
    }
    $titre = SITE_NAME . ' - Dashboard Admin';
    $db =  connectDB();
    $sql = 'SELECT Utilisateurs.id, Utilisateurs.prenom, Utilisateurs.admin FROM sessions INNER JOIN Utilisateurs ON Utilisateurs.id = sessions.user_id WHERE sessions.token = :token';
    $request = $db->prepare($sql);
    $request->bindParam(':token', $_COOKIE['user_token']);
    $request->execute();
    $result = $request->fetch(PDO::FETCH_ASSOC);
    if(!isset($result['admin']) || $result['admin'] == false){
        header('Location:  ../index.php');
        exit();
    }
    $sql_prof_requests = "SELECT DemandeProf.id , DemandeProf.id_utilisateur, DemandeProf.presentation FROM DemandeProf";
    $request = $db->prepare( $sql_prof_requests);
    $request->execute();
    $profRequests = $request->fetchAll(PDO::FETCH_ASSOC);

    function DeleteRequest($user){
        global $db;
        $sql = "DELETE FROM DemandeProf WHERE id_utilisateur = :id;";
        $request = $db->prepare($sql);
        $request->bindParam(':id', $user);
        $request->execute();
    }
    function AddTeacher($user){
        global $db, $result;
        $sql = "UPDATE Utilisateurs SET Utilisateurs.role = 'professeur' WHERE id = :id;";
        $request = $db->prepare($sql);
        $request->bindParam(':id', $user);
        $request->execute();
        DeleteRequest($user);

        $token = generateToken();
        $currentDate = date('Y-m-d H:i:s');
        $sql = "INSERT INTO KeyTable (actif, token, date_creation) VALUES (true, :token, :date)";
        $request = $db->prepare($sql);
        $request->bindParam(':token', $token);
        $request->bindParam(':date', $currentDate);
        $request->execute();

        $keyId = $db->lastInsertId();

        $sql = 'UPDATE Utilisateurs SET Utilisateurs.key_id = :kid WHERE Utilisateurs.id = :id';
        $request = $db->prepare($sql);
        $request->bindParam(':kid', $keyId);
        $request->bindParam(':id', $_POST['user']);
        $request->execute();

        header("Location: " . $_SERVER['PHP_SELF']);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['user']) && isset($_POST['action'])) {
            if ($_POST['action'] == "1") {
                AddTeacher($_POST['user']);
            } else {
                DeleteRequest($_POST['user']);
            }
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/admin.css">
    <title><?php echo htmlspecialchars($titre);?></title>
</head>
<body>
    <h1>Bienvenue <?php echo htmlspecialchars(htmlspecialchars($result['prenom']))?></h1>
    <?php 
    if(isset($profRequests[0]["id"])){
        echo "<form method='POST'>";
        echo "<table>";
        echo "<thead>";
        echo "<tr>";
        echo "<td>id</td>";
        echo "<td>profil</td>";
        echo "<td>Actions</td>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        foreach ($profRequests as $request) {
            echo "<tr>";
            echo "<td> ". htmlspecialchars($request['id']). "</td>";
            echo "<td>". htmlspecialchars($request['presentation']). "</td>";
            echo '<td>
            <input type="hidden" value="'. htmlspecialchars($request['id_utilisateur']). '" name="user">
            <button name="action" value="1" type="submit"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-icon lucide-circle-check"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg></button>
            <button name="action" value="0" type="submit"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-x-icon lucide-circle-x"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg></button>
            </td>';
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</form>";
    }else{
        echo "<p>Aucune demande en cours</p>";
    }
?>
</body>
</html>