<?php
session_start();
require_once("../include/config.php");
require_once("../include/connectdb.php");
require_once("../include/sessionManager.php");
$db = connectDB();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_cours = $_GET['id'];

    try {
        // Récupérer infos du cours
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

        // Récupérer le nombre de likes (favoris)
        $stmt_likes = $db->prepare("SELECT COUNT(*) as total FROM Favoris WHERE cours_id = :id");
        $stmt_likes->bindParam(':id', $id_cours, PDO::PARAM_INT);
        $stmt_likes->execute();
        $likes = $stmt_likes->fetch(PDO::FETCH_ASSOC);
        $formation['likes'] = $likes['total'];

        // Récupérer le nombre de vues du cours
        $stmt_vues = $db->prepare("SELECT COUNT(*) as total FROM Vues WHERE cours_id = :id");
        $stmt_vues->bindParam(':id', $id_cours, PDO::PARAM_INT);
        $stmt_vues->execute();
        $vues = $stmt_vues->fetch(PDO::FETCH_ASSOC);
        $formation['vues'] = $vues['total'];

        // Récupérer les chapitres du cours pour afficher le nombre
        $stmt_chapitres = $db->prepare("SELECT COUNT(*) as total FROM Chapitres WHERE cours_id = :id");
        $stmt_chapitres->bindParam(':id', $id_cours, PDO::PARAM_INT);
        $stmt_chapitres->execute();
        $chapitres_count = $stmt_chapitres->fetch(PDO::FETCH_ASSOC);
        $nb_chapitres = $chapitres_count['total'];

        try {
            $ip_address = $_SERVER['REMOTE_ADDR'];

            if (IsConnected()) {
                // Utilisateur connecté - vérifier s'il a déjà vu ce cours
                $sql = 'SELECT id FROM Utilisateurs WHERE id = (SELECT user_id FROM sessions WHERE token = :token)';
                $request = $db->prepare($sql);
                $request->bindParam(':token', $_COOKIE['user_token']);
                $request->execute();
                $user = $request->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Vérifier si une vue existe déjà pour cet utilisateur sur ce cours
                    $check_vue = $db->prepare("SELECT id FROM Vues WHERE utilisateur_id = :user_id AND cours_id = :cours_id");
                    $check_vue->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                    $check_vue->bindParam(':cours_id', $id_cours, PDO::PARAM_INT);
                    $check_vue->execute();

                    // Si aucune vue n'existe, en créer une
                    if ($check_vue->rowCount() == 0) {
                        $stmt_vue = $db->prepare("INSERT INTO Vues (utilisateur_id, cours_id, date_vue) VALUES (:user_id, :cours_id, NOW())");
                        $stmt_vue->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                        $stmt_vue->bindParam(':cours_id', $id_cours, PDO::PARAM_INT);
                        $stmt_vue->execute();
                    }
                }
            } else {
                // Utilisateur non connecté - vérifier si l'IP a déjà vu ce cours
                $check_ip_vue = $db->prepare("SELECT id FROM Vues WHERE ip_address = :ip AND cours_id = :cours_id AND utilisateur_id IS NULL");
                $check_ip_vue->bindParam(':ip', $ip_address, PDO::PARAM_STR);
                $check_ip_vue->bindParam(':cours_id', $id_cours, PDO::PARAM_INT);
                $check_ip_vue->execute();

                // Si aucune vue n'existe pour cette IP, en créer une
                if ($check_ip_vue->rowCount() == 0) {
                    $stmt_ip_vue = $db->prepare("INSERT INTO Vues (ip_address, cours_id, date_vue) VALUES (:ip, :cours_id, NOW())");
                    $stmt_ip_vue->bindParam(':ip', $ip_address, PDO::PARAM_STR);
                    $stmt_ip_vue->bindParam(':cours_id', $id_cours, PDO::PARAM_INT);
                    $stmt_ip_vue->execute();
                }
            }
        } catch (PDOException $e) {
            if (DEBUG) {
                error_log("Erreur lors de l'enregistrement de la vue: " . $e->getMessage());
            }
        }



        // Vérifier si l'utilisateur est inscrit à ce cours
        $is_inscrit = false;
        if (IsConnected()) {
            $sql = 'SELECT id FROM Utilisateurs WHERE id = (SELECT user_id FROM sessions WHERE token = :token)';
            $request = $db->prepare($sql);
            $request->bindParam(':token', $_COOKIE['user_token']);
            $request->execute();
            $user = $request->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $stmt_inscrit = $db->prepare("SELECT id FROM Inscriptions WHERE etudiant_id = :user_id AND cours_id = :cours_id");
                $stmt_inscrit->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                $stmt_inscrit->bindParam(':cours_id', $id_cours, PDO::PARAM_INT);
                $stmt_inscrit->execute();
                $is_inscrit = $stmt_inscrit->rowCount() > 0;
            }
        }

        // Si l'utilisateur s'inscrit au cours
        if (isset($_POST['inscription']) && IsConnected()) {
            $sql = 'SELECT id FROM Utilisateurs WHERE id = (SELECT user_id FROM sessions WHERE token = :token)';
            $request = $db->prepare($sql);
            $request->bindParam(':token', $_COOKIE['user_token']);
            $request->execute();
            $user = $request->fetch(PDO::FETCH_ASSOC);

            if ($user && !$is_inscrit) {
                // Ajouter l'inscription
                $stmt_inscription = $db->prepare("INSERT INTO Inscriptions (etudiant_id, cours_id) VALUES (:user_id, :cours_id)");
                $stmt_inscription->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                $stmt_inscription->bindParam(':cours_id', $id_cours, PDO::PARAM_INT);
                $stmt_inscription->execute();

                // Ajouter une entrée dans la table Vues
                $stmt_vue = $db->prepare("INSERT INTO Vues (utilisateur_id, cours_id) VALUES (:user_id, :cours_id)");
                $stmt_vue->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                $stmt_vue->bindParam(':cours_id', $id_cours, PDO::PARAM_INT);
                $stmt_vue->execute();

                // Rediriger pour éviter la soumission multiple du formulaire
                header("Location: detail.php?id=" . $id_cours);
                exit();
            }
        }

    } catch (PDOException $e) {
        die("Erreur lors de la récupération des détails de la formation : " . $e->getMessage());
    }
} else {
    header("Location: formations.php");
    exit();
}

$titre = SITE_NAME . ' - ' . htmlspecialchars($formation['nom']);

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
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titre; ?></title>
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/detail.css">
</head>

<body>
    <header>
        <nav>
            <div class="logo">
                <!--Mode clair-->
                <svg class="logo-light" width="88" height="20" viewBox="0 0 88 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_328_52)">
                        <path
                            d="M6.14108 16.0043C5.16361 16.0043 4.2428 15.8596 3.37866 15.57C2.51452 15.2805 1.78495 14.81 1.18997 14.1585C0.594984 13.5071 0.198328 12.6457 0 11.5744H2.31619C2.50035 12.1824 2.79784 12.6602 3.20866 13.0076C3.61949 13.3406 4.09406 13.5722 4.63237 13.7025C5.18486 13.8328 5.73734 13.8979 6.28983 13.8979C6.81398 13.8979 7.30272 13.8256 7.75604 13.6808C8.22353 13.536 8.60602 13.2971 8.90351 12.9642C9.201 12.6312 9.34975 12.1969 9.34975 11.6612C9.34975 11.2559 9.27183 10.9229 9.116 10.6623C8.96017 10.4017 8.7406 10.1918 8.45727 10.0326C8.18811 9.85885 7.86229 9.72132 7.4798 9.61998C7.04064 9.47521 6.56607 9.35215 6.05609 9.25081C5.56027 9.14948 5.06445 9.03366 4.56863 8.90337C4.08697 8.77307 3.63365 8.60659 3.20866 8.40391C2.85451 8.25914 2.50743 8.08541 2.16744 7.88274C1.84162 7.66558 1.55829 7.41223 1.31746 7.12269C1.07664 6.81868 0.87831 6.46399 0.72248 6.05863C0.580818 5.65328 0.509986 5.19001 0.509986 4.66884C0.509986 3.97394 0.616233 3.38038 0.828728 2.88817C1.05539 2.39595 1.3458 1.98335 1.69995 1.65038C2.06828 1.31741 2.48618 1.05682 2.95367 0.868622C3.43532 0.665943 3.93823 0.521173 4.46238 0.43431C4.98653 0.347448 5.49652 0.304017 5.99234 0.304017C6.89898 0.304017 7.73479 0.448787 8.49977 0.738327C9.26475 1.02787 9.89515 1.49113 10.391 2.12812C10.901 2.75063 11.1772 3.57582 11.2197 4.60369H9.03101C8.97434 4.05357 8.79018 3.61925 8.47852 3.30076C8.18103 2.96779 7.80562 2.73616 7.3523 2.60586C6.89898 2.46109 6.41733 2.38871 5.90734 2.38871C5.53902 2.38871 5.17069 2.4249 4.80237 2.49729C4.43405 2.55519 4.09406 2.67101 3.7824 2.84473C3.48491 3.00398 3.24408 3.22114 3.05992 3.4962C2.88992 3.77126 2.80492 4.11147 2.80492 4.51683C2.80492 4.86428 2.87576 5.17553 3.01742 5.4506C3.15908 5.71118 3.35741 5.93558 3.6124 6.12378C3.88156 6.2975 4.17905 6.44951 4.50488 6.5798C5.09986 6.81144 5.75151 6.9924 6.45983 7.12269C7.16814 7.23851 7.83395 7.40499 8.45727 7.62215C8.91059 7.76692 9.33558 7.94788 9.73224 8.16504C10.1289 8.36772 10.4689 8.62106 10.7522 8.92508C11.0355 9.21462 11.2551 9.56207 11.4109 9.96743C11.5668 10.3583 11.6447 10.8143 11.6447 11.3355C11.6447 12.1897 11.4959 12.9207 11.1984 13.5288C10.901 14.1223 10.4972 14.6001 9.98723 14.962C9.47724 15.3239 8.88934 15.5917 8.22353 15.7655C7.55771 15.9247 6.86356 16.0043 6.14108 16.0043Z"
                            fill="black" />
                        <path
                            d="M14.1367 15.8306V0.477742H16.3254V9.14224H18.1316L21.2765 4.38654H23.7414L20.0653 9.83713L24.0602 15.8306H21.489L18.3016 11.1184H16.3254V15.8306H14.1367Z"
                            fill="black" />
                        <path
                            d="M25.4669 15.8306V4.38654H27.6556V15.8306H25.4669ZM26.5507 2.86645C26.1257 2.86645 25.7786 2.73616 25.5094 2.47557C25.2544 2.20051 25.1269 1.85306 25.1269 1.43322C25.1269 1.01339 25.2615 0.673181 25.5307 0.412595C25.7998 0.137532 26.1398 0 26.5507 0C26.9331 0 27.2661 0.137532 27.5494 0.412595C27.8469 0.673181 27.9956 1.01339 27.9956 1.43322C27.9956 1.85306 27.854 2.20051 27.5706 2.47557C27.3015 2.73616 26.9615 2.86645 26.5507 2.86645Z"
                            fill="black" />
                        <path d="M30.3643 15.8306V0.477742H32.553V15.8306H30.3643Z" fill="black" />
                        <path d="M35.2616 15.8306V0.477742H37.4503V15.8306H35.2616Z" fill="black" />
                        <path
                            d="M46.5762 16.0043C45.1596 16.0043 43.998 15.7148 43.0913 15.1357C42.1989 14.5422 41.5401 13.7315 41.1151 12.7036C40.6902 11.6757 40.4777 10.4814 40.4777 9.12052V0.477742H42.7301V9.14224C42.7301 9.99638 42.8505 10.7854 43.0913 11.5092C43.3322 12.2331 43.7359 12.8122 44.3026 13.2465C44.8692 13.6808 45.6342 13.8979 46.5975 13.8979C47.575 13.8979 48.34 13.6808 48.8924 13.2465C49.4449 12.8122 49.8345 12.2331 50.0611 11.5092C50.2878 10.7854 50.4011 9.99638 50.4011 9.14224V0.477742H52.6748V9.12052C52.6748 10.4669 52.4623 11.6612 52.0373 12.7036C51.6124 13.7315 50.9536 14.5422 50.0611 15.1357C49.1687 15.7148 48.007 16.0043 46.5762 16.0043Z"
                            fill="black" />
                        <path
                            d="M55.681 20V4.38654H57.7634L57.8697 6.0152C58.2663 5.42164 58.7834 4.98009 59.4209 4.69055C60.0583 4.38654 60.7525 4.23453 61.5033 4.23453C62.6224 4.23453 63.5432 4.49511 64.2657 5.01629C64.9882 5.52298 65.5265 6.22512 65.8807 7.12269C66.2348 8.00579 66.4119 8.99747 66.4119 10.0977C66.4119 11.2269 66.2278 12.2331 65.8594 13.1162C65.5053 13.9993 64.9528 14.6942 64.202 15.2009C63.4653 15.7076 62.5304 15.9609 61.3971 15.9609C60.8587 15.9609 60.3629 15.903 59.9096 15.7872C59.4704 15.6714 59.0809 15.5049 58.7409 15.2877C58.4151 15.0561 58.1246 14.7883 57.8697 14.4843V20H55.681ZM61.1208 13.9631C61.8858 13.9631 62.4949 13.7894 62.9483 13.4419C63.4016 13.08 63.7274 12.6095 63.9257 12.0304C64.1241 11.4513 64.2232 10.8216 64.2232 10.1412C64.2232 9.43178 64.117 8.78755 63.9045 8.20847C63.7062 7.61491 63.3733 7.14441 62.9058 6.79696C62.4524 6.43504 61.8433 6.25407 61.0783 6.25407C60.3842 6.25407 59.7963 6.43504 59.3146 6.79696C58.833 7.14441 58.4646 7.61491 58.2096 8.20847C57.9688 8.80203 57.8484 9.44625 57.8484 10.1412C57.8484 10.8505 57.9617 11.502 58.1884 12.0955C58.4292 12.6746 58.7905 13.1307 59.2721 13.4636C59.7679 13.7966 60.3842 13.9631 61.1208 13.9631Z"
                            fill="black" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M88 2.86019L79.5016 14.5581L76.2306 11.3746L72.0702 17.2206L70.0484 15.718L75.8877 7.51283L79.1791 10.7162L85.9928 1.33729L88 2.86019Z"
                            fill="#A042F0" />
                    </g>
                    <defs>
                        <clipPath id="clip0_328_52">
                            <rect width="88" height="20" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
                <!--Mode sombre-->
                <svg class="logo-dark" width="88" height="20" viewBox="0 0 88 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_328_49)">
                        <path
                            d="M6.14108 16.0043C5.16361 16.0043 4.2428 15.8596 3.37866 15.57C2.51452 15.2805 1.78495 14.81 1.18997 14.1585C0.594984 13.5071 0.198328 12.6457 0 11.5744H2.31619C2.50035 12.1824 2.79784 12.6602 3.20866 13.0076C3.61949 13.3406 4.09406 13.5722 4.63237 13.7025C5.18486 13.8328 5.73734 13.8979 6.28983 13.8979C6.81398 13.8979 7.30272 13.8256 7.75604 13.6808C8.22353 13.536 8.60602 13.2971 8.90351 12.9642C9.201 12.6312 9.34975 12.1969 9.34975 11.6612C9.34975 11.2559 9.27183 10.9229 9.116 10.6623C8.96017 10.4017 8.7406 10.1918 8.45727 10.0326C8.18811 9.85885 7.86229 9.72132 7.4798 9.61998C7.04064 9.47521 6.56607 9.35215 6.05609 9.25081C5.56027 9.14948 5.06445 9.03366 4.56863 8.90337C4.08697 8.77307 3.63365 8.60659 3.20866 8.40391C2.85451 8.25914 2.50743 8.08541 2.16744 7.88274C1.84162 7.66558 1.55829 7.41223 1.31746 7.12269C1.07664 6.81868 0.87831 6.46399 0.72248 6.05863C0.580818 5.65328 0.509986 5.19001 0.509986 4.66884C0.509986 3.97394 0.616233 3.38038 0.828728 2.88817C1.05539 2.39595 1.3458 1.98335 1.69995 1.65038C2.06828 1.31741 2.48618 1.05682 2.95367 0.868622C3.43532 0.665943 3.93823 0.521173 4.46238 0.43431C4.98653 0.347448 5.49652 0.304017 5.99234 0.304017C6.89898 0.304017 7.73479 0.448787 8.49977 0.738327C9.26475 1.02787 9.89515 1.49113 10.391 2.12812C10.901 2.75063 11.1772 3.57582 11.2197 4.60369H9.03101C8.97434 4.05357 8.79018 3.61925 8.47852 3.30076C8.18103 2.96779 7.80562 2.73616 7.3523 2.60586C6.89898 2.46109 6.41733 2.38871 5.90734 2.38871C5.53902 2.38871 5.17069 2.4249 4.80237 2.49729C4.43405 2.55519 4.09406 2.67101 3.7824 2.84473C3.48491 3.00398 3.24408 3.22114 3.05992 3.4962C2.88992 3.77126 2.80492 4.11147 2.80492 4.51683C2.80492 4.86428 2.87576 5.17553 3.01742 5.4506C3.15908 5.71118 3.35741 5.93558 3.6124 6.12378C3.88156 6.2975 4.17905 6.44951 4.50488 6.5798C5.09986 6.81144 5.75151 6.9924 6.45983 7.12269C7.16814 7.23851 7.83395 7.40499 8.45727 7.62215C8.91059 7.76692 9.33558 7.94788 9.73224 8.16504C10.1289 8.36772 10.4689 8.62106 10.7522 8.92508C11.0355 9.21462 11.2551 9.56207 11.4109 9.96743C11.5668 10.3583 11.6447 10.8143 11.6447 11.3355C11.6447 12.1897 11.4959 12.9207 11.1984 13.5288C10.901 14.1223 10.4972 14.6001 9.98723 14.962C9.47724 15.3239 8.88934 15.5917 8.22353 15.7655C7.55771 15.9247 6.86356 16.0043 6.14108 16.0043Z"
                            fill="white" />
                        <path
                            d="M14.1367 15.8306V0.477742H16.3254V9.14224H18.1316L21.2765 4.38654H23.7414L20.0653 9.83713L24.0602 15.8306H21.489L18.3016 11.1184H16.3254V15.8306H14.1367Z"
                            fill="white" />
                        <path
                            d="M25.4669 15.8306V4.38654H27.6556V15.8306H25.4669ZM26.5507 2.86645C26.1257 2.86645 25.7786 2.73616 25.5094 2.47557C25.2544 2.20051 25.1269 1.85306 25.1269 1.43322C25.1269 1.01339 25.2615 0.673181 25.5307 0.412595C25.7998 0.137532 26.1398 0 26.5507 0C26.9331 0 27.2661 0.137532 27.5494 0.412595C27.8469 0.673181 27.9956 1.01339 27.9956 1.43322C27.9956 1.85306 27.854 2.20051 27.5706 2.47557C27.3015 2.73616 26.9615 2.86645 26.5507 2.86645Z"
                            fill="white" />
                        <path d="M30.3643 15.8306V0.477742H32.553V15.8306H30.3643Z" fill="white" />
                        <path d="M35.2616 15.8306V0.477742H37.4503V15.8306H35.2616Z" fill="white" />
                        <path
                            d="M46.5762 16.0043C45.1596 16.0043 43.998 15.7148 43.0913 15.1357C42.1989 14.5422 41.5401 13.7315 41.1151 12.7036C40.6902 11.6757 40.4777 10.4814 40.4777 9.12052V0.477742H42.7301V9.14224C42.7301 9.99638 42.8505 10.7854 43.0913 11.5092C43.3322 12.2331 43.7359 12.8122 44.3026 13.2465C44.8692 13.6808 45.6342 13.8979 46.5975 13.8979C47.575 13.8979 48.34 13.6808 48.8924 13.2465C49.4449 12.8122 49.8345 12.2331 50.0611 11.5092C50.2878 10.7854 50.4011 9.99638 50.4011 9.14224V0.477742H52.6748V9.12052C52.6748 10.4669 52.4623 11.6612 52.0373 12.7036C51.6124 13.7315 50.9536 14.5422 50.0611 15.1357C49.1687 15.7148 48.007 16.0043 46.5762 16.0043Z"
                            fill="white" />
                        <path
                            d="M55.681 20V4.38654H57.7634L57.8697 6.0152C58.2663 5.42164 58.7834 4.98009 59.4209 4.69055C60.0583 4.38654 60.7525 4.23453 61.5033 4.23453C62.6224 4.23453 63.5432 4.49511 64.2657 5.01629C64.9882 5.52298 65.5265 6.22512 65.8807 7.12269C66.2348 8.00579 66.4119 8.99747 66.4119 10.0977C66.4119 11.2269 66.2278 12.2331 65.8594 13.1162C65.5053 13.9993 64.9528 14.6942 64.202 15.2009C63.4653 15.7076 62.5304 15.9609 61.3971 15.9609C60.8587 15.9609 60.3629 15.903 59.9096 15.7872C59.4704 15.6714 59.0809 15.5049 58.7409 15.2877C58.4151 15.0561 58.1246 14.7883 57.8697 14.4843V20H55.681ZM61.1208 13.9631C61.8858 13.9631 62.4949 13.7894 62.9483 13.4419C63.4016 13.08 63.7274 12.6095 63.9257 12.0304C64.1241 11.4513 64.2232 10.8216 64.2232 10.1412C64.2232 9.43178 64.117 8.78755 63.9045 8.20847C63.7062 7.61491 63.3733 7.14441 62.9058 6.79696C62.4524 6.43504 61.8433 6.25407 61.0783 6.25407C60.3842 6.25407 59.7963 6.43504 59.3146 6.79696C58.833 7.14441 58.4646 7.61491 58.2096 8.20847C57.9688 8.80203 57.8484 9.44625 57.8484 10.1412C57.8484 10.8505 57.9617 11.502 58.1884 12.0955C58.4292 12.6746 58.7905 13.1307 59.2721 13.4636C59.7679 13.7966 60.3842 13.9631 61.1208 13.9631Z"
                            fill="white" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M88 2.86019L79.5016 14.5581L76.2306 11.3746L72.0702 17.2206L70.0484 15.718L75.8877 7.51283L79.1791 10.7162L85.9928 1.33729L88 2.86019Z"
                            fill="#A042F0" />
                    </g>
                    <defs>
                        <clipPath id="clip0_328_49">
                            <rect width="88" height="20" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
            </div>
            <ul>
                <li><a href="../"> Accueil </a> </li>
                <li><a href="formations.php"> Formations </a> </li>
                <li><a href="categories.php"> Catégories </a> </li>
            </ul>
            <?php if (IsConnected()): ?>
                <a href="profile.php">
                    <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar"
                        style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                </a>
            <?php else: ?>
                <a href="connection.php">
                    <img src="../assets/images/user.svg" alt="Se connecter">
                </a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="details">
        <section class="hero">
            <div class="image-container">
                <?php if (!empty($formation['illustration_url'])): ?>
                    <img src="<?php echo htmlspecialchars($formation['illustration_url']); ?>"
                        alt="Illustration du cours <?php echo htmlspecialchars($formation['nom']); ?>">
                <?php else: ?>
                    <div class="no-image">Pas d'image</div>
                <?php endif; ?>
            </div>

            <div class="title-container">
                <h1><?php echo htmlspecialchars($formation['nom']); ?></h1>
                <div class="author-info">
                    <span>Par
                        <?php echo htmlspecialchars($formation['prenom'] . ' ' . $formation['nom_prof']); ?></span>
                </div>
                <div>
                    <span
                        class="course-category"><?php echo htmlspecialchars($formation['categorie_nom'] ?? 'Non catégorisé'); ?></span>
                </div>
            </div>
        </section>

        <section class="description">
            <p><?php echo htmlspecialchars($formation['description']); ?></p>
        </section>

        <section class="info">
            <ul>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v10l4.24 4.24" />
                        <circle cx="12" cy="12" r="10" />
                    </svg>
                    <span><?php echo $nb_chapitres; ?> chapitre(s)</span>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                    </svg>
                    <span><?php echo $formation['likes']; ?> J'aime</span>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-eye-icon lucide-eye">
                        <path
                            d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <span><?php echo $formation['vues']; ?> vues</span>
                </li>
                <li>
            </ul>
        </section>

        <section class="course-actions">
            <?php if (!$is_inscrit && $nb_chapitres > 0): ?>
                <form method="POST">
                    <?php if (IsConnected()): ?>
                        <button type="submit" name="inscription" class="enroll-button">S'inscrire au cours</button>
                    <?php else: ?>
                        <a href="connection.php" class="enroll-button">Se connecter pour s'inscrire</a>
                    <?php endif; ?>
                </form>
            <?php elseif ($is_inscrit && $nb_chapitres > 0): ?>
                <a href="course_viewer.php?id=<?php echo $id_cours; ?>" class="view-course-button">Consulter le cours</a>
            <?php elseif ($nb_chapitres == 0): ?>
                <div>
                    <p>Ce cours ne contient pas encore de chapitres.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
    </footer>
</body>

</html>