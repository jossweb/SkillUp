<?php
session_start();
require_once("include/config.php");
require_once("include/stats.php");
require_once("include/sessionManager.php");
$titre = SITE_NAME . ' - Accueil';
$stats = getStats();
if (IsConnected()) {
    $db = connectDB();
    $sql = 'SELECT Utilisateurs.avatar_url, Utilisateurs.role FROM sessions INNER JOIN Utilisateurs ON Utilisateurs.id = sessions.user_id WHERE sessions.token = :token';
    $request = $db->prepare($sql);
    $request->bindParam(':token', $_COOKIE['user_token']);
    $request->execute();
    $result = $request->fetch(PDO::FETCH_ASSOC);
    if ($result && isset($result['avatar_url'])) {
        $avatar = $result['avatar_url'];
    } else {
        $avatar = null;
    }
    if ($avatar == null) {
        $avatar = './assets/images/user.svg';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titre); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="./<?php echo CSS_PATH; ?>/assets.css">
    <link rel="stylesheet" type="text/css" href="./<?php echo CSS_PATH; ?>/landing.css">
    <style>
        .logo {
            width: 100px;
            height: 40px;
            background-image: url('./<?php echo IMG_PATH; ?>/logo-light.svg');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            margin: 0;
        }

        @media (prefers-color-scheme: dark) {
            .logo {
                background-image: url('./<?php echo IMG_PATH; ?>/logo-dark.svg');
            }
        }
    </style>
</head>

<body>
    <header>
        <svg id="hg" width="549" height="477" viewBox="0 0 549 477" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path class="hg-path" fill-rule="evenodd" clip-rule="evenodd"
                d="M549 45.7348L289.099 397.04L189.066 301.434L61.8308 477L0 431.873L178.579 185.46L279.237 281.663L487.615 0L549 45.7348Z"
                fill="#A042F0" fill-opacity="0.3"></path>
        </svg>
        <nav>
            <a href="./" class="logo" aria-label="SkillUp"></a>
            <input type="checkbox" id="burger-toggle" class="burger-toggle">
            <label for="burger-toggle" class="burger-menu">
                <span></span>
                <span></span>
                <span></span>
            </label>
            <div class="nav-links">
                <ul>
                    <li><a href="./">Accueil</a></li>
                    <li><a href="pages/formations.php">Formations</a></li>
                    <li><a href="pages/categories.php">Catégories</a></li>
                </ul>
                <div class="mobile">
                    <?php
                    if (IsConnected()) {
                        echo '<button class="profile-btn mobile-profile-btn" onclick="location.href=\'pages/profile.php\';">
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
                    echo '<button class="profile-btn" onclick="location.href=\'pages/profile.php\';">
                                <img src="' . htmlspecialchars($avatar) . '" alt="Profil">
                              </button>';
                } else {
                    echo '<button onclick="location.href=\'pages/connection.php\';">Se connecter</button>';
                }
                ?>
            </div>
        </nav>
        <div>
            <h1>Bienvenue sur la plateforme idéale <br> pour apprendre gratuitement</h1>
            <p>Apprenez à votre rythme, développez <br> vos compétences librement.</p>
            <?php if (IsConnected()) {
                if ($result['role'] == 'professeur') { ?>
                    <button onclick="location.href='pages/dashboard.php';">Accéder à mon dashboard</button>
                <?php } else { ?>
                    <button onclick="location.href='pages/profile.php';">Accéder à mon profil</button>
                <?php }
            } else { ?>
                <button onclick="location.href='pages/connection.php';">Inscrivez-vous maintenant</button>
            <?php } ?>
        </div>
        <svg id="bd" width="549" height="477" viewBox="0 0 549 477" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M549 45.7348L289.099 397.04L189.066 301.434L61.8308 477L0 431.873L178.579 185.46L279.237 281.663L487.615 0L549 45.7348Z"
                fill="#A042F0" fill-opacity="0.3"></path>
        </svg>
        <!-- Pas sûr de vouloir le garder finalement -->
        <!-- <button id="scroll" onclick="document.getElementById('why').scrollIntoView()">
            En apprendre plus
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down-icon lucide-arrow-down"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>
        </button> -->
    </header>
    <section id="why">
        <div class="content">
            <h1 class="title">Pourquoi SkillUp ?</h1>
            <div class="features">
                <div class="feature">
                    <div class="icon-container">
                        <svg class="icon" width="48" height="49" viewBox="0 0 48 49" fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M8 14.5C6.89543 14.5 6 15.3954 6 16.5V32.5C6 33.6046 6.89543 34.5 8 34.5H40C41.1046 34.5 42 33.6046 42 32.5V16.5C42 15.3954 41.1046 14.5 40 14.5H8ZM2 16.5C2 13.1863 4.68629 10.5 8 10.5H40C43.3137 10.5 46 13.1863 46 16.5V32.5C46 35.8137 43.3137 38.5 40 38.5H8C4.68629 38.5 2 35.8137 2 32.5V16.5ZM24 22.5C22.8954 22.5 22 23.3954 22 24.5C22 25.6046 22.8954 26.5 24 26.5C25.1046 26.5 26 25.6046 26 24.5C26 23.3954 25.1046 22.5 24 22.5ZM18 24.5C18 21.1863 20.6863 18.5 24 18.5C27.3137 18.5 30 21.1863 30 24.5C30 27.8137 27.3137 30.5 24 30.5C20.6863 30.5 18 27.8137 18 24.5ZM10 24.5C10 23.3954 10.8954 22.5 12 22.5H12.02C13.1246 22.5 14.02 23.3954 14.02 24.5C14.02 25.6046 13.1246 26.5 12.02 26.5H12C10.8954 26.5 10 25.6046 10 24.5ZM34 24.5C34 23.3954 34.8954 22.5 36 22.5H36.02C37.1246 22.5 38.02 23.3954 38.02 24.5C38.02 25.6046 37.1246 26.5 36.02 26.5H36C34.8954 26.5 34 25.6046 34 24.5Z" />
                        </svg>
                    </div>
                    <h2>Formation 100% gratuite</h2>
                    <p>Apprenez sans dépenser un centime, avec un accès complet aux cours et aux ressources pour vous
                        former en toute liberté.</p>
                </div>

                <div class="feature">
                    <div class="icon-container">
                        <svg class="icon" width="48" height="49" viewBox="0 0 48 49" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M8 4.5C8 3.39543 8.89543 2.5 10 2.5H38C39.1046 2.5 40 3.39543 40 4.5C40 5.60457 39.1046 6.5 38 6.5H36V12.844C35.9997 14.4351 35.3674 15.9613 34.2422 17.0862C34.2421 17.0863 34.2423 17.0861 34.2422 17.0862L26.8284 24.5L34.242 31.9136C34.2419 31.9135 34.2421 31.9136 34.242 31.9136C35.3672 33.0385 35.9997 34.5645 36 36.1556V42.5H38C39.1046 42.5 40 43.3954 40 44.5C40 45.6046 39.1046 46.5 38 46.5H10C8.89543 46.5 8 45.6046 8 44.5C8 43.3954 8.89543 42.5 10 42.5H12V36.156C12.0003 34.5649 12.6326 33.0387 13.7578 31.9138C13.7577 31.9139 13.7579 31.9137 13.7578 31.9138L21.1716 24.5L13.758 17.0864C13.7579 17.0864 13.7581 17.0865 13.758 17.0864C12.6328 15.9615 12.0003 14.4355 12 12.8444V6.5H10C8.89543 6.5 8 5.60457 8 4.5ZM16 6.5V12.8436C16 12.8434 16 12.8437 16 12.8436C16.0002 13.3738 16.211 13.8827 16.586 14.2576L24 21.6716L31.4138 14.2578C31.7888 13.8829 31.9998 13.3742 32 12.844C32 12.8439 32 12.8441 32 12.844V6.5H16ZM24 27.3284L16.5862 34.7422C16.2112 35.1171 16.0002 35.6258 16 36.156C16 36.1559 16 36.1561 16 36.156V42.5H32V36.1564C32 36.1563 32 36.1566 32 36.1564C31.9998 35.6262 31.789 35.1173 31.414 34.7424L24 27.3284Z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <h2>Avancez à votre rythme</h2>
                    <p>Organisez votre apprentissage selon votre emploi du temps, sans pression ni contrainte de temps.
                    </p>
                </div>

                <div class="feature">
                    <div class="icon-container">
                        <svg class="icon" width="48" height="49" viewBox="0 0 48 49" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect width="48" height="48" transform="translate(0 0.5)" fill="none" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M16 2.5C17.1046 2.5 18 3.39543 18 4.5V6.5H30V4.5C30 3.39543 30.8954 2.5 32 2.5C33.1046 2.5 34 3.39543 34 4.5V6.5H38C39.5913 6.5 41.1174 7.13214 42.2426 8.25736C43.3679 9.38258 44 10.9087 44 12.5V17.5C44 18.6046 43.1046 19.5 42 19.5C40.8954 19.5 40 18.6046 40 17.5V12.5C40 11.9696 39.7893 11.4609 39.4142 11.0858C39.0391 10.7107 38.5304 10.5 38 10.5H34V12.5C34 13.6046 33.1046 14.5 32 14.5C30.8954 14.5 30 13.6046 30 12.5V10.5H18V12.5C18 13.6046 17.1046 14.5 16 14.5C14.8954 14.5 14 13.6046 14 12.5V10.5H10C9.46957 10.5 8.96086 10.7107 8.58579 11.0858C8.21071 11.4609 8 11.9696 8 12.5V18.5H14C15.1046 18.5 16 19.3954 16 20.5C16 21.6046 15.1046 22.5 14 22.5H8V40.5C8 41.0304 8.21071 41.5391 8.58579 41.9142C8.96086 42.2893 9.46957 42.5 10 42.5H18.6C19.7046 42.5 20.6 43.3954 20.6 44.5C20.6 45.6046 19.7046 46.5 18.6 46.5H10C8.4087 46.5 6.88258 45.8679 5.75736 44.7426C4.63214 43.6174 4 42.0913 4 40.5V12.5C4 10.9087 4.63214 9.38258 5.75736 8.25736C6.88258 7.13214 8.4087 6.5 10 6.5H14V4.5C14 3.39543 14.8954 2.5 16 2.5ZM22 18.5C23.1046 18.5 24 19.3954 24 20.5V23.5551C25.3145 22.3795 26.8735 21.5069 28.5668 21.0014C30.3977 20.4548 32.3328 20.3534 34.2108 20.7055C36.0889 21.0576 37.8558 21.8532 39.3644 23.026C40.873 24.1987 42.0797 25.7148 42.8841 27.448C43.3491 28.4499 42.9139 29.6391 41.912 30.1041C40.9101 30.5691 39.7209 30.1339 39.2559 29.132C38.7196 27.9765 37.9151 26.9658 36.9094 26.184C35.9036 25.4021 34.7257 24.8718 33.4737 24.637C32.2216 24.4022 30.9316 24.4699 29.711 24.8343C28.5999 25.166 27.5755 25.7348 26.7078 26.5H30C31.1046 26.5 32 27.3954 32 28.5C32 29.6046 31.1046 30.5 30 30.5H22C20.8954 30.5 20 29.6046 20 28.5V20.5C20 19.3954 20.8954 18.5 22 18.5ZM32 36.5C32 35.3954 32.8954 34.5 34 34.5H42C43.1046 34.5 44 35.3954 44 36.5V44.5C44 45.6046 43.1046 46.5 42 46.5C40.8954 46.5 40 45.6046 40 44.5V41.4449C38.6854 42.6205 37.1265 43.4931 35.4332 43.9986C33.6023 44.5452 31.6672 44.6466 29.7892 44.2945C27.9111 43.9424 26.1442 43.1468 24.6356 41.974C23.127 40.8013 21.9203 39.2852 21.1159 37.552C20.6509 36.5501 21.0861 35.3609 22.088 34.8959C23.0899 34.4309 24.2791 34.8661 24.7441 35.868C25.2804 37.0235 26.0849 38.0342 27.0906 38.816C28.0964 39.5979 29.2743 40.1282 30.5263 40.363C31.7784 40.5978 33.0684 40.5301 34.289 40.1657C35.4001 39.834 36.4245 39.2652 37.2922 38.5H34C32.8954 38.5 32 37.6046 32 36.5Z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <h2>Contenus toujours à jour</h2>
                    <p>Profitez de formations constamment actualisées pour rester à la pointe des connaissances et
                        compétences du moment.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="stat">
        <svg class="shape shape-bottom-left" viewBox="0 0 440 440" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M220,434.0901932104801C264.31024056895495,441.6130655255292,310.8416105553644,421.09510092399114,344.30782265915883,391.09503968120305C376.1138799485698,362.5831882767303,383.7638671655749,318.08544825396774,395.2900773489821,276.9551986848739C406.29742248646994,237.6764725993674,423.33761362341573,196.80127270517053,409.510751153327,158.42422430239108C395.83720913855177,120.47272338353497,355.26057047460836,102.41872218176806,323.4628374636889,77.59562107433104C290.24647416780493,51.66505727495762,261.73422136487955,16.310327001739797,220.00000000000006,10.481459476674612C175.479076422765,4.26338334782088,127.1343277356712,15.98482969937638,93.16836148570268,45.43122580518573C60.31572603094929,73.91243283462865,56.96631408557444,121.316111566086,44.20846609809941,162.8818682042778C31.707621136959503,203.61029512523106,3.330743013396418,245.73916206329744,19.60238896653323,285.1131309027941C35.781123536023,324.262273689961,88.99599715562277,328.2479773082508,122.99162295370077,353.520576300777C157.5719069453079,379.2278145071985,177.5189384786475,426.87787394682016,220,434.0901932104801"
                fill="#A042F0" fill-opacity="0.3" />
        </svg>
        <h1 class="title">SkillUp c'est:</h1>
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['utilisateurs']); ?></div>
                <div class="stat-label">utilisateurs</div>
            </div>

            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['cours']); ?></div>
                <div class="stat-label">cours</div>
            </div>

            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['profs']); ?></div>
                <div class="stat-label">profs</div>
            </div>
        </div>
    </section>

    <section id="trending-courses">
        <div class="content">
            <h1 class="title">Cours Tendances</h1>
            <div class="trending-container">
                <?php
                include_once('./include/stats.php');
                $trendingCourses = getTrendingCourses(4);

                if (empty($trendingCourses)) {
                    echo '<p class="no-courses">Aucun cours tendance disponible pour le moment.</p>';
                } else {
                    foreach ($trendingCourses as $course) {
                        ?>
                        <div class="trending-card">
                            <div class="trending-image">
                                <img src="<?= htmlspecialchars($course['illustration_url']) ?>"
                                    alt="<?= htmlspecialchars($course['nom']) ?>">
                            </div>
                            <div class="trending-content">
                                <div class="trending-category">
                                    <?= htmlspecialchars($course['categorie_nom'] ?? 'Non catégorisé') ?></div>
                                <h3 class="trending-title"><?= htmlspecialchars($course['nom']) ?></h3>
                                <p class="trending-desc">
                                    <?= htmlspecialchars(substr($course['description'], 0, 100)) . (strlen($course['description']) > 100 ? '...' : '') ?>
                                </p>
                                <a href="pages/detail.php?id=<?= $course['id'] ?>" class="trending-link">Voir le cours</a>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <section id="how">
        <svg class="shape shape-bottom-right" viewBox="0 0 440 440" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M220,413.58941515441984C259.95326635217594,412.96636588463554,293.87205709233154,389.45597812635526,327.9080767665793,368.5227259343352C365.03607508059395,345.68780694173006,417.7026257404433,329.71882806568675,425.95669940536175,286.9193882079221C434.31524868152616,243.57821613141914,380.62770460227244,214.19747871528972,365.68002926982183,172.6656891424486C351.5107595627011,133.29668260539714,373.7465910426454,79.4100236058215,341.623061638416,52.600216848533165C309.7144716895735,25.96979527619521,261.2203182309422,45.77084645989595,220,51.08336286774528C184.3423686250396,55.67895455345726,150.8271966542031,64.16195965364258,119.20373631463744,81.26584501242183C82.92516327489392,100.88749765855965,33.412795940574426,116.25092341036913,24.89666045887457,156.6070821822719C16.283031733147066,197.42524074781613,65.0777675465806,226.55412593647105,80.35236082555782,265.3742685002022C94.17693123167433,300.50920036031374,83.72661776911445,344.44624702551374,109.6186975528965,371.9268290462537C137.26431173827694,401.26852637143116,179.69094331747098,414.21801277921395,220,413.58941515441984"
                fill="#A042F0" fill-opacity="0.3" />
        </svg>
        <h1 class="title">Comment ça marche ?</h1>
        <div class="steps-container">
            <div class="step-card">
                <h2 class="step-title">Fais un compte</h2>
                <p class="step-description">Rien de plus simple !</p>
            </div>
            <div class="step-line"></div>
            <div class="step-card">
                <h2 class="step-title">Explore les cours</h2>
                <p class="step-description">Choisis ce qui t'intéresse !</p>
            </div>
            <div class="step-line"></div>
            <div class="step-card">
                <h2 class="step-title">Apprends à ton rythme</h2>
                <p class="step-description">Progresse à ton propre tempo !</p>
            </div>
        </div>
    </section>

    <section id="testimonials">
        <div class="content">
            <h1 class="title">Ce que nos étudiants disent</h1>
            <div class="testimonials-container">
                <div class="testimonial-card">
                    <div class="testimonial-avatar">
                        <img src="./assets/images/user1.webp" alt="Utilisateur">
                    </div>
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <span>★★★★★★★★★★</span>
                        </div>
                        <p class="testimonial-text">"Grâce à SkillUp, j'ai pu apprendre le développement web en
                            seulement 3 mois et j'ai trouvé un emploi dans ce domaine. Les cours sont très bien
                            structurés."</p>
                        <div class="testimonial-author">Mathieu D.</div>
                        <div class="testimonial-role">Développeur Junior</div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-avatar">
                        <img src="./assets/images/user2.webp" alt="Utilisateur">
                    </div>
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <span>★★★★★</span>
                        </div>
                        <p class="testimonial-text">"La qualité des cours est exceptionnelle, surtout pour une
                            plateforme gratuite. J'ai particulièrement apprécié les cours sur l'intelligence
                            artificielle qui sont à jour avec les dernières technologies."</p>
                        <div class="testimonial-author">Sophie L.</div>
                        <div class="testimonial-role">Étudiante en informatique</div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-avatar">
                        <img src="./assets/images/user3.webp" alt="Utilisateur">
                    </div>
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <span>★★★★</span>
                        </div>
                        <p class="testimonial-text">"En tant qu'autodidacte, j'ai toujours eu du mal à trouver des
                            ressources bien structurées. SkillUp a changé la donne pour moi. La progression est logique
                            et on peut avancer à son rythme."</p>
                        <div class="testimonial-author">Thomas R.</div>
                        <div class="testimonial-role">Reconversion professionnelle</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="faq">
        <svg class="shape shape-top-left" viewBox="0 0 440 440" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M220,417.49133754676825C259.59733997554616,415.1784036736629,284.5950480373144,375.97923738261926,313.92325535692396,349.2742705850674C339.5982447146035,325.8957622823835,362.9567392597893,302.2760256777744,379.8504799738675,271.9385693957933C400.6738806372658,234.54430497799154,431.33459152572175,196.07623435757165,422.56150740607563,154.18377654513833C413.62802729586537,111.52540993981575,373.3103264969542,82.7556401276428,335.6410747782344,60.83371541138328C300.7292651405994,40.51650634390707,260.39149877532924,35.91383640911213,220,36.30147431821874C179.97845290925147,36.6855617946395,140.18584353712632,42.289683194831625,105.9604061033118,63.03796474272353C69.52066995541148,85.12860731199996,38.61512440901157,116.36277722188814,22.717317824709685,155.89897083559163C6.084610950234502,197.2627851342611,-2.542916206113805,245.7700736946296,16.220415069101836,286.2120008342151C34.332195931420884,325.2495955586427,81.57430958206965,337.42674456450584,117.75154567222907,360.73292393286636C151.34394237861204,382.37389233357413,180.10825746498762,419.8214678692447,220,417.49133754676825"
                fill="#A042F0" fill-opacity="0.3" />
        </svg>
        <h1 class="title">Foire aux questions</h1>
        <div class="faq-container">
            <div class="faq-item">
                <input type="checkbox" id="faq-1" class="faq-toggle">
                <label for="faq-1" class="faq-question">
                    SkillUp est-il vraiment gratuit ?
                    <svg class="faq-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-plus-icon lucide-plus">
                        <path id="horizontale" d="M5 12h14" />
                        <path id="verticale" d="M12 5v14" />
                    </svg>
                </label>
                <div class="faq-answer">
                    <p>Oui, SkillUp est une plateforme 100% gratuite ! Tous nos cours et ressources sont accessibles
                        sans frais.</p>
                </div>
            </div>
            <div class="faq-item">
                <input type="checkbox" id="faq-2" class="faq-toggle">
                <label for="faq-2" class="faq-question">
                    Les contenus sont-ils vérifiés avant publication ?
                    <svg class="faq-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-plus-icon lucide-plus">
                        <path id="horizontale" d="M5 12h14" />
                        <path id="verticale" d="M12 5v14" />
                    </svg>
                </label>
                <div class="faq-answer">
                    <p>Absolument ! Tous les contenus publiés sur SkillUp passent par un processus de vérification
                        rigoureux.</p>
                </div>
            </div>
            <div class="faq-item">
                <input type="checkbox" id="faq-3" class="faq-toggle">
                <label for="faq-3" class="faq-question">
                    Comment puis-je contribuer en tant que formateur ?
                    <svg class="faq-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-plus-icon lucide-plus">
                        <path id="horizontale" d="M5 12h14" />
                        <path id="verticale" d="M12 5v14" />
                    </svg>
                </label>
                <div class="faq-answer">
                    <p>Pour devenir formateur sur SkillUp, il vous suffit de créer un compte, puis de postuler pour
                        devenir Prof. Après validation de votre profil par notre équipe, vous pourrez créer et publier
                        vos propres cours sur la plateforme.</p>
                </div>
            </div>
            <div class="faq-item">
                <input type="checkbox" id="faq-4" class="faq-toggle">
                <label for="faq-4" class="faq-question">
                    Puis-je accéder aux cours depuis n'importe quel appareil ?
                    <svg class="faq-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-plus-icon lucide-plus">
                        <path id="horizontale" d="M5 12h14" />
                        <path id="verticale" d="M12 5v14" />
                    </svg>
                </label>
                <div class="faq-answer">
                    <p>Oui, SkillUp est entièrement responsive ! Vous pouvez accéder à tous nos cours depuis votre
                        ordinateur, tablette ou smartphone. Notre plateforme s'adapte automatiquement à la taille de
                        votre écran pour vous offrir une expérience d'apprentissage optimale.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="cta">
        <div class="cta-card">
            <h2 class="cta-title">Vous êtes prof ? Partagez votre expertise !</h2>
            <p class="cta-text">Rejoignez SkillUp et créez des cours pour aider des milliers d'étudiants à progresser.
            </p>
            <button>Devenir formateur</button>
        </div>
    </section>
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
                        <li><a href="./">Accueil</a></li>
                        <li><a href="pages/formations.php">Formations</a></li>
                        <li><a href="pages/categories.php">Catégories</a></li>
                        <li><a href="pages/dashboard.php">Tableau de Bord</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Catégories</h3>
                    <ul>
                        <li><a href="pages/formations.php?categorie=12">Algorithmique</a></li>
                        <li><a href="pages/formations.php?categorie=9">Architecture Logicielle</a></li>
                        <li><a href="pages/formations.php?categorie=6">Base de Données</a></li>
                        <li><a href="pages/formations.php?categorie=10">Cloud Computing</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <ul>
                        <li><a href="pages/formations.php?categorie=2">Cybersécurité</a></li>
                        <li><a href="pages/formations.php?categorie=7">Développement Mobile</a></li>
                        <li><a href="pages/formations.php?categorie=1">Développement Web</a></li>
                        <li><a href="pages/formations.php?categorie=11">DevOps</a></li>
                        <li><a href="pages/formations.php?categorie=13">Sciences & Ingénierie</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <div id="cookie">
            <h2>Utilisation des Cookies</h2>
            <p>Ce site web utilise des cookies indispensable à son fonctionnement. Aucunes de vos données ne seront utilisées !</p>
            <div class="progress-bar"></div>
    </div>
    <script>
        // mon objectif etait de tout faire en CSS malheureusement j'ai pas le choix
        // si je veux bloquer le scroll de la page
        // je dois mettre le body en position fixed
        // sauf que avec les sections en relative
        // ça casse tout et c'est pas beau 

        // listener au chargement du DOM
        document.addEventListener('DOMContentLoaded', function () {
            // selectionner l'input checkbox burger-toggle
            const burgerToggle = document.querySelector('.burger-toggle');
            // si l'input existe
            if (burgerToggle) {
                // ecouter le changement de l'input checkbox
                burgerToggle.addEventListener('change', function () {
                    // si l'input est coché
                    if (this.checked) {
                        // ajouter la classe no-scroll au html
                        document.documentElement.classList.add('no-scroll');
                    } else {
                        // sinon retirer la classe no-scroll du html
                        document.documentElement.classList.remove('no-scroll');
                    }
                });
            }
        });
        setTimeout(HideCookiePopup, 8000);

        function HideCookiePopup(){
            document.getElementById('cookie').style.opacity = "0";
        }
    </script>
</body>
</html>