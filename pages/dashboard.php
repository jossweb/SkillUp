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
    $sql_prof = 'SELECT COUNT(DISTINCT Inscriptions.id) AS nb_inscription, COUNT(DISTINCT Vues.id) AS nb_vues, COUNT(DISTINCT Favoris.id) AS nb_likes FROM Utilisateurs INNER JOIN Cours ON Utilisateurs.id = Cours.prof_id LEFT JOIN Inscriptions ON Cours.id = Inscriptions.cours_id LEFT JOIN Vues ON Cours.id = Vues.cours_id LEFT JOIN Favoris ON Cours.id = Favoris.cours_id WHERE Utilisateurs.e_mail = :email';    
    $request_prof = $db->prepare($sql_prof);
    $request_prof->bindParam(':email', $result['e_mail']);
    $request_prof->execute();
    $result_prof = $request_prof->fetch(PDO::FETCH_ASSOC);
    $nb_inscription = $result_prof['nb_inscription'];
    $nb_vues = $result_prof['nb_vues'];
    $nb_likes = $result_prof['nb_likes'];

    $today = date('Y-m-d');
    $sql_prof_yesterday = 'SELECT COUNT(DISTINCT Inscriptions.id) AS nb_inscription, COUNT(DISTINCT Vues.id) AS nb_vues, COUNT(DISTINCT Favoris.id) AS nb_likes FROM Utilisateurs INNER JOIN Cours ON Utilisateurs.id = Cours.prof_id LEFT JOIN Inscriptions ON Cours.id = Inscriptions.cours_id AND Inscriptions.date_inscription < :today1 LEFT JOIN Vues ON Cours.id = Vues.cours_id AND Vues.date_vue < :today2 LEFT JOIN Favoris ON Cours.id = Favoris.cours_id AND Favoris.date_ajout < :today3 WHERE Utilisateurs.e_mail = :email';    
    $request_prof_yesterday = $db->prepare($sql_prof_yesterday);
    $request_prof_yesterday->bindParam(':email', $result['e_mail']);
    $request_prof_yesterday->bindParam(':today1', $today);
    $request_prof_yesterday->bindParam(':today2', $today);
    $request_prof_yesterday->bindParam(':today3', $today);
    $request_prof_yesterday->execute();
    $result_prof_yesterday = $request_prof_yesterday->fetch(PDO::FETCH_ASSOC);
    $nb_inscription_yesterday = $result_prof_yesterday['nb_inscription'];
    $nb_vues_yesterday = $result_prof_yesterday['nb_vues'];
    $nb_likes_yesterday = $result_prof_yesterday['nb_likes'];


    //get graph data
    $date = (new DateTime())->modify('-30 days')->format('Y-m-d');
    $sql_data = "SELECT DATE(Inscriptions.date_inscription) AS date, COUNT(Inscriptions.id) AS count FROM Inscriptions INNER JOIN Cours ON Inscriptions.cours_id = Cours.id INNER JOIN Utilisateurs ON Cours.prof_id = Utilisateurs.id WHERE Utilisateurs.e_mail = :email AND Inscriptions.date_inscription >= :date GROUP BY date ORDER BY date ASC";
    $request_data = $db->prepare($sql_data);
    $request_data->bindParam(':email', $result["e_mail"]);
    $request_data->bindParam(':date', $date);
    $request_data->execute();
    $result_data = $request_data->fetchAll(PDO::FETCH_KEY_PAIR);

    //prepare theses values for js
    $counts = [];
    for ($i = 0; $i <= 30; $i++) {
        $currentDate = (new DateTime())->modify("-$i days")->format('Y-m-d');
        $counts[] = isset($result_data[$currentDate]) ? (int)$result_data[$currentDate] : 0;
    }
    $counts_json = json_encode(array_reverse($counts));

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
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/jossua.css"> 
    <title><?php echo htmlspecialchars($titre);?></title>
<body id="dash">
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
    <button onclick="location.href='../'" class="backhome">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
    </button>
    <div class="top">
      <img src="<?php echo $avatar ?>" alt="profile picture" class="avatar">
      <div class="title">
        <h1>Tableau de bord professeur</h1>
        <p><?php echo $result['e_mail'] ?></p>
      </div>
    </div>
    <div class="toggle">
        <button id="login-btn" class="isSelected left" onclick="toggleStats(false)">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-no-axes-column-increasing-icon lucide-chart-no-axes-column-increasing"><line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/></svg> 
        Statistiques</button>
        <button onclick="toggleStats(true)" class="right" id="register-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open-icon lucide-book-open"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
        <p>Mes Cours</p>
      </button>
    </div>
    <div id="stats">
      <div class="stats-header">
        <div class="stat-box">
            <h2>Total étudiants</h2>
            <p>Utilisateurs inscrites à vos cours</p>
            <div class="value-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              <p class="value"><?php echo htmlspecialchars($nb_inscription)?></p>
            </div>
            <?php if($nb_inscription - $nb_inscription_yesterday > 0){
                echo '<p class="green"> + '. htmlspecialchars($nb_inscription - $nb_inscription_yesterday) ." inscription(s) aujourd'hui </p>";
              } 
              ?> 
        </div>
        <div class="stat-box">
            <h2>Vues totales</h2>
            <p>Sur la totalité des vos cours</p>
            <div class="value-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-icon lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
              <p class="value"><?php echo $nb_vues?></p>
            </div>
            <?php if($nb_vues - $nb_vues_yesterday > 0){
                echo '<p class="green"> + '. htmlspecialchars($nb_vues - $nb_vues_yesterday) ." vue(s) aujourd'hui<p>";
              } 
              ?> 
        </div>
        <div class="stat-box">
            <h2>Popularité</h2>
            <p>Utilisateurs qui apprécient votre travail</p>
            <div class="value-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-thumbs-up-icon lucide-thumbs-up"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/></svg>
              <p class="value"><?php echo $nb_likes?></p>
            </div>
            <?php if($nb_likes - $nb_likes_yesterday != 0){
                echo '<p class="green"> + '. htmlspecialchars($nb_likes - $nb_likes_yesterday) ." nouveau(x) favori<p>";
              } 
              ?> 
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
            <button onclick="CloseDeleteCheckDash()">Annuler</button>
            <form method="POST">
              <input type="hidden" id="id-c" name="id">
              <button type="submit" id="delete" name="delete" onclick="CloseDeleteCheck()">Supprimer ce cours</button>
            </form>
        </div>
    </div>
</body>
<script src="../<?php echo JS_PATH; ?>/jossua.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  var blurredBg = document.getElementById('blurred-bg');
  var popup = document.getElementById('delete-check');
  var hiddenInput = document.getElementById('id-c');
  const today = new Date();
  const day = String(today.getDate()).padStart(2, '0');
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const formattedDate = `${day}/${month}`

  document.addEventListener("DOMContentLoaded", function () {
  var ctx = document.getElementById("myBarChart").getContext("2d");

  function getLast31Days() {
    const dates = [];
    const today = new Date();

    for (let i = 30; i >= 0; i--) {
      const date = new Date(today);
      date.setDate(today.getDate() - i);

      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');

      dates.push(`${day}/${month}`);
    }

    return dates;
  }
  var myBarChart = new Chart(ctx, {
    type: "bar",
    data: {
    labels: getLast31Days(),
    datasets: [
      {
        
      label: "Nouveaux inscrits cette semaine",
      data: <?php echo $counts_json; ?>,
      backgroundColor: "rgba(160, 66, 240, 0.8)",
      borderColor: "rgba(0, 0, 0, 1)",
      borderWidth: 1,
      },
    ],
    },
    options: {
    responsive: true,
    maintainAspectRatio: false, 
    plugins: {
      legend: {
      position: 'top',
      },
    },
    scales: {
      y: {
      beginAtZero: true,
      },
    },
    },
  });
  });
</script>
</html>