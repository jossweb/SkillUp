<?php
    require_once("../include/config.php"); 
    $titre = SITE_NAME . ' - Accueil';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/connection.css">
    <title>Document</title>
</head>
<body>
    <div class="background">
        <img src="../<?php echo IMG_PATH; ?>/skillup-logo.svg" alt="background" id="img-top"/>
        <img src="../<?php echo IMG_PATH; ?>/skillup-logo2.svg" alt="background" id="img-bottom"/>
    </div>
    <div class="content">
        <section class="form">
            <div class="toggle">
                <button id="login-btn" class="isSelected" onclick="toggleForm(false)">Connexion</button>
                <button onclick="toggleForm(true)" id="register-btn">Inscription</button>
            </div>
            <form id="login-form" class="isSelected">
                <label for="email">Email</label>
                <input type="text" id="email">
                <label for="password">Mot de passe</label>
                <input type="password" id="password"/>
                <button type="submit">Connexion</button>
                <a href="">mot de passe oublié</a>
            </form>
            <form id="register-form">
                <div class="mini-inputs">
                    <label for="name">Nom</label>
                    <input type="text" id="name"/>
                    <label for="firstName">Prénom</label>
                    <input type="text" id="firstName"/>
                <div>
                <label for="email">Email</label>
                <input type="text" id="email">
                <label for="password">Mot de passe</label>
                <input type="password" id="password"/>
                <label for="check-pass">Confirmation</label>
                <input type="password" id="check-pass"/>
                <button type="submit">Connexion</button>
            </form>
        </section>
    </div>
</body>
<script src="../<?php echo JS_PATH; ?>/forms.js"></script>
</html>