<?php
require_once("../include/config.php");
require_once("../include/connectdb.php");
require_once("../include/sessionManager.php");
$titre = SITE_NAME . ' - Formations';
$db = connectDB();

$categorie = isset($_GET['categorie']) ? intval($_GET['categorie']) : '';
$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';

$categoriesQuery = "SELECT id, nom FROM Categories ORDER BY nom";
$categoriesStmt = $db->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$requete = "SELECT c.id, c.nom, c.description, c.illustration_url, cat.nom as categorie_nom 
            FROM Cours c 
            LEFT JOIN Categories cat ON c.categorie_id = cat.id 
            WHERE 1=1";
$param = [];

if (!empty($categorie)) {
    $requete .= " AND c.categorie_id = :categorie";
    $param[':categorie'] = $categorie;
}

if (!empty($recherche)) {
    $requete .= " AND (c.nom LIKE :recherche_nom OR c.description LIKE :recherche_desc)";
    $param[':recherche_nom'] = "%" . $recherche . "%";
    $param[':recherche_desc'] = "%" . $recherche . "%";
}

$requete .= " ORDER BY c.date_creation DESC";

$stmt = $db->prepare($requete);
$stmt->execute($param);
$resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(IsConnected()){
    $sql = 'SELECT Utilisateurs.avatar_url FROM sessions INNER JOIN Utilisateurs ON Utilisateurs.id = sessions.user_id WHERE sessions.token = :token';
    $request = $db->prepare($sql);
    $request->bindParam(':token', $_COOKIE['user_token']);
    $request->execute();
    $result = $request->fetch(PDO::FETCH_ASSOC);
    
    if ($result && isset($result['avatar_url'])) {
        $avatar = $result['avatar_url'];
    } else {
        $avatar = null;
    }
    if($avatar == null){
        $avatar = '../assets/images/user.svg';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titre);?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/assets.css">
    <!-- j'utilise la base de la landing pour la nav , header mais aussi pour reprendre les cards etc.. -->
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/landing.css">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/formations.css">
    <style>
        .logo {
            width: 100px;
            height: 40px;
            background-image: url('../<?php echo IMG_PATH; ?>/logo-light.svg');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            margin: 0;
        }

        @media (prefers-color-scheme: dark) {
            .logo {
                background-image: url('../<?php echo IMG_PATH; ?>/logo-dark.svg');
            }
        }
    </style>
</head>
<body>
    <!-- A PARTIR DE LA LANDING : -->
    <header>
        <svg id="hg" width="549" height="477" viewBox="0 0 549 477" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path class="hg-path" fill-rule="evenodd" clip-rule="evenodd" d="M549 45.7348L289.099 397.04L189.066 301.434L61.8308 477L0 431.873L178.579 185.46L279.237 281.663L487.615 0L549 45.7348Z" fill="#A042F0" fill-opacity="0.3"></path>
        </svg>
        <nav>
            <a href="/" class="logo" aria-label="SkillUp"></a>
            <input type="checkbox" id="burger-toggle" class="burger-toggle">
            <label for="burger-toggle" class="burger-menu">
                <span></span>
                <span></span>
                <span></span>
            </label>
            <div class="nav-links">
                <ul>
                    <li><a href="/">Accueil</a></li>
                    <li><a href="/pages/formations.php">Formations</a></li>
                    <li><a href="/pages/categories.php">Catégories</a></li>
                </ul>
                <div class="mobile">
                    <?php
                        if (IsConnected()) {
                            echo '<button class="profile-btn mobile-profile-btn" onclick="location.href=\'/pages/profile.php\';">
                                    <img src="' . htmlspecialchars($avatar) . '" alt="Profil">
                                    <span>Mon compte</span>
                                  </button>';
                        } else {
                            echo '<button class="mobile-login-btn" onclick="location.href=\'connection.php\';">Se connecter</button>';
                        }
                    ?>
                </div>
            </div>
            <div class="desktop">
                <?php
                    if (IsConnected()) {
                        echo '<button class="profile-btn" onclick="location.href=\'/pages/profile.php\';">
                                <img src="' . htmlspecialchars($avatar) . '" alt="Profil">
                              </button>';
                    } else {
                        echo '<button onclick="location.href=\'connection.php\';">Se connecter</button>';
                    }
                ?>
            </div>
        </nav>
        <div class="hero-content">
            <h1>Découvrez nos formations</h1>
            <p>Trouvez le cours qui vous correspond et développez vos compétences</p>
            <div class="search-container">
                <form action="formations.php" method="get">
                    <input type="text" name="recherche" placeholder="Rechercher une formation..." value="<?php echo htmlspecialchars($recherche); ?>">
                    <button type="submit">Rechercher</button>
                </form>
            </div>
        </div>
        <svg id="bd" width="549" height="477" viewBox="0 0 549 477" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M549 45.7348L289.099 397.04L189.066 301.434L61.8308 477L0 431.873L178.579 185.46L279.237 281.663L487.615 0L549 45.7348Z" fill="#A042F0" fill-opacity="0.3"></path>
        </svg>
    </header>

    <main>
        <div class="filters-container">
            <div class="results-count">
                <span><?php echo count($resultat); ?> résultats</span>
            </div>
            <div class="filters">
                <form action="formations.php" method="get">
                    <?php if (!empty($recherche)): ?>
                        <input type="hidden" name="recherche" value="<?php echo htmlspecialchars($recherche); ?>">
                    <?php endif; ?>
                    <select name="categorie" onchange="this.form.submit()">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($categorie == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <div class="courses-grid">
            <?php if (!empty($resultat)): ?>
                <?php foreach ($resultat as $row): ?>
                    <div class="course-card">
                        <div class="trending-image">
                            <?php if (!empty($row['illustration_url'])): ?>
                                <img src="<?php echo htmlspecialchars($row['illustration_url']); ?>" alt="Illustration de <?php echo htmlspecialchars($row['nom']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <span>Pas d'image</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="course-content">
                            <div class="course-category"><?php echo htmlspecialchars($row['categorie_nom'] ?? 'Non catégorisé'); ?></div>
                            <h2 class="course-title"><?php echo htmlspecialchars($row['nom']); ?></h2>
                            <p class="course-description"><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . (strlen($row['description']) > 100 ? '...' : ''); ?></p>
                            <a href="detail.php?id=<?php echo $row['id']; ?>" class="trending-link">Voir le cours</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <h2>Aucune formation trouvée</h2>
                    <p>Essayez d'ajuster vos critères de recherche ou explorez d'autres catégories</p>
                    <a href="formations.php" class="reset-link">Réinitialiser la recherche</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <!-- A PARTIR DE LA LANDING : -->
    <footer>
        <div class="footer-content">
            <div class="footer-brand">
                <div class="logo" aria-label="SkillUp"></div>
                <p class="copyright">Copyright © 2025 - SkillUp</p>
            </div>
            <div class="footer-links">
                <div class="footer-col">
                    <h3>Plateforme</h3>
                    <ul>
                        <li><a href="/">Accueil</a></li>
                        <li><a href="/pages/formations.php">Formations</a></li>
                        <li><a href="/pages/categories.php">Catégories</a></li>
                        <li><a href="/pages/dashboard.php">Tableau de Bord</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Catégories</h3>
                    <ul>
                        <li><a href="/pages/formations.php?categorie=12">Algorithmique</a></li>
                        <li><a href="/pages/formations.php?categorie=9">Architecture Logicielle</a></li>
                        <li><a href="/pages/formations.php?categorie=6">Base de Données</a></li>
                        <li><a href="/pages/formations.php?categorie=10">Cloud Computing</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <ul>
                        <li><a href="/pages/formations.php?categorie=2">Cybersécurité</a></li>
                        <li><a href="/pages/formations.php?categorie=7">Développement Mobile</a></li>
                        <li><a href="/pages/formations.php?categorie=1">Développement Web</a></li>
                        <li><a href="/pages/formations.php?categorie=11">DevOps</a></li>
                        <li><a href="/pages/formations.php?categorie=13">Sciences & Ingénierie</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <script>
        // Listener au chargement du DOM
        document.addEventListener('DOMContentLoaded', function() {
            // Selectionner l'input checkbox burger-toggle
            const burgerToggle = document.querySelector('.burger-toggle');
            // Si l'input existe
            if (burgerToggle) {
                // Ecouter le changement de l'input checkbox
                burgerToggle.addEventListener('change', function() {
                    // Si l'input est coché
                    if (this.checked) {
                        // Ajouter la classe no-scroll au html
                        document.documentElement.classList.add('no-scroll');
                    } else {
                        // Sinon retirer la classe no-scroll du html
                        document.documentElement.classList.remove('no-scroll');
                    }
                });
            }
        });
    </script>
</body>
</html>