<?php 

function getAvatar(){
    global $new_avatar;
    if (!isset($_POST['prompt']) || empty($_POST['prompt'])) {
        return null;
    }

    $prompt = $_POST['prompt'];

    $url = "https://remyweb.fr/emoji.php";
    $data = ['prompt' => $prompt];
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']); 
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        echo "<h1>Erreur cURL : " . curl_error($curl) . "</h1>";
        curl_close($curl);
        return null;
    }
    curl_close($curl);
    $decodedResponse = json_decode($response, true);

    if (isset($decodedResponse['imagePath'])) {
        $imagePath = htmlspecialchars($decodedResponse['imagePath']);
        $new_avatar = 'https://remyweb.fr/' . $imagePath;
        $_SESSION['new_avatar'] = $new_avatar;
        return $new_avatar;
    } else {
        echo "<h1>Erreur : Réponse invalide</h1>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        return null;
    }
}
function GetProfId($token){
    $db =  connectDB();
    $sql = 'SELECT Utilisateurs.id FROM Utilisateurs INNER JOIN KeyTable ON KeyTable.key_id = Utilisateurs.key_id WHERE KeyTable.token = :token';
    $request = $db->prepare($sql);
    $request->bindParam(':token', $token);
    $request->execute();
    $user = $request->fetch(PDO::FETCH_ASSOC);

    if($user['id']){
        return $user['id'];
    }else{
        return false;
    }
}
?>