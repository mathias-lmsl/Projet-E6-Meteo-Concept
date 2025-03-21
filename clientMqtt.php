<?php
require 'phpmqtt/phpMQTT.php'; // Inclure phpMQTT
use Bluerhinos\phpMQTT;

require 'phpmailer/src/PHPMailer.php'; //Inclure PHPMailer
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//-------------------------------------------------PROTOTYPE-----------------------------------------------------------
function hex2SigDem($hex);

//-------------------------------------------------CONNEXION BDD-------------------------------------------------------
// Informations de connexion à la base de données
$username = "mqtt"; // Nom d'utilisateur de la BDD
$password = "Mqtt"; // Mot de passe (à adapter selon la config)

// Création de la connexion avec PDO (PHP Data Objects)
try {
    $pdo = new PDO("mysql:host=192.168.1.205;dbname=meteoconcept;charset=utf8mb4", $username, $password);
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}

//-----------------------------------------------CONNEXION BROCKER MQTT-----------------------------------------------
// Configuration MQTT
$brocker = '192.168.1.163'; // Adresse du serveur MQTT
$port = 1883; // Port MQTT
$clientId = 'phpClient'; // ID unique du client MQTT

// Connexion au serveur MQTT
$mqtt = new phpMQTT($brocker, $port, $clientId);

if (!$mqtt->connect(true, NULL)) {
    exit("Impossible de se connecter au serveur MQTT\n");
}

//-----------------------------------------------ABONNEMENT AU TOPIC------------------------------------------------------
// Chemin du topic et affectation tableau associatif
$topic['application/863b91c6-a4ad-47b9-9100-66ff4580605f/device/+/event/up'] = ["qos" => 0, "function" => "donnee"];

// S'abonner au topic MQTT
$mqtt->subscribe($topic, 0);

// Boucle d'écoute
while ($mqtt->proc()) {}

$mqtt->close();

// Fonction de traitement des messages
function donnee($topic, $message) {
    global $pdo;
    // Traitement du message JSON
    $data = json_decode($message, true);
    if ($data) {
        //valeur hexa qui été convertit en base64 par le chipstarck que je reconvertit en hexa
        $decodeData = bin2hex(base64_decode($data['data']));
        $temperature = substr(decodeData, 0, 8)
        $humidite = substr(decodeData, 8, 8)
        $vitVent = substr(decodeData, 16, 8)
        $dirVent = substr(decodeData, 24, 8)
	    $decimalSigned = hex2SigDem($temperature);
    }

        "SELECT capteur.IdCapteur FROM capteur, possede, carte WHERE carte.devEUI = possede.devEUI 
        AND possede.IdCapteur = capteur.IdCapteur
        AND carte.devEUI = '".$devEUI."'
        AND capteur.Grandeur = 'Temperature'";

    //----------------------------------------ENVOIE EMAIL EN FONCTION DES SEUILS---------------------------------------------
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'projet.meteoconcept@gmail.com';
    $mail->Password = 'nlvp sgay fmsz holb'; // mot de passe d'application Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Requête SQL pour récupérer les infos du capteur
    $req = $pdo->query("SELECT `SeuilMin`, `SeuilMax`, `Nom`, `Unite` FROM `capteur` WHERE `IdCapteur` = '$capteurId'");
    $infos = $req->fetch();
    
        $seuilMin = $infos['SeuilMin'];
        $seuilMax = $infos['SeuilMax'];
        $NomCapt = $infos['Nom'];
        $UniteCapt = $infos['Unite'];

        if($mesure < $seuilMin || $mesure > $seuilMax){
        if ($mesure < $seuilMin) {
            $messageAlerte = "Seuil minimum du capteur $capteurId ($NomCapt) dépassé ! <br>Valeur mesurée : $mesure$UniteCapt";
        } elseif ($mesure > $seuilMax) {
            $messageAlerte = "Seuil maximum du capteur $capteurId ($NomCapt) dépassé !<br>Valeur mesurée : $mesure$UniteCapt";
        }

        try {
            $mail->setFrom('projet.meteoconcept@gmail.com', 'Alerte Capteur');
            $mail->addAddress('benoitaubouin@gmail.com');
            $mail->isHTML(true);
            $mail->Subject = "Alerte Capteur";
            $mail->Body = "$messageAlerte";
            $mail->send();
        } catch (Exception $e) {
            echo "Erreur d'envoi : {$mail->ErrorInfo}";
        }
    }

    //--------------------------------------------INSERTION VALEUR DANS LA BDD------------------------------------------------
    $pdo->query("INSERT INTO `mesure`(`Horodatage`, `Valeur`, `IdCapteur`) VALUES (NOW(), '$mesure', '$capteurId')");
}

//Fonction pour complémenter à 2 une valeur hexadecimal
function Hex2SigDem($hex){
//Convertir l'hexadécimal en un nombre entier
    $decimal = hexdec($hex);

    // Si le nombre dépasse la moitié, c'est un négatif en complément à deux
    if ($decimal >= 32768) {
        $decimal -= 65536 ; // Soustraction du max pour le signe
    }

    return $decimal;
}
?>
