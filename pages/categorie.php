<?php
// Inclure la connexion
session_start(); 
require_once("../include/config.php"); 
$titre = SITE_NAME . ' - catégorie';
// Requête pour récupérer les catégories
$query = "SELECT * FROM categories";

// Préparer et exécuter avec PDO
$stmt = $db->prepare($query);
$stmt->execute();

// Récupérer toutes les lignes sous forme de tab associatifs
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!--Page principale HTML -->
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Catégories-SkillUp</title>
        <link rel="stylesheet" href="../assets/css/categorie.css">
    </head>
    <body>

        <!-- Barre de navigation principale -->
        <nav>
            <p>
                SkillUp
                <img src="../assets/images/skillup-logo.svg" alt="SkillUp">
            </p>
            <ul>
                <li><a href="#">Page</a></li>
                <li><a href="#">Page</a></li>
                <li><a href="#">Page</a></li>
                <li><a href="#">Page</a></li>
            </ul>
            <div id="profil">
                <a href="#"><img src="../assets/images/profil.png" alt="Profil"/></a>
            </div>
        </nav> 
        <!--Conteneur principal-->
        <main>
            <section class="grid-container">
                <?php foreach ($categories as $categorie): ?>
                    <div class="category">
                        <a href="test_recherche.php?categorie=<?php echo urlencode ($categorie['id'])?>">
                            <img src="../assets/images/<?php echo htmlspecialchars($categorie['image']); ?>" alt="Image de <?php echo htmlspecialchars($categorie['nom']); ?>">
                            <h2><?php echo htmlspecialchars($categorie['nom']); ?></h2>
                        </a>
                        <p><?php echo htmlspecialchars($categorie['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </section>
        </main>
    </body>
</html>