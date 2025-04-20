<?php
    require_once("../include/config.php"); 
    $titre = SITE_NAME . ' - Accueil';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titre);?></title>
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/formations.css"> 
</head>
<body>
<header>
<nav>
    <img src="../assets/images/skillup-logo.svg" alt="light logo"> 
</div>
<ul>
        <li><a href="pageAccueil" Accueil></a>page</li>
        <li><a href="formations.html" Formations>page</a> </li>
        <li><a href="pageCatégories" Catégories></a>page</li>
        <li><a href="pageProfil" Profil></a>page</li>
    </ul>
</nav>
</header>
<div class="barre">
    <input type="text" placeholder="WEB">
    <button>Rechercher</button>
</div>

    <div class="filtrage">
        <span>5 résultats</span>
        <select>
            <option value="1">Filtrer</option>
            <option value="2">Les plus populaires</option>
            <option value="3">Les mieux notés</option>
            <option value="4">Les plus récents</option>
        </select>
    </div>

    <section class="container">
    <div class="cas">
        <img src="../assets/images/frontel.jpg" alt="dvt web">
        <h2>Développement Web</h2>
        <p>Apprenez le développement en langages HTML, CSS et JavaScript pour créer des sites web attrayants et stylisés ; gérez-en aussi les données grâce aux langages PHP et SQL </p>
    </div>

    <div class="cas">
        <img src="../assets/images/machine.jpg" alt="IA et Machine Learning">
        <h2>IA & Machine Learning</h2>
        <p>Soyez au cœur du développement de l’IA avec Python: créez des modèles complexes, analysez des données et manipulez des algorithmes d’IA avancés</p>
    </div>
    
    <div class="cas">
        <img src="../assets/images/java.jpg" alt="dvt jeux mobiles">
        <h2>Développement de jeux mobiles</h2>
        <p>Vous avez toujours voulu développer des jeux mobiles? Réalisez votre rêve en apprenant à créer des jeux avec les langages Unity et Java ! </p>
    </div>
    

    <div class="cas">
        <img src="../assets/images/programmation.jpg" alt="design">
        <h2>Design graphique</h2>
        <p>Exprimez votre créativité en vous formant au design graphique : apprenez à manipuler des outils de création graphique tels que UX/UI, Adobe Illustrator et Photoshop </p>
    </div>

    <div class="cas">
        <img src="../assets/images/programmation.jpg" alt="Programmation C">
        <h2>Programmation en C#</h2>
        <p>Découvrez les bases de la programmation avec cette formation au langage C. Vous souhaitez créer des applications sous Windows? Vous êtes aussi au bon endroit!</p>
    </div>

    <div class="cas">
        <img src="../assets/images/programmation.jpg" alt="Cybersécurité">
        <h2>Cybersécurité</h2>
        <p>La sécurité informatique et ses diverses branches vos ouvrent leurs portes : formez vous dès maintenant en sécurité réseaux et en cryptographie pour la protection contre les menaces informatiques</p>
    </div>

    </section>

</body>
    </html>