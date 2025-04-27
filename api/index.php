<?php
    session_start();
    require_once("../include/connectdb.php"); 
    require_once("../include/sessionManager.php");
    require_once("../include/secureCheck.php");
    if(!IsConnected()){
        header('Location:  ../pages/connection.php');
        exit();
    }
    $titre = SITE_NAME . ' - API';
    $db =  connectDB();
    $sql = 'SELECT Utilisateurs.role, KeyTable.token FROM sessions INNER JOIN Utilisateurs ON Utilisateurs.id = sessions.user_id INNER JOIN KeyTable ON KeyTable.key_id = Utilisateurs.key_id WHERE sessions.token = :token';
    $request = $db->prepare($sql);
    $request->bindParam(':token', $_COOKIE['user_token']);
    $request->execute();
    $result = $request->fetch(PDO::FETCH_ASSOC);
    if($result['role'] != 'professeur'){
        header('Location:  ../pages/connection.php');
        exit();
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $newtoken = generateToken();
        while(CheckToken($newtoken)){
            $newtoken = generateToken();
        }
        $now = date("Y-m-d H:i:s");
        $sql = 'UPDATE Keytable SET Keytable.token = :ntoken, Keytable.date_creation = :now WHERE Keytable.token = :otoken';
        $request = $db->prepare($sql);
        $request->bindParam(':ntoken', $newtoken);
        $request->bindParam(':now', $now );
        $request->bindParam(':otoken', $result['token']);
        $request->execute();

        $result['token'] = $newtoken;
    }
    setcookie("token_api", $result['token'], time() + 3600);
    $_COOKIE['token_api'] = $result['token'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titre; ?></title>
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/jossua.css">
</head>
<body id="api">
    <header>
        <div class="title">
            <h1>API</h1>
            <h1 class="gradient">Skillup</h1>
        </div>
        <p>Documentation pour l'API Skillup. Explorez les endpoints, pour intégrez notre API à vos projets, ou créer des cours depuis une autre interface.</p>
    </header>
    <section class="token">
        <div class="head">
            <div class="inline">
                <svg id="purple" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-key-icon lucide-key"><path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"/><path d="m21 2-9.6 9.6"/><circle cx="7.5" cy="15.5" r="5.5"/></svg>
                <h2>Ma clé d'accès</h2>
            </div>
            <p>Cette clé est indispensable pour accéder à notre API. Ne la partagez jamais.</p>
        </div>
        <div class="contentapi">
            <div class="inline">
                <button onclick="copy(token)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy-icon lucide-copy"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                </button>
                <form method="POST">
                    <button type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rotate-ccw-icon lucide-rotate-ccw"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    </button>
                </form>
                <p><?php echo htmlspecialchars($result['token'])?></p>
            </div>
            <p class="warning">⚠️ Si vous pensez que des tiers ont eu accès à votre clé, vous pouvez la régénérer ici.</p>
        </div>
    </section>
    <div class="inline title">
        <svg id="purple" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-icon lucide-file"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
        <h2>Documentation</h2>
    </div>
    <section class="endpoint">
        <div class="head">
            <div class="inline">
                <svg id="purple" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-code-icon lucide-code"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                <h2>GetMd.php</h2>
            </div>
            <p>Récupérer le markdown correspondant à un chapitre</p>
            <div class="postincon">POST</div>
        </div>
        <div class="contentapi">
            <label for="url">Url</label>
            <p class="url" id="url">https://skillup.great-site.net/api/GetMd.php</p>
            <label for="t1">Paramètres</label>
            <table id="t1">
                <thead>
                    <tr>
                        <td>Nom</td>
                        <td>Type</td>
                        <td>Requis</td>
                        <td>Description</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>token</td>
                        <td>String</td>
                        <td>Oui</td>
                        <td>Votre clé d'api</td>
                    </tr>
                    <tr>
                        <td>chapter</td>
                        <td>Int</td>
                        <td>Oui</td>
                        <td>Id du chapitre cible</td>
                    </tr>
                    <tr>
                        <td>cours</td>
                        <td>Int</td>
                        <td>Oui</td>
                        <td>Id du cours cible</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="endpoint">
        <div class="head">
            <div class="inline">
                <svg id="purple" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-code-icon lucide-code"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                <h2>SaveMd.php</h2>
            </div>
            <p>Sauvegarder le markdown correspondant à un chapitre</p>
            <div class="postincon">POST</div>
        </div>
        <div class="contentapi">
            <label for="url">Url</label>
            <p class="url" id="url">https://skillup.great-site.net/api/SaveMd.php</p>
            <label for="t1">Paramètres</label>
            <table id="t1">
                <thead>
                    <tr>
                        <td>Nom</td>
                        <td>Type</td>
                        <td>Requis</td>
                        <td>Description</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>token</td>
                        <td>String</td>
                        <td>Oui</td>
                        <td>Votre clé d'api</td>
                    </tr>
                    <tr>
                        <td>chapter</td>
                        <td>Int</td>
                        <td>Oui</td>
                        <td>Id du chapitre cible</td>
                    </tr>
                    <tr>
                        <td>cours</td>
                        <td>Int</td>
                        <td>Oui</td>
                        <td>Id du cours cible</td>
                    </tr>
                    <tr>
                        <td>markdown</td>
                        <td>String</td>
                        <td>Oui</td>
                        <td>Le contenu markdown à Sauvegarder</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="endpoint">
        <div class="head">
            <div class="inline">
                <svg id="purple" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-code-icon lucide-code"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                <h2>UploadImage.php</h2>
            </div>
            <p>Enregistre une image et retourne son chemin</p>
            <div class="postincon">POST</div>
        </div>
        <div class="contentapi">
            <label for="url">Url</label>
            <p class="url" id="url">https://skillup.great-site.net/api/UploadImage.php</p>
            <label for="t1">Paramètres</label>
            <table id="t1">
                <thead>
                    <tr>
                        <td>Nom</td>
                        <td>Type</td>
                        <td>Requis</td>
                        <td>Description</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>token</td>
                        <td>String</td>
                        <td>Oui</td>
                        <td>Votre clé d'api</td>
                    </tr>
                    <tr>
                        <td>chapter</td>
                        <td>Int</td>
                        <td>Oui</td>
                        <td>Id du chapitre cible</td>
                    </tr>
                    <tr>
                        <td>cours</td>
                        <td>Int</td>
                        <td>Oui</td>
                        <td>Id du cours cible</td>
                    </tr>
                    <tr>
                        <td>image</td>
                        <td>String</td>
                        <td>Oui</td>
                        <td>Image au format Base64</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="endpoint">
        <div class="head">
            <div class="inline">
                <svg id="purple" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-code-icon lucide-code"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                <h2>NewCours.php</h2>
            </div>
            <p>Crée votre nouveau compte</p>
            <div class="postincon">POST</div>
        </div>
        <div class="contentapi">
            <label for="url">Url</label>
            <p class="url" id="url">https://skillup.great-site.net/api/NewCours.php</p>
            <label for="t1">Paramètres</label>
            <table id="t1">
                <thead>
                    <tr>
                        <td>Nom</td>
                        <td>Type</td>
                        <td>Requis</td>
                        <td>Description</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>token</td>
                        <td>String</td>
                        <td>Oui</td>
                        <td>Votre clé d'api</td>
                    </tr>
                    <tr>
                        <td>name</td>
                        <td>String</td>
                        <td>Oui</td>
                        <td>Nom du cours</td>
                    </tr>
                    <tr>
                        <td>prompt</td>
                        <td>String</td>
                        <td>Oui</td>
                        <td>Le prompt qui servir à générer l'image de votre cours</td>
                    </tr>
                    <tr>
                        <td>categorie</td>
                        <td>String</td>
                        <td>Oui</td>
                        <td>la catégorie du cours (voir catégorie dans la documentation)</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>String</td>
                        <td>Non</td>
                        <td>la description du cours</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="endpoint">
        <div class="head">
            <div class="inline">
                <svg id="purple" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-code-icon lucide-code"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                <h2>NewChapter.php</h2>
            </div>
            <p>Crée un nouveau chapitre dans votre cours</p>
            <div class="postincon">POST</div>
        </div>
        <div class="contentapi">
            <label for="url">Url</label>
            <p class="url" id="url">https://skillup.great-site.net/api/NewChapter.php</p>
            <label for="t1">Paramètres</label>
            <table id="t1">
                <thead>
                    <tr>
                        <td>Nom</td>
                        <td>Type</td>
                        <td>Requis</td>
                        <td>Description</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>token</td>
                        <td>String</td>
                        <td>Oui</td>
                        <td>Votre clé d'api</td>
                    </tr>
                    <tr>
                        <td>name</td>
                        <td>String</td>
                        <td>Oui</td>
                        <td>Nom du chapitre</td>
                    </tr>
                    <tr>
                        <td>cours_id</td>
                        <td>Int</td>
                        <td>Oui</td>
                        <td>l'id du cours associé au nouveau chapitre</td>
                    </tr>
                    <tr>
                        <td>md</td>
                        <td>String</td>
                        <td>Non</td>
                        <td>le contenu du chapitre (markdown)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="endpoint">
        <div class="head">
            <div class="inline">
                <svg id="purple" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-code2-icon lucide-file-code-2"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m5 12-3 3 3 3"/><path d="m9 18 3-3-3-3"/></svg>
                <h2>Exemple de requête</h2>
            </div>
            <p>Comment envoyer une requête à l'api</p>
        </div>
        <div class="contentapi">
        <label id="lcurl">Exemple requête Curl</label>
        <pre><code>
            curl -X POST https://skillup.great-site.net/api/CleanImg.php \
            -H "Content-Type: application/x-www-form-urlencoded" \
            -d "token=9676563a2a765e126537c7e374f5SQQscfd7814c27806basa5a4ebf3050935df62e1de4813" \
            -d "chapter=13" \
            -d "cours=12" \
            --data-urlencode "markdown=# Chapitre 1 :"
        </code></pre>
        </div>
    </section>
    <section class="info">
        <div class="head">
            <div class="inline">
                <svg id="purple" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                <h2>Autres Infos</h2>
            </div>
        </div>
        <div class="contentapi">
            <h3>Valeurs possibles pour catégorie :</h3>
            <p class="enum">Développement Web / Cybersécurité / Intelligence Artificielle / Réseaux Informatiques / Systèmes d’Exploitation / Base de Données / Développement Mobile / Programmation Orientée Objet / Architecture Logicielle / Cloud Computing / DevOps / Algorithmique / Informatique Théorique / UX/UI Design / Robotique</p>
            <h3>Contact:</h3>
            <p class="enum">contact.fictif@api.skillup.fr</p>
        </div>
    </section>
</body>
<script src="../<?php echo JS_PATH; ?>/jossua.js"></script>
<script>
    var token = getCookie('token_api');
</script>
</html>