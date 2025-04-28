<?php
session_start(); 
require_once("../include/connectdb.php"); 
require_once("../include/sessionManager.php");
$titre = SITE_NAME . ' - connexion/inscription';
$db =  connectDB();//connect to db
$message = ""; // message success or error

if(IsConnected()){
    header('Location: profile.php');
    exit();
}
function registration() {
    global $db, $message;
    $name = $_POST['name'];
    $firstName = $_POST['firstName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmedPassword = $_POST['cPassword'];

    if(!empty($name) && !empty($firstName) && !empty($email) && !empty($password) && !empty($confirmedPassword)) {
        if (!(strlen($name) > 40)) {
            if(!(strlen($firstName) > 15)){
                if(!(strlen($email) > 255)){
                    if(!(strlen($password) > 255)){
                        if ($password == $confirmedPassword) {
                            $currentDateTime = date('Y-m-d H:i:s');
                            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                            $sql = "INSERT INTO Utilisateurs (nom, prenom, e_mail, mot_de_passe, date_creation) 
                                    VALUES (:nom, :prenom, :email, :mot_de_passe, :date_creation)";
                            $request = $db->prepare($sql);
                            $request->bindParam(':nom', $name);
                            $request->bindParam(':prenom', $firstName);
                            $request->bindParam(':email', $email);
                            $request->bindParam(':mot_de_passe', $hashedPassword);
                            $request->bindParam(':date_creation', $currentDateTime);
                            $request->execute();
                        } else {
                            $message = "<p>Les mots de passes ne correspondent pas</p>";
                        }
                    }
                    else{
                        $message = "<p>Le mot de passe ne peut contenir que 255 au maximum</p>";
                    }
                }
                else{
                    $message = "<p>Le nom ne peut contenir que 250 caractères au maximum</p>";
                }
            }
            else{
                $message = "<p>Le prénom ne peut contenir que 15 caractères au maximum</p>";
            }
    
        } else {
            $message = "<p>Le nom ne peut contenir que 40 caractères au maximum</p>";
        }
    }
    else{
        $message = "<p>Vous devez remplir tout les champs</p>";
    }
}

function connection() {
    global $db, $message;
    $email = $_POST['email'];
    $password = $_POST['password'];
    $sql = "SELECT id, nom, prenom, mot_de_passe FROM Utilisateurs WHERE e_mail = :email";
    $request = $db->prepare($sql);
    $request->bindParam(':email', $email);
    $request->execute();
    $result = $request->fetch(PDO::FETCH_ASSOC);

    if (($result) && password_verify($password, $result['mot_de_passe'])) {
        $token_seed = $result['id'] . (new DateTime())->format('Y-m-d H:i:s');
        $token = password_hash($token_seed, PASSWORD_DEFAULT);
    
        setcookie("user_token", $token, time() + 86400, "/"); 
        $sql_session = 'INSERT INTO sessions (user_id, token, expires_at) VALUES (:id, :token, :expiresDate)';
        $request_session = $db->prepare($sql_session);
        $request_session->bindParam(':id', $result['id']);
        $request_session->bindParam(':token', $token);
        $expiresDate = new DateTime();
        $expiresDate->modify('+1 day');
        $expiresDateFormatted = $expiresDate->format('Y-m-d H:i:s');
        $request_session->bindParam(':expiresDate', $expiresDateFormatted);
        $request_session->execute();
        header("Location: profile.php");
        exit;
    } else {
        $message = "<p>Email ou mot de passe incorrect !</p>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['connectionForm'])) { 
        connection();
    } elseif (isset($_POST['registrationForm'])) {
        registration();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/jossua.css">
    <title><?php echo $titre; ?></title>
</head>
<body id="connection">
<button onclick="location.href='../'" class="home">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house">
        <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/>
        <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
    </svg>
</button>
    <div class="content">
        <?php if ($message): ?>
                    <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <section class="form">
            <div class="toggle">
                <button id="login-btn" class="isSelected" onclick="toggleForm(false)">Connexion</button>
                <button onclick="toggleForm(true)" id="register-btn">Inscription</button>
            </div>
            <form id="login-form" class="isSelected" method="POST" action="connection.php">
                <label for="email">Email</label>
                <input type="email" id="email" maxlength="255" name="email" required>
                <label for="password">Mot de passe</label>
                <input type="password" id="password" maxlength="255" name="password" required/>
                <button type="submit" name="connectionForm">Connexion</button>
                
            </form>
            <form id="register-form" method="POST">
                <div class="mini-inputs">
                    <div class="input-group">
                        <label for="name">Nom</label>
                        <input type="text" class="little-input" id="name" name="name" maxlength="40" required/>
                    </div>
                    <div class="input-group">
                        <label for="firstName">Prénom</label>
                        <input type="text" class="little-input" name="firstName" maxlength="15" required/>
                    </div>
                </div>
                <label for="email">Email</label>
                <input type="email" id="email" maxlength="255" name="email" required>
                <label for="password" >Mot de passe</label>
                <input type="password" id="password" minlength="8" maxlength="255" name="password" required/>
                <label for="check-pass">Confirmation</label>
                <input type="password" id="check-pass" minlength="8" maxlength="255" name="cPassword" required/>
                <button type="submit" name="registrationForm">Inscription</button>
            </form>
        </section>
    </div>
</body>
<script src="../<?php echo JS_PATH; ?>/jossua.js"></script>
</html>