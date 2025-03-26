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
3. Assurez-vous que la connexion est ok en vous rendant sur : `include/config.php`

erDiagram
    UTILISATEURS ||--o{ INSCRIPTION : "s'inscrit_à"
    UTILISATEURS ||--o{ COURS : "crée"
    COURS ||--o{ CHAPITRE : "contient"
    COURS ||--o{ INSCRIPTION : "inscrit"
    COURS }|--o{ CATEGORIES : "appartient_à"
    
    UTILISATEURS {
        int id_utilisateur PK
        varchar(15) prenom
        varchar(40) nom
        varchar(255) e_mail
        varchar(255) mot_de_passe
        varchar(255) avatar_url
        enum role
        datetime date_creation
    }
    
    COURS {
        int id_cours PK
        varchar(255) nom
        varchar(255) illustration_url
        text description
        datetime date_creation
        datetime date_update
        int id_categorie FK
        int id_utilisateur FK
    }
    
    CHAPITRE {
        int id_chapitre PK
        varchar(50) titre
        varchar(255) fichier_url
        datetime date_creation
        int id_cours FK
    }
    
    CATEGORIES {
        int id_categorie PK
        varchar(255) nom
        text description
        datetime date_creation
    }
    
    INSCRIPTION {
        int id_inscription PK
        date date_inscription
        int id_cours FK
        int id_utilisateur FK
    }