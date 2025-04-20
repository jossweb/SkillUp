<?php
require_once("../include/config.php"); 
$titre = SITE_NAME . ' - Accueil';

$connexion = new mysqli($serveur, $utilisateur, $mot_de_passe, $base_de_donnees);

if ($connexion->connect_error) {
    die("Connexion échouée :" . $connexion->connect_error);
}

$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : '';
$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';

$requete = "SELECT id, nom, description, illustration_url FROM Cours WHERE 1=1";
if ($categorie) {
    $categorie = $connexion->real_escape_string($categorie);
    $requete .= "AND categorie_id = '$categorie'";
} elseif ($recherche) {
    $recherche = $connexion->real_escape_string($recherche);
    $requete .= " AND (nom LIKE '%$recherche%' OR description LIKE '%$recherche%')";
}

$resultat = $connexion->query($requete);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>/formations.css">
</head>
<body>
<header>
<nav>
    <div class="logo">
    <img src="assets/images/logo-light.svg" alt="light logo"> 
</div>
<ul>
        <li><a href="pageAccueil"> Accueil </a> </li>
        <li><a href="formations.php"> Formations </a> </li>
        <li><a href="categorie.php"> Catégories </a> </li>
        <li><a href="connection.php"> <img src="assets/images/user.svg" alt="user"> </a> </li>
    </ul>
</nav>
</header>

<main>
<div class="barre">
    <form action="formations.php" method="get">
    <input type="text" name="recherche" placeholder="Formations diplômantes en C">
    <button type="submit">Rechercher</button>
</form>
</div>

    <div class="filtrage">
    <span><?php echo $resultat ? $resultat->num_rows : 0; ?> résultats</span>
        <select>
            <option value="1">Filtrer</option>
            <option value="2">Les plus populaires</option>
            <option value="3">Les mieux notés</option>
            <option value="4">Les plus récents</option>
        </select>
    </div>

    <ul class="liste">
    <?php 
        if ($resultat && $resultat->num_rows > 0) {
            while ($row = $resultat->fetch_assoc()) {
        echo '<li class="resultat">';
        echo '  <h2 class="titre">'. htmlspecialchars($row["nom"]) . '</h2>';
        echo '  <p class="description">' . htmlspecialchars($row["description"]) . '</p>';
        echo '  <div class="info">';
        echo'        <span><img src="assets/images/heart.svg" alt="illustration_url">' . htmlspecialchars($row["illustration_url"]) . '</span>';
        echo'        <span><img src="assets/images/clock.svg" alt="illustration_url">' .  htmlspecialchars($row["illustration_url"]) . '</span>';
echo '</div>';
echo '</li>';
            }
        } else {
            echo '<p> Aucune formation trouvée. </p>';
        }
?>
</ul>
</main>

    <?php $connexion->close();
    ?>

</body>
    </html>

