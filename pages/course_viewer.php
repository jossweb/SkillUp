<?php
// Inclure la connexion et les outils nécessaires
session_start(); 
require_once("../include/config.php"); 
require_once("../include/connectdb.php");
require_once("../include/sessionManager.php");
$db = connectDB();

// Vérifier si l'utilisateur est connecté
if (!IsConnected()) {
    header("Location: connection.php");
    exit();
}

// Récupérer l'ID de l'utilisateur
$sql = 'SELECT id FROM Utilisateurs WHERE id = (SELECT user_id FROM sessions WHERE token = :token)';
$request = $db->prepare($sql);
$request->bindParam(':token', $_COOKIE['user_token']);
$request->execute();
$user = $request->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: connection.php");
    exit();
}

$user_id = $user['id'];

// Vérifier si un ID de cours est passé
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: formations.php");
    exit();
}

$id_cours = $_GET['id'];

// Vérifier si l'utilisateur est inscrit à ce cours
$stmt_inscrit = $db->prepare("SELECT id FROM Inscriptions WHERE etudiant_id = :user_id AND cours_id = :cours_id");
$stmt_inscrit->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_inscrit->bindParam(':cours_id', $id_cours, PDO::PARAM_INT);
$stmt_inscrit->execute();

// Si l'utilisateur n'est pas inscrit, le rediriger vers la page de détails du cours
if ($stmt_inscrit->rowCount() === 0) {
    header("Location: detail.php?id=" . $id_cours);
    exit();
}

try {
    // Récupérer les infos du cours
    $stmt_cours = $db->prepare("SELECT c.id, c.nom, c.description, c.illustration_url, c.prof_id, 
                               u.prenom, u.nom as nom_prof, 
                               cat.nom as categorie_nom 
                               FROM Cours c
                               LEFT JOIN Utilisateurs u ON c.prof_id = u.id
                               LEFT JOIN Categories cat ON c.categorie_id = cat.id
                               WHERE c.id = :id");
    $stmt_cours->bindParam(':id', $id_cours, PDO::PARAM_INT);
    $stmt_cours->execute();
    $formation = $stmt_cours->fetch(PDO::FETCH_ASSOC);

    if (!$formation) {
        header("Location: formations.php");
        exit();
    }

    // Récupérer les chapitres du cours
    $stmt_chapitres = $db->prepare("SELECT id, titre, fichier_url FROM Chapitres WHERE cours_id = :id ORDER BY id ASC");
    $stmt_chapitres->bindParam(':id', $id_cours, PDO::PARAM_INT);
    $stmt_chapitres->execute();
    $chapitres = $stmt_chapitres->fetchAll(PDO::FETCH_ASSOC);

    // Si le cours n'a pas de chapitres, rediriger vers la page de détail
    if (empty($chapitres)) {
        header("Location: detail.php?id=" . $id_cours . "?error=no_chapters");
        exit();
    }

    // Gérer l'affichage du chapitre actif
    $chapitre_actif = null;
    
    // Si un chapitre spécifique est demandé
    if (isset($_GET['chapitre']) && is_numeric($_GET['chapitre'])) {
        $chapitre_id = $_GET['chapitre'];
        // Vérifier que le chapitre demandé appartient bien à ce cours
        foreach ($chapitres as $chapitre) {
            if ($chapitre['id'] == $chapitre_id) {
                $chapitre_actif = $chapitre;
                break;
            }
        }
    }
    
    // Si aucun chapitre n'est spécifié ou si le chapitre demandé n'existe pas, prendre le premier
    if ($chapitre_actif === null && !empty($chapitres)) {
        $chapitre_actif = $chapitres[0];
    }
    
    // Lire le contenu du fichier Markdown
    if ($chapitre_actif && !empty($chapitre_actif['fichier_url']) && file_exists($chapitre_actif['fichier_url'])) {
        $contenu_markdown = file_get_contents($chapitre_actif['fichier_url']);
    } else {
        $contenu_markdown = "# Contenu non disponible\n\nLe contenu de ce chapitre n'est pas disponible pour le moment.";
    }

    // Mettre à jour la progression de l'utilisateur (optionnel - pour une future fonctionnalité)
    // Cette partie peut être développée plus tard pour suivre la progression de l'étudiant

} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}

// Récupérer l'avatar de l'utilisateur
$avatar = "../assets/images/user.svg";
if (IsConnected()) {
    $sql = 'SELECT Utilisateurs.avatar_url FROM sessions INNER JOIN Utilisateurs ON Utilisateurs.id = sessions.user_id WHERE sessions.token = :token';
    $request = $db->prepare($sql);
    $request->bindParam(':token', $_COOKIE['user_token']);
    $request->execute();
    $result = $request->fetch(PDO::FETCH_ASSOC);
    
    if ($result && isset($result['avatar_url']) && $result['avatar_url'] != null) {
        $avatar = $result['avatar_url'];
    }
}

$titre = SITE_NAME . ' - ' . htmlspecialchars($formation['nom']) . ' - ' . htmlspecialchars($chapitre_actif['titre']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titre);?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/assets.css">
    <!-- j'utilise la base de la landing pour la nav etc.. -->
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/landing.css">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/viewer.css">
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
                    echo '<button class="mobile-login-btn" onclick="location.href=\'pages/connection.php\';">Se connecter</button>';
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
                echo '<button onclick="location.href=\'pages/connection.php\';">Se connecter</button>';
            }
            ?>
        </div>
    </nav>
    <div class="course-viewer-container">        
        <div class="course-viewer">
            <aside class="chapters-sidebar">
                <h2 class="chapters-title"><?php echo htmlspecialchars($formation['nom']); ?></h2>
                <ul class="chapters-list">
                    <?php foreach ($chapitres as $chapitre): ?>
                    <li class="chapter-item">
                        <a href="course_viewer.php?id=<?php echo $id_cours; ?>&chapitre=<?php echo $chapitre['id']; ?>" 
                           class="chapter-link <?php echo ($chapitre_actif && $chapitre['id'] == $chapitre_actif['id']) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($chapitre['titre']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
            
            <div class="chapter-content">
                <h2 class="chapter-title"><?php echo htmlspecialchars($chapitre_actif['titre']); ?></h2>
                
                <div class="markdown-content" id="markdown-content">
                    <div id="loading">Chargement du contenu...</div>
                </div>
                
                <div class="course-meta">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 6v6l4 2"></path>
                        </svg>
                        <span>Chapitre <?php 
                            $current_chapter_index = 0;
                            foreach ($chapitres as $index => $chapitre) {
                                if ($chapitre['id'] == $chapitre_actif['id']) {
                                    $current_chapter_index = $index + 1;
                                    break;
                                }
                            }
                            echo $current_chapter_index . ' / ' . count($chapitres); 
                        ?></span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Par <?php echo htmlspecialchars($formation['prenom'] . ' ' . $formation['nom_prof']); ?></span>
                    </div>
                </div>
                
                <!-- Barre de progression -->
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?php echo ($current_chapter_index / count($chapitres)) * 100; ?>%;"></div>
                </div>
                
                <?php if (count($chapitres) > 1): ?>
                <div class="navigation-buttons">
                    <?php
                    // Trouver l'index du chapitre actif
                    $currentIndex = 0;
                    foreach ($chapitres as $index => $chapitre) {
                        if ($chapitre['id'] == $chapitre_actif['id']) {
                            $currentIndex = $index;
                            break;
                        }
                    }
                    
                    // Chapitre précédent
                    if ($currentIndex > 0) {
                        $prevChapter = $chapitres[$currentIndex - 1];
                        echo '<a href="course_viewer.php?id=' . $id_cours . '&chapitre=' . $prevChapter['id'] . '" class="nav-button prev-button">';
                        echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>';
                        echo 'Chapitre précédent</a>';
                    } else {
                        echo '<span class="nav-button prev-button" style="visibility: hidden;"></span>';
                    }
                    
                    // Chapitre suivant
                    if ($currentIndex < count($chapitres) - 1) {
                        $nextChapter = $chapitres[$currentIndex + 1];
                        echo '<a href="course_viewer.php?id=' . $id_cours . '&chapitre=' . $nextChapter['id'] . '" class="nav-button next-button">';
                        echo 'Chapitre suivant';
                        echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>';
                        echo '</a>';
                    } else {
                        echo '<span class="nav-button next-button" style="visibility: hidden;"></span>';
                    }
                    ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

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

    <!-- Scripts pour le rendu Markdown -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const markdownContent = document.getElementById('markdown-content');
            const loadingElement = document.getElementById('loading');
            
            <?php if (isset($contenu_markdown)): ?>
            // Échapper les caractères spéciaux JS dans le contenu Markdown
            const markdown = <?php echo json_encode($contenu_markdown); ?>;
            
            // Utiliser marked.js pour convertir le Markdown en HTML
            if (markdownContent && loadingElement) {
                try {
                    loadingElement.remove();
                    markdownContent.innerHTML = marked.parse(markdown);
                    
                    // Traiter les images
                    const images = markdownContent.querySelectorAll('img');
                    images.forEach(img => {
                        img.onerror = function() {
                            this.src = '../assets/images/skillup-logo.svg';
                            this.alt = 'Image non disponible';
                        };
                    });
                    
                    // Ajouter des classes aux éléments générés
                    const headings = markdownContent.querySelectorAll('h1, h2, h3, h4, h5, h6');
                    headings.forEach(heading => {
                        heading.classList.add('markdown-heading');
                    });
                    
                    const codeBlocks = markdownContent.querySelectorAll('pre code');
                    codeBlocks.forEach(block => {
                        block.parentNode.classList.add('markdown-code-block');
                    });
                } catch (e) {
                    markdownContent.innerHTML = '<p>Erreur lors du chargement du contenu.</p>';
                    console.error('Erreur de rendu Markdown:', e);
                }
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>