-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : mer. 23 avr. 2025 à 18:02
-- Version du serveur : 8.0.40
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `skillup2`
--

-- --------------------------------------------------------

--
-- Structure de la table `ApiLogs`
--

CREATE TABLE `ApiLogs` (
  `id` int NOT NULL,
  `ip` varchar(45) NOT NULL,
  `succes` tinyint(1) NOT NULL DEFAULT '0',
  `date_heure` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Categories`
--

CREATE TABLE `Categories` (
  `id` int NOT NULL,
  `nom` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `Categories`
--

INSERT INTO `Categories` (`id`, `nom`, `description`, `date_creation`) VALUES
(1, 'Développement Web', 'Apprenez à créer des sites et applications web avec HTML, CSS, JavaScript, et des frameworks modernes.', '2025-04-23 20:01:05'),
(2, 'Cybersécurité', 'Comprenez les principes de sécurité informatique, les menaces et la protection des systèmes.', '2025-04-23 20:01:05'),
(3, 'Intelligence Artificielle', 'Explorez le machine learning, le deep learning et les applications de l’IA.', '2025-04-23 20:01:05'),
(4, 'Réseaux Informatiques', 'Étudiez les protocoles, l’architecture réseau, et la communication entre machines.', '2025-04-23 20:01:05'),
(5, 'Systèmes d’Exploitation', 'Analysez le fonctionnement des systèmes comme Windows, Linux, et macOS.', '2025-04-23 20:01:05'),
(6, 'Base de Données', 'Apprenez à concevoir, interroger et administrer des bases de données relationnelles ou NoSQL.', '2025-04-23 20:01:05'),
(7, 'Développement Mobile', 'Créez des applications mobiles natives ou hybrides pour Android et iOS.', '2025-04-23 20:01:05'),
(8, 'Programmation Orientée Objet', 'Maîtrisez les concepts fondamentaux de la POO avec des langages comme Java, C# ou Python.', '2025-04-23 20:01:05'),
(9, 'Architecture Logicielle', 'Concevez des logiciels robustes et maintenables avec des modèles d’architecture.', '2025-04-23 20:01:05'),
(10, 'Cloud Computing', 'Découvrez les services cloud comme AWS, Azure ou GCP, et le déploiement d’applications à l’échelle.', '2025-04-23 20:01:05'),
(11, 'DevOps', 'Automatisez le déploiement, améliorez l’intégration continue et gérez l’infrastructure efficacement.', '2025-04-23 20:01:05'),
(12, 'Algorithmique', 'Étudiez les structures de données et les algorithmes fondamentaux pour résoudre des problèmes complexes.', '2025-04-23 20:01:05'),
(13, 'Informatique Théorique', 'Approfondissez les bases mathématiques et logiques de l’informatique.', '2025-04-23 20:01:05'),
(14, 'UX/UI Design', 'Apprenez à concevoir des interfaces utilisateurs intuitives et attrayantes.', '2025-04-23 20:01:05'),
(15, 'Robotique', 'Initiez-vous à la programmation et au contrôle de robots autonomes.', '2025-04-23 20:01:05');

-- --------------------------------------------------------

--
-- Structure de la table `Chapitres`
--

CREATE TABLE `Chapitres` (
  `id` int NOT NULL,
  `titre` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `fichier_url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cours_id` int DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Cours`
--

CREATE TABLE `Cours` (
  `id` int NOT NULL,
  `nom` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `illustration_url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `categorie_id` int DEFAULT NULL,
  `prof_id` int DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `date_update` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `DemandeProf`
--

CREATE TABLE `DemandeProf` (
  `id` int NOT NULL,
  `id_utilisateur` int DEFAULT NULL,
  `presentation` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Favoris`
--

CREATE TABLE `Favoris` (
  `id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `cours_id` int NOT NULL,
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Images`
--

CREATE TABLE `Images` (
  `id` int NOT NULL,
  `id_chapitre` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Inscriptions`
--

CREATE TABLE `Inscriptions` (
  `id` int NOT NULL,
  `etudiant_id` int DEFAULT NULL,
  `cours_id` int DEFAULT NULL,
  `date_inscription` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `KeyTable`
--

CREATE TABLE `KeyTable` (
  `key_id` int NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `token` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Utilisateurs`
--

CREATE TABLE `Utilisateurs` (
  `id` int NOT NULL,
  `prenom` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `nom` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `e_mail` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `mot_de_passe` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `avatar_url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `role` enum('professeur','etudiant') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'etudiant',
  `date_creation` datetime DEFAULT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `key_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `Utilisateurs`
--

INSERT INTO `Utilisateurs` (`id`, `prenom`, `nom`, `e_mail`, `mot_de_passe`, `avatar_url`, `role`, `date_creation`, `admin`, `key_id`) VALUES
(5, 'admin', NULL, 'admin@skillup.com', '$2y$10$Ik.Rg.BWGfle4Vx4ZGbtHuY8fw6eAXQcttYfezurbq9CG97QLkQ3m', NULL, 'etudiant', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `Vues`
--

CREATE TABLE `Vues` (
  `id` int NOT NULL,
  `utilisateur_id` int DEFAULT NULL,
  `cours_id` int NOT NULL,
  `date_vue` datetime DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `ApiLogs`
--
ALTER TABLE `ApiLogs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `Categories`
--
ALTER TABLE `Categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `Chapitres`
--
ALTER TABLE `Chapitres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chapitres_contenu_dans_cours` (`cours_id`);

--
-- Index pour la table `Cours`
--
ALTER TABLE `Cours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cours_appartient_categorie` (`categorie_id`),
  ADD KEY `idx_cours_cree_par_prof` (`prof_id`);

--
-- Index pour la table `DemandeProf`
--
ALTER TABLE `DemandeProf`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `Favoris`
--
ALTER TABLE `Favoris`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favori` (`utilisateur_id`,`cours_id`),
  ADD KEY `idx_favoris_utilisateur` (`utilisateur_id`),
  ADD KEY `idx_favoris_cours` (`cours_id`);

--
-- Index pour la table `Images`
--
ALTER TABLE `Images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_chapitre` (`id_chapitre`);

--
-- Index pour la table `Inscriptions`
--
ALTER TABLE `Inscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inscriptions_etudiant` (`etudiant_id`),
  ADD KEY `idx_inscriptions_concerne_cours` (`cours_id`);

--
-- Index pour la table `KeyTable`
--
ALTER TABLE `KeyTable`
  ADD PRIMARY KEY (`key_id`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `Utilisateurs`
--
ALTER TABLE `Utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `fk_key_id` (`key_id`);

--
-- Index pour la table `Vues`
--
ALTER TABLE `Vues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vues_utilisateur` (`utilisateur_id`),
  ADD KEY `idx_vues_cours` (`cours_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `ApiLogs`
--
ALTER TABLE `ApiLogs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Categories`
--
ALTER TABLE `Categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `Chapitres`
--
ALTER TABLE `Chapitres`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Cours`
--
ALTER TABLE `Cours`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `DemandeProf`
--
ALTER TABLE `DemandeProf`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Favoris`
--
ALTER TABLE `Favoris`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Images`
--
ALTER TABLE `Images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Inscriptions`
--
ALTER TABLE `Inscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `KeyTable`
--
ALTER TABLE `KeyTable`
  MODIFY `key_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `Utilisateurs`
--
ALTER TABLE `Utilisateurs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `Vues`
--
ALTER TABLE `Vues`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Chapitres`
--
ALTER TABLE `Chapitres`
  ADD CONSTRAINT `cle_etrangere_chapitres_contenu_dans_cours` FOREIGN KEY (`cours_id`) REFERENCES `Cours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Cours`
--
ALTER TABLE `Cours`
  ADD CONSTRAINT `cle_etrangere_cours_appartient_categorie` FOREIGN KEY (`categorie_id`) REFERENCES `Categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `cle_etrangere_cours_cree_par_prof` FOREIGN KEY (`prof_id`) REFERENCES `Utilisateurs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `DemandeProf`
--
ALTER TABLE `DemandeProf`
  ADD CONSTRAINT `demandeprof_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `Utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Favoris`
--
ALTER TABLE `Favoris`
  ADD CONSTRAINT `cle_etrangere_favoris_cours` FOREIGN KEY (`cours_id`) REFERENCES `Cours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cle_etrangere_favoris_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `Utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Images`
--
ALTER TABLE `Images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`id_chapitre`) REFERENCES `Chapitres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Inscriptions`
--
ALTER TABLE `Inscriptions`
  ADD CONSTRAINT `cle_etrangere_inscriptions_concerne_cours` FOREIGN KEY (`cours_id`) REFERENCES `Cours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cle_etrangere_inscriptions_etudiant` FOREIGN KEY (`etudiant_id`) REFERENCES `Utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Utilisateurs`
--
ALTER TABLE `Utilisateurs`
  ADD CONSTRAINT `fk_key_id` FOREIGN KEY (`key_id`) REFERENCES `KeyTable` (`key_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `Vues`
--
ALTER TABLE `Vues`
  ADD CONSTRAINT `cle_etrangere_vues_cours` FOREIGN KEY (`cours_id`) REFERENCES `Cours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cle_etrangere_vues_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `Utilisateurs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
