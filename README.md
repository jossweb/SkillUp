![Skill](assets/images/banner.svg)

# SkillUp

## Qu'est-ce que SkillUp ?
SkillUp est une plateforme d'apprentissage en ligne développée dans le cadre des cours de web et de bases de données en première année de licence d'informatique à l'Université de Tours. Notre objectif est de permettre aux utilisateurs de se former ou de trouver des informations concernant de multiples disciplines. Bien que le projet ait vocation à couvrir divers domaines, nous nous sommes concentrés sur l'informatique pour cette première version.

## Structure du projet
- **`/api`** : Endpoints pour l'API REST
- **`/assets`** : Ressources statiques (CSS, JS, images, SQL)
- **`/include`** : Fichiers d'inclusion PHP (configuration, connexion BD, et autre utils..)
- **`/pages`** : Pages du site (profil, connexion, formations, etc.)

## Installation
1. Clonez ce dépôt : `git clone https://github.com/jossweb/SkillUp.git`
2. Importez le fichier SQL dans votre base de données : `assets/sql/skillup.sql`
3. Configurez les paramètres de connexion dans `include/config.php` (Rendez-vous sur `include/test.php` pour tester votre configuration)
4. Déployez sur un serveur PHP compatible (version 8.0+ recommandée)