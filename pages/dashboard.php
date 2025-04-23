<?php
    session_start();
    require_once("../include/connectdb.php"); 
    require_once("../include/sessionManager.php");
    if(!IsConnected()){
        header('Location:  connection.php');
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
        header('Location:  connection.php');
        exit();
    }
    //reset cookies
    setcookie("activeChapId", "", time() - 3600, "./");
    setcookie("activeChapTitle", "", time() - 3600, "./");
    setcookie("activeChapMd", "", time() - 3600, "./");

    $avatar = $result['avatar_url'];
    if($avatar == null){
        $avatar = 'https://remyweb.fr/images/1356835268082008064.webp';
    }
    $sql_prof = 'SELECT COUNT(Inscriptions.id) AS nb_inscription, COUNT(Vues.id) AS nb_vues, COUNT(Favoris.id) AS nb_likes FROM Utilisateurs INNER JOIN Cours ON Utilisateurs.id = Cours.prof_id INNER JOIN Vues ON Cours.id = Vues.cours_id INNER JOIN Favoris ON Cours.id = Favoris.cours_id INNER JOIN Inscriptions ON Cours.id = Inscriptions.cours_id WHERE Utilisateurs.e_mail = :email';
    $request_prof = $db->prepare($sql_prof);
    $request_prof->bindParam(':email', $result['e_mail']);
    $request_prof->execute();
    $result_prof = $request_prof->fetch(PDO::FETCH_ASSOC);
    $nb_inscription = $result_prof['nb_inscription'];
    $nb_vues = $result_prof['nb_vues'];
    $nb_likes = $result_prof['nb_likes'];

    $today = date('Y-m-d');
    $sql_prof_yesterday = 'SELECT COUNT(Inscriptions.id) AS nb_inscription, COUNT(Vues.id) AS nb_vues, COUNT(Favoris.id) AS nb_likes FROM Utilisateurs INNER JOIN Cours ON Utilisateurs.id = Cours.prof_id INNER JOIN Vues ON Cours.id = Vues.cours_id INNER JOIN Favoris ON Cours.id = Favoris.cours_id INNER JOIN Inscriptions ON Cours.id = Inscriptions.cours_id WHERE Utilisateurs.e_mail = :email AND Inscriptions.date_inscription < :today1 AND Vues.date_vue < :today2 AND Favoris.date_ajout < :today3';
    $request_prof_yesterday = $db->prepare($sql_prof_yesterday);
    $request_prof_yesterday->bindParam(':email', $result['e_mail']);
    $request_prof_yesterday->bindParam(':today1', $today);
    $request_prof_yesterday->bindParam(':today2', $today);
    $request_prof_yesterday->bindParam(':today3', $today);
    $request_prof_yesterday->execute();
    $result_prof_yesterday = $request_prof_yesterday->fetch(PDO::FETCH_ASSOC);
    $nb_inscription_yesterday = $result_prof['nb_inscription'];
    $nb_vues_yesterday = $result_prof['nb_vues'];
    $nb_likes_yesterday = $result_prof['nb_likes'];


    //get graph data
    $date = (new DateTime())->modify('-31 days')->format('Y-m-d');
    $sql_data = "SELECT Inscriptions.date_inscription, COUNT(Inscriptions.id) FROM Inscriptions INNER JOIN Cours ON Inscriptions.cours_id = Cours.id INNER JOIN Utilisateurs ON Cours.prof_id = Utilisateurs.id WHERE Utilisateurs.e_mail = :email AND Inscriptions.date_inscription > :date GROUP BY Inscriptions.date_inscription ORDER BY Inscriptions.date_inscription DESC";
    $request_data = $db->prepare($sql_data);
    $request_data->bindParam(':email', $result["e_mail"]);
    $request_data->bindParam(':date', $date);
    $request_data->execute();
    $result_data = $request_data->fetch(PDO::FETCH_ASSOC);

    //get courses infos
    $sql_courses = "SELECT Cours.id, Cours.nom, Cours.illustration_url, Cours.description, Categories.nom AS 'cat_nom', COUNT(Vues.id) AS 'Vues', COUNT(Inscriptions.id) AS 'Inscrits', COUNT(Favoris.id) AS 'like' FROM Cours INNER JOIN Utilisateurs ON Cours.prof_id = Utilisateurs.id LEFT JOIN Vues ON Cours.id = Vues.cours_id LEFT JOIN Inscriptions ON Cours.id = Inscriptions.cours_id LEFT JOIN Favoris ON Cours.id = Favoris.cours_id LEFT JOIN Categories ON Categories.id = Cours.categorie_id WHERE Utilisateurs.id = :id GROUP BY Cours.id;";
    $request_courses = $db->prepare($sql_courses);
    $request_courses->bindParam(":id", $result["id"]);
    $request_courses->execute();
    $result_courses = $request_courses->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      if(isset($_POST["id"])){
        $sql_delete = "DELETE FROM Cours WHERE id = :cid AND prof_id = :pid;";
        $request_delete = $db->prepare($sql_delete);
        $request_delete->bindParam(":cid", $_POST["id"]);
        $request_delete->bindParam(":pid", $result['id']);
        $request_delete->execute();
        if ($request_delete->rowCount() > 0) {
          $_SESSION['message'] = 'Success';
      } else {
          $_SESSION['message'] = 'Error';
      }
      header("Location: dashboard.php");
      exit();
      }

    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/dashboard.css"> 
    <title><?php echo htmlspecialchars($titre);?></title>
<body>
  <?php
    if (isset($_SESSION['message'])) {
      if($message='Success'){
        echo '<div class="message">Cours Supprimé avec succès</div>';
      }else{
        echo '<div class="message">Erreur, impossible de supprimer le cours</div>';
      }
      unset($_SESSION['message']); 
    }
  ?>
  <div class="stats-container">
    <div class="top">
      <img src="<?php echo $avatar ?>" alt="profile picture" class="avatar">
      <div class="title">
        <h1>Tableau de bord professeur</h1>
        <p><?php echo $result['e_mail'] ?></p>
      </div>
    </div>
    <div class="toggle">
        <button id="login-btn" class="isSelected left" onclick="toggleStats(false)">
        <svg width="29" height="26" viewBox="0 0 29 26" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M13.7983 21.6666V16.2499V10.8333" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M20.3467 21.6666V4.33325" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M7.25 21.6666V17.3333" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>  
        Statistiques</button>
        <button onclick="toggleStats(true)" class="right" id="register-btn">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 7V21" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M3 18C2.73478 18 2.48043 17.8946 2.29289 17.7071C2.10536 17.5196 2 17.2652 2 17V4C2 3.73478 2.10536 3.48043 2.29289 3.29289C2.48043 3.10536 2.73478 3 3 3H8C9.06087 3 10.0783 3.42143 10.8284 4.17157C11.5786 4.92172 12 5.93913 12 7C12 5.93913 12.4214 4.92172 13.1716 4.17157C13.9217 3.42143 14.9391 3 16 3H21C21.2652 3 21.5196 3.10536 21.7071 3.29289C21.8946 3.48043 22 3.73478 22 4V17C22 17.2652 21.8946 17.5196 21.7071 17.7071C21.5196 17.8946 21.2652 18 21 18H15C14.2044 18 13.4413 18.3161 12.8787 18.8787C12.3161 19.4413 12 20.2044 12 21C12 20.2044 11.6839 19.4413 11.1213 18.8787C10.5587 18.3161 9.79565 18 9 18H3Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <p>Mes Cours</p>
      </button>
    </div>
    <div id="stats">
      <div class="stats-header">
        <div class="stat-box">
            <h2>Total étudiants</h2>
            <p>Toutes les personnes inscrites à vos cours</p>
            <div class="value-box">
              <svg width="41" height="41" viewBox="0 0 41 41" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M27.3332 35.875V32.4583C27.3332 30.646 26.6132 28.9079 25.3317 27.6264C24.0502 26.3449 22.3121 25.625 20.4998 25.625H10.2498C8.43752 25.625 6.69944 26.3449 5.41794 27.6264C4.13644 28.9079 3.4165 30.646 3.4165 32.4583V35.875" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M15.3748 18.7917C19.1488 18.7917 22.2082 15.7323 22.2082 11.9583C22.2082 8.18439 19.1488 5.125 15.3748 5.125C11.6009 5.125 8.5415 8.18439 8.5415 11.9583C8.5415 15.7323 11.6009 18.7917 15.3748 18.7917Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M37.5835 35.8751V32.4584C37.5824 30.9444 37.0784 29.4736 36.1508 28.277C35.2232 27.0803 33.9245 26.2257 32.4585 25.8472" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M27.3335 5.34717C28.8034 5.72352 30.1062 6.57837 31.0365 7.77695C31.9669 8.97553 32.4719 10.4497 32.4719 11.967C32.4719 13.4843 31.9669 14.9584 31.0365 16.157C30.1062 17.3556 28.8034 18.2104 27.3335 18.5868" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <p class="value"><?php echo htmlspecialchars($nb_inscription)?></p>
              <?php if($nb_inscription - $nb_inscription_yesterday > 0){
                echo '<p> + '. htmlspecialchars($nb_inscription_yesterday) ." inscription aujourd'hui <p>";
              } 
              ?> 
            </div>
        </div>
        <div class="stat-box">
            <h2>Vues totales</h2>
            <p>Sur la totalité des vos cours</p>
            <div class="value-box">
              <svg width="34" height="33" viewBox="0 0 34 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.92107 16.9784C2.803 16.6697 2.803 16.3301 2.92107 16.0214C4.07098 13.3152 6.02288 11.0013 8.52933 9.37312C11.0358 7.74493 13.9839 6.87573 16.9999 6.87573C20.0159 6.87573 22.964 7.74493 25.4705 9.37312C27.9769 11.0013 29.9288 13.3152 31.0787 16.0214C31.1968 16.3301 31.1968 16.6697 31.0787 16.9784C29.9288 19.6846 27.9769 21.9985 25.4705 23.6267C22.964 25.2548 20.0159 26.124 16.9999 26.124C13.9839 26.124 11.0358 25.2548 8.52933 23.6267C6.02288 21.9985 4.07098 19.6846 2.92107 16.9784Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M17 20.625C19.3472 20.625 21.25 18.7782 21.25 16.5C21.25 14.2218 19.3472 12.375 17 12.375C14.6528 12.375 12.75 14.2218 12.75 16.5C12.75 18.7782 14.6528 20.625 17 20.625Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <p class="value"><?php echo $nb_vues?></p>
              <?php if($nb_inscription - $nb_inscription_yesterday > 0){
                echo '<p> + '. htmlspecialchars($nb_inscription_yesterday) ." inscription aujourd'hui <p>";
              } 
              ?> 
            </div>
        </div>
        <div class="stat-box">
            <h2>Popularité</h2>
            <p>Toutes les personnes qui apprécient votre travail</p>
            <div class="value-box">
              <svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9.9165 14.1667V31.1667" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21.2502 8.32992L19.8335 14.1666H28.0927C28.5325 14.1666 28.9663 14.269 29.3598 14.4657C29.7532 14.6624 30.0954 14.948 30.3593 15.2999C30.6232 15.6518 30.8016 16.0603 30.8803 16.4931C30.959 16.9258 30.9358 17.371 30.8127 17.7933L27.5118 29.1266C27.3402 29.7151 26.9823 30.2321 26.4918 30.5999C26.0014 30.9677 25.4049 31.1666 24.7918 31.1666H5.66683C4.91538 31.1666 4.19471 30.8681 3.66336 30.3367C3.13201 29.8054 2.8335 29.0847 2.8335 28.3333V16.9999C2.8335 16.2485 3.13201 15.5278 3.66336 14.9964C4.19471 14.4651 4.91538 14.1666 5.66683 14.1666H9.57683C10.1039 14.1663 10.6205 14.019 11.0685 13.7412C11.5165 13.4634 11.8781 13.0661 12.1127 12.5941L17.0002 2.83325C17.6682 2.84152 18.3258 3.00066 18.9237 3.29876C19.5216 3.59687 20.0445 4.02624 20.4532 4.55479C20.8618 5.08334 21.1458 5.69741 21.2838 6.35112C21.4218 7.00483 21.4103 7.68128 21.2502 8.32992Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <p class="value"><?php echo $nb_likes?></p>
              <?php if($nb_inscription - $nb_inscription_yesterday > 0){
                echo '<p> + '. htmlspecialchars($nb_inscription_yesterday) ." inscription aujourd'hui <p>";
              } 
              ?> 
            </div>
        </div>
      </div>
      <?php
        if ($result_data) {
          echo '<div class="chart-container">';
          echo '<canvas id="myBarChart"></canvas>';
        echo '</div>';
        } else {
            echo "<p class='nothing-to-show'>Personne n'a encore accédé à votre cours, mais courage, gardez le rythme !!!";
        }
      ?>
    </div>
    <div id="cours">
        <div class="top">
          <h2>Liste des cours</h2>
          <button onclick="location.href='addCourse.php?cours=00000';">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M6.25 15H23.75" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M15 6.25V23.75" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <p>Nouveau cours</p>
          </button>
        </div>
        <?php
        if ($result_courses){
          echo "<table>";
          echo "<thead>";
          echo "<tr>";
          echo "<td class='name'>Nom</td>";
          echo "<td>Catégorie</td>";
          echo "<td>Étudiant</td>";
          echo "<td>Vues</td>";
          echo "<td>J'aimes</td>";
          echo "<td>Actions</td>";
          echo "</tr>";
          echo "</thead>";
          echo "<tbody>";
          foreach ($result_courses as $course) {
            echo "<tr>";
            echo "<td>" . $course['nom'] . "</td>";
            echo "<td>" . $course['cat_nom'] . "</td>";
            echo "<td>" . $course['Inscrits'] . "</td>";
            echo "<td>" . $course['Vues'] . "</td>";
            echo "<td>" . $course['like'] . "</td>";
            echo '<td>
              <button onclick="location.href=\'addCourse.php?cours=' . $course['id'] . '\'">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil">
                  <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                  <path d="m15 5 4 4"/>
                </svg>
              </button>
              <button onclick="OpenPopup('. $course['id'] .')">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2">
                  <path d="M3 6h18"/>
                  <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                  <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                  <line x1="10" y1="11" x2="10" y2="17"/>
                  <line x1="14" y1="11" x2="14" y2="17"/>
                </svg>
              </button>
            </td>';
            echo "</tr>";
          }
          echo "</tbody>";
          echo "</table>";
        }else{
          echo "<h2 class='nothing-to-show'>Vous n'avez pas encore publié de cours ...</h2>";
        }

        ?>
    </div>
  </div>
  <div class="blurred-bg" id="blurred-bg"></div>
  <div class="popup" id="delete-check">
        <h2>Supprimer ce cours ?</h2>
        <p>Cette opération ne peut pas être annulée</p>
        <div class="button-container">
            <button onclick="CloseDeleteCheck()">Annuler</button>
            <form method="POST">
              <input type="hidden" id="id-c" name="id">
              <button type="submit" id="delete" name="delete" onclick="CloseDeleteCheck()">Supprimer ce cours</button>
            </form>
        </div>
    </div>
</body>
<script src="../<?php echo JS_PATH; ?>/forms.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../<?php echo JS_PATH; ?>/dashboard.js"></script>
</html>