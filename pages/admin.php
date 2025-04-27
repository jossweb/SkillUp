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
    $users = getTotalUsers();
    $newusers = getNewUsers(7);
    $newcourses = getNewCourses(7);

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

    function getTotalUsers() {
      global $db;
      $sql = "SELECT COUNT(*) as total FROM Utilisateurs";
      $request = $db->prepare($sql);
      $request->execute();
      $result = $request->fetch(PDO::FETCH_ASSOC);
      return $result['total'];
  }

    function getNewUsers($days) {
      global $db;
      $sql = "SELECT COUNT(*) as total FROM Utilisateurs WHERE date_creation >= NOW() - INTERVAL :days DAY";
      $request = $db->prepare($sql);
      $request->bindParam(':days', $days, PDO::PARAM_INT);
      $request->execute();
      $result = $request->fetch(PDO::FETCH_ASSOC);
      return $result['total'];
  }

    function getNewCourses($days) {
      global $db;
      $sql = "SELECT COUNT(*) as total FROM Cours WHERE date_creation >= NOW() - INTERVAL :days DAY";
      $request = $db->prepare($sql);
      $request->bindParam(':days', $days, PDO::PARAM_INT);
      $request->execute();
      $result = $request->fetch(PDO::FETCH_ASSOC);
      return $result['total'];
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
    <button onclick="location.href='../index.php';">Retour à l'accueil</button>
    <h1>Bienvenue <?php echo htmlspecialchars(htmlspecialchars($result['prenom']))?></h1>
    <div id="stats">
    <div class="stats-header">
        <div class="stat-box">
            <div class="stat-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-round-icon lucide-users-round"><path d="M18 21a8 8 0 0 0-16 0"/><circle cx="10" cy="8" r="5"/><path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"/></svg>
                <h2>Utilisateurs totaux</h2>
            </div>
            <div class="value-box">
                <div><?php echo ($users); ?></div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-plus-icon lucide-user-round-plus"><path d="M2 21a8 8 0 0 1 13.292-6"/><circle cx="10" cy="8" r="5"/><path d="M19 16v6"/><path d="M22 19h-6"/></svg>
                <h2>Nouveaux utilisateurs (7j)</h2>
            </div>
            <div class="value-box">
                <div><?php echo ($newusers); ?></div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open-icon lucide-book-open"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                <h2>Nouveaux cours (7j)</h2>
            </div>
            <div class="value-box">
                <div><?php echo ($newcourses); ?></div>
            </div>
        </div>
    </div>
</div>
<?php 
if(isset($profRequests[0]["id"])){
    echo "<h2>Demandes en attente</h2>";
    echo "<table>";
    echo "<thead>";
    echo "<tr>";
    echo "<td>ID</td>";
    echo "<td>CV</td>";
    echo "<td>Actions</td>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    foreach ($profRequests as $request) {
        echo "<tr>";
        echo "<td>DEM-" . htmlspecialchars($request['id']) . "</td>";
        echo "<td>" . htmlspecialchars($request['presentation']) . "</td>";
        echo '<td class="actions-cell">
            <form method="POST">
                <input type="hidden" value="' . htmlspecialchars($request['id_utilisateur']) . '" name="user">
                <button class="accept-btn" name="action" value="1" type="submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-icon lucide-circle-check"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                    Accepter
                </button>
                <button class="refuse-btn" name="action" value="0" type="submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-x-icon lucide-circle-x"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                    Refuser
                </button>
            </form>
        </td>';
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
}else{
    echo "<p>Aucune demande en cours</p>";
}
?>
</body>
</html>