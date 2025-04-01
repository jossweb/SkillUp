<?php
require_once("../include/config.php"); 
$titre = SITE_NAME . ' - Accueil';


$connexion = new mysqli($serveur, $utilisateur, $mot_de_passe, $base_de_donnees);

if ($connexion->connect_error) {
    die("Connexion échouée :" . $connexion->connect_error);
}

$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : '';
$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';

$requete = "SELECT titre, description, image FROM formations WHERE 1=1";
if ($categorie) {
    $categorie = $connexion->real_escape_string($categorie);
    $requete .= "AND categorie = '$categorie'";
} elseif ($recherche) {
    $recherche = $connexion->real_escape_string($recherche);
    $requete .= "AND (titre LIKE '%$recherche%' OR description LIKE '%$recherche%')";
}

$resultat = $connexion->query($requete);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formations en <?php echo htmlspecialchars($categorie ? $categorie : ($recherche ? "Recherche : " . $recherche : "")); ?></title>
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>/formations.css">
</head>
<body>
<header>
<nav>
    <div class="logo">
    <img src="assets/images/logo-light.svg" alt="light logo"> 
</div>
<ul>
        <li><a href="pageAccueil" Accueil></a> </li>
        <li><a href="formations.php" Formations></a> </li>
        <li><a href="categorie.php" Catégories></a> </li>
        <li><a href="pageProfil" Profil></a> </li>
    </ul>
</nav>
</header>
<div class="barre">
    <input type="text" placeholder="WEB">
    <button>Rechercher</button>
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

    <section class="container">
        <?php 
        if ($resultat) {
            if ($resultat->num_rows > 0) {
                while ($row = $resultat->fetch_assoc()) {
                    echo "<div class='cas'>";
                    echo "<img src='" . htmlspecialchars($row["image"]) . "' alt='" . htmlspecialchars($row["titre"]) . "'>";
                    echo "<h2>" . htmlspecialchars($row["titre"]) . "</h2>";
                    echo "<p>" . htmlspecialchars($row["description"]) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "Aucune formation trouvée.";
            }
            $resultat->free_result();
            } else {
                echo "Erreur de requête : " . $connexion->error;
            }
        ?>
        </section>

    <?php $connexion->close();
    ?>

</body>
    </html>