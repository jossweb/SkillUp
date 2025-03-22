<!--Affichage et assemblage html+php -->
<?php
//Inclure le fichier qui contient les catégories
include 'catégorie.php';
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Catégorie</title>
        <link rel="stylesheet" href="catégorie.css">
    </head>
    <body>
        <!--Titre principale de la page-->
        <h1>Catégories de Formations</h1>
        <p>Découvrez toutes nos Catégories de formations et trouvez celle qui vous convient.</p>
        <!--Conteneur qui affichera toutes les catégories sous forme de grille-->
        <div class="grid-container">
            <?php 
            //Vérifie si le tableau des catégories n'est pas vide
            if (!empty($categories)){
                //utilisation d'une boucle foreach pour parcourir chaque catégorie et l'afficher
                //Affichage des catégories
                foreach ($categories as $categorie) {
                    echo "<div class='card'>";//Conteneur pour une catégorie
                    echo "<img src='". $categorie['image'] . "' alt='" . $categorie['titre'] . "'>"; //Image
                    echo "<h2>" . $categorie ['titre'] . "</h2>";//Titre
                    echo "<p>" . $categorie ['description'] . "</p>";//Description
                    echo "</div>"; //Fermeture du conteneur de la catégorie
                }
            }else{
                //Message affiché si aucun catégorie n'est disponible
                echo "<p>Aucune catégorie disponible.</p>";
            }
            ?>
        </div>  
    </body>
</html>
   