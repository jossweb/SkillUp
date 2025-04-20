![Skill](assets/images/banner.svg)

### Comment faire pour ajouter votre code
1. Faites un fork de votre code
2. Faites vos modifications
3. Quand tout fonctionne parfaitement faites une pull request sur ce dépots voir fiche sur le whatsapp 
4. Avec Rémy nous allons analyser votre code (DONC PAS DE GPT OU AUTRE IA)


### Configuration de la base de données
Pour configurer correctement la base de données du projet, suivez ces étapes:

1. Créez une base de données MySQL (nom par defaut:`skillup`)

2. Si vous souhaitez utiliser d'autres paramètres que ce présent dans le dans le fichier `include/config.php`, modifiez les constantes correspondantes dans le fichier:
   ```php
   define('DB_HOST', 'votre_hote');
   define('DB_PORT', votre_port);
   define('DB_USER', 'votre_utilisateur');
   define('DB_PASS', 'votre_mot_de_passe');
   define('DB_NAME', 'votre_bdd');
   ```
3. Assurez-vous que la connexion est ok en vous rendant sur : `include/test.php`