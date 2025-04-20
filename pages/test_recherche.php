<?php

//Je vérifie si un identifiant de catégorie est passée dans l'URL avec $GET
if (isset($_GET['categorie'])) {
    //Je récupère l'id de la cat depuis L'URL
    $id = $_GET['categorie'];

    //On affiche un message de confirmation
    echo "<h1>Test de redirection</h1>";
    echo "<p>Vous avez clique sur la catégorie avec L'ID : <strong>" . htmlspecialchars($id) . "</strong></p>";
}else{
    //Aucun identifiant passé dans L'URL
    echo "<h1>Erreur</h1>";
    echo "<p>Aucune catégorie selectionnée</p>";
}
?>