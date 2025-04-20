<?php
require_once 'config.php';

function connectDB() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        if (DEBUG) {
            afficheErreur($e);
        } else {
            echo "<p>Une erreur est survenue lors de la connexion aux données.</p>";
        }
        exit;
    }
}

function afficheErreur($exception) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur !</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/style.css">

        <style>
            body {
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--color);
                background-color: var(--bg);
            }
            code {
                background-color: var(--bg);
                padding: 2px 5px;
                border-radius: 3px;
                font-family: monospace;
            }
            pre {
                background-color: var(--bg);
                padding: 15px;
                border-radius: 3px;
                overflow-x: auto;
            }
            #erreur {
                width: 75vw;
                margin: 40px auto;
                padding: 20px;
                background-color: var(--alt-bg);
                border-radius: var(--radius);
                border: 1px solid var(--border);
                border-left: 5px solid var(--skillup-pink);
                display: flex;
                gap: 25px;
                flex-direction: column;
                justify-content: center;
            }
        </style>
    </head>
    <body>
        <div id="erreur">
            <h2>Erreur de connexion à la base de données</h2>
            <p>Une erreur s'est produite lors de la tentative de connexion à la base de données.</p>
            <h2>Détails de l'erreur:</h2>
            <pre><?= htmlspecialchars($exception->getMessage()) ?></pre>
            <h2>Trace:</h2>
            <pre><?= htmlspecialchars($exception->getTraceAsString()) ?></pre>
            
        </div>
    </body>
    </html>
    <?php
}
?>