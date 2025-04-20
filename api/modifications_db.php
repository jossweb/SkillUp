<?php 
    require_once("../include/connectdb.php");
    $db =  connectDB();
    
    try{
        $sql = 'CREATE TABLE KeyTable (key_id INT PRIMARY KEY AUTO_INCREMENT, date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, actif BOOLEAN NOT NULL DEFAULT TRUE, token VARCHAR(100) NOT NULL);';
        $request = $db->prepare($sql);
        $request->execute();

    }catch(Exception $e){
        echo "1 : ". $e->getMessage();
    }
    try{
        $sql = 'CREATE TABLE ApiLogs ( id INT PRIMARY KEY AUTO_INCREMENT, ip VARCHAR(45) NOT NULL, succes BOOLEAN NOT NULL DEFAULT FALSE, date_heure DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP );';
        $request = $db->prepare($sql);
        $request->execute();

    }catch(Exception $e){
        echo "2 : ". $e->getMessage();

    }
    try{
        $sql = 'ALTER TABLE Utilisateurs ADD COLUMN admin BOOLEAN NOT NULL DEFAULT FALSE;';
        $request = $db->prepare($sql);
        $request->execute();

    }catch(Exception $e){
        echo "3 : ". $e->getMessage();

    }
    try{

        $sql = 'CREATE TABLE DemandeProf (id INT PRIMARY KEY AUTO_INCREMENT, id_utilisateur INT, presentation VARCHAR(250), FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id) ON DELETE CASCADE ON UPDATE CASCADE);';
        $request = $db->prepare($sql);
        $request->execute();

    }catch(Exception $e){
        echo "4 : ". $e->getMessage();

    }
    try{
        $sql = 'INSERT INTO Utilisateurs (Utilisateurs.prenom, Utilisateurs.e_mail, Utilisateurs.mot_de_passe, Utilisateurs.admin) VALUES ("admin", "admin@skillup.com", "$2y$10$Ik.Rg.BWGfle4Vx4ZGbtHuY8fw6eAXQcttYfezurbq9CG97QLkQ3m", true);';
        $request = $db->prepare($sql);
        $request->execute();

    }catch(Exception $e){
        echo "5 : ". $e->getMessage();

    }
?>