<?php //https://www.cloudamqp.com/docs/php_mqtt.html regarder subscriber
require 'phpmqtt/phpMQTT.php'; // Inclure phpMQTT
use Bluerhinos\phpMQTT;

require 'phpmailer/src/PHPMailer.php'; //Inculre PHPMailer
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//-------------------------------------------------CONNEXION BDD-------------------------------------------------------
// Informations de connexion à la base de données
$host = "192.168.1.205"; // Adresse du serveur MariaDB
$dbname = "meteoconcept"; // Nom de la base de données
$username = "mqtt"; // Nom d'utilisateur de la BDD
$password = "Mqtt"; // Mot de passe (à adapter selon la config)

// Création de la connexion avec PDO (PHP Data Objects)
try {
$pdo = new PDO(mysql:host = $host; dbname = $dbname; charset = utf8mb4, $username, $password, [
    PDO::ATTR_ERRMODE = > PDO::ERRMODE_EXCEPTION, // Active les erreurs SQL
        PDO::ATTR_DEFAULT_FETCH_MODE = > PDO::FETCH_ASSOC // Retourne les résultats sous forme de tableau associatif
]);
}
catch (PDOException $e) {
die("Erreur : ".$e->getMessage());
}

//-----------------------------------------------CONNEXION BROCKER MQTT-----------------------------------------------
// Configuration MQTT
$brocker = '192.168.1.163';     // Adresse du serveur MQTT
$port = 1883;              // Port MQTT
$clientId = 'phpClient';  // ID unique du client MQTT

// Connexion au serveur MQTT
$mqtt = new phpMQTT($brocker, $port, $clientId);

if (!$mqtt->connect(true, NULL)) {
exit("Impossible de se connecter au serveur MQTT\n");
}

//-----------------------------------------------ABONNEMENT AU TOPIC------------------------------------------------------
// Chemin du topic
$topic['application/863b91c6-a4ad-47b9-9100-66ff4580605f/device/0004a30b00216c4c/event/up'] = array("qos" = > 0, "function" = > "donnee");

// S'abonner au topic MQTT
$mqtt->subscribe($topic, 0); //donnee est une fonction callback

// Boucle d'écoute
while ($mqtt->proc()) {}

$mqtt->close();

// Fonction de traitement des messages
function donnee($topic, $message) {
	// Traitement du message JSON
	$data = json_decode($message, true);
	if ($data) {
	    // affichage dans l'invite de commande de la donnée
	    $data = floatval(hexdec($data['data']));
	}
}

//----------------------------------------ENVOIE EMAIL EN FONCTION DES SEUILS---------------------------------------------
// Requête SQL pour récupérer les seuils du capteur
$req = $pdo->query("SELECT `SeuilMin`, `SeuilMax` FROM `capteur` WHERE `IdCapteur` = '".$capteurId."';");
$seuils = $req->fetch(); // Récupération des résultats dans un tableau

//if ($seuils) { // Vérifie si des seuils existent pour ce capteur 
$seuilMin = $seuils['SeuilMin']; // Affectation des seuils dans des variables
$seuilMax = $seuils['SeuilMax'];

// Vérification si la valeur reçue dépasse le seuil MIN
if ($data < $seuilMin) {
// Envoi d'un email d'alerte
try {
    // Configuration SMTP Gmail (https://support.google.com/a/answer/176600 -> OPTION 2)
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'projet.meteoconcept@gmail.com'; // Remplacez par votre email Gmail
    $mail->Password = 'nlvp sgay fmsz holb'; // Utilisez un mot de passe d'application Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Configuration de l'email
    $mail->setFrom('projet.meteoconcept@gmail.com', 'Alerte Capteur');
    $mail->addAddress('projet.meteoconcept@gmail.com'); //Destinataire (peut être n'importe qui)
    $mail->isHTML(true);
    $mail->Subject = "Alerte Capteur";
    $mail->Body = "Seuil du capteur minimum".$capteurId." dépassé !\nLa valeur mesurée est de :".$data;

    //envoie de l'email
    $mail->send();
}
catch (Exception $e) {
    echo "Erreur d'envoi : {$mail->ErrorInfo}";
}

}
}
else if($data > $seuilMax){ // Vérification si la valeur reçue dépasse le seuil MAX
// Envoi d'un email d'alerte
try {
    // Configuration SMTP Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'projet.meteoconcept@gmail.com'; // Remplacez par votre email Gmail
    $mail->Password = 'nlvp sgay fmsz holb'; // Utilisez un mot de passe d'application Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Configuration de l'email
    $mail->setFrom('projet.meteoconcept@gmail.com', 'Alerte Capteur');
    $mail->addAddress('projet.meteoconcept@gmail.com'); //Destinataire (peut être n'importe qui)
    $mail->isHTML(true);
    $mail->Subject = "Alerte Capteur";
    $mail->Body = "Seuil maximum du capteur ".$capteurId." dépassé !\nLa valeur mesurée est de :".$data;

    //envoie de l'email
    $mail->send();
}
catch (Exception $e) {
    echo "Erreur d'envoi : {$mail->ErrorInfo}";
}
}

//--------------------------------------------INSERTION VALEUR DANS LA BDD------------------------------------------------
// Requête SQL pour inserer la mesure dans la BDD
$req = $pdo->query("INSERT INTO `mesure`(`Horodatage`,`Valeur`,`IdCapteur`) VALUES (NOW(),'".$data."','".$capteurId."');");
});
?>
