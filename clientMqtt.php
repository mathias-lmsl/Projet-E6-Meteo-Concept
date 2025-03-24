<?php
require 'phpmqtt/phpMQTT.php'; // Inclure phpMQTT
use Bluerhinos\phpMQTT;

require 'phpmailer/src/PHPMailer.php'; //Inclure PHPMailer
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//-------------------------------------------------PROTOTYPE-----------------------------------------------------------
function donnee($topic, $message);
function hex2SigDem($hex);
function recupId($grandeur, $devEui);
function traitementData($mesure, $capteurId);

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
$topic = 'application/863b91c6-a4ad-47b9-9100-66ff4580605f/device/+/event/up';

// S'abonner au topic MQTT
$mqtt->subscribe($topic => ["qos" => 0, "function" => "donnee"]);

// Boucle d'écoute
while ($mqtt->proc()) {}

$mqtt->close();

//-----------------------------------------------------FONCTIONS--------------------------------------------------------------
// Fonction de traitement des messages
// @param $topic : contient le chemin du topic
//	  $message : contient la donnée que le broker envoi
function donnee($topic, $message) {
	global $pdo; //ça sert ??

	
    	// Traitement du message JSON
    	$data = json_decode($message, true);
	if($data){
		// valeur hexa qui été convertit en base64 par le chipstarck que je reconvertit en hexa
       	 	$decodeData = bin2hex(base64_decode($data['data']));

		// je stocke la valeur mesuree pour chaque capteur
        	$temperature = (hex2SigDem(substr(decodeData, 0, 8)))/10;
		$humidite = (hex2SigDem(substr(decodeData, 8, 8)))/10;
        	$vitVent = (hex2SigDem(substr(decodeData, 16, 8)))/10;
        	$dirVent = (hex2SigDem(substr(decodeData, 24, 8)))/10;

		// je récupère les id des capteurs
		$idCaptTemp = recupId($temperature);
		$idCaptHum = recupId($humidite);
		$idCaptVit = recupId($vitVent);
		$idCaptDir = recupId($dirVent);

		// appel fonction pour traitement des données
		traitementData($temperature, $idCaptTemp);
		traitementData($humidite, $idCaptHum);
		traitementData($vitVent, $idCaptVit);
		traitementData($dirVent, $idCaptDir);

	}
}

// Fonction pour traiter les données, envoi du mail si besoin et insertion dans la BDD
// @param $mesure : la mesure envoyé en mqtt
//	  $capteurId : l'id du capteur lié a la mesure
function traitementData($mesure, $capteurId){
	// Envoi mail en fonction des seuils du capteur
    	// Requête SQL pour récupérer les infos du capteur
    	$req = $pdo->query("SELECT `SeuilMin`, `SeuilMax`, `Nom`, `Unite` FROM `capteur` WHERE `IdCapteur` = '$capteurId'");
    	$infos = $req->fetch();

        $seuilMin = $infos['SeuilMin'];
        $seuilMax = $infos['SeuilMax'];
        $NomCapt = $infos['Nom'];
        $UniteCapt = $infos['Unite'];

	//verification du depassment de seuil et envoi du mail
        if($mesure < $seuilMin || $mesure > $seuilMax){
        	if ($mesure < $seuilMin) {
            		$messageAlerte = "Seuil minimum du capteur $capteurId ($NomCapt) dépassé ! <br>Valeur mesurée : $mesure$UniteCapt";
        	} else {
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
	
	// Insertion dans la BDD
    	$pdo->query("INSERT INTO `mesure`(`Horodatage`, `Valeur`, `IdCapteur`) VALUES (NOW(), '$mesure', '$capteurId')");
}

// Fonction pour complémenter à 2 une valeur hexadecimal
// @param $hex : un chaine de caractères qui repsente une valeur hexa
// @return : la valeur en décimal
function hex2SigDem($hex){
//Convertir l'hexadécimal en un nombre entier
    $decimal = hexdec($hex);

    // Si le nombre dépasse la moitié, c'est un négatif en complément à deux
    if ($decimal >= 32768) {
        $decimal -= 65536 ; // Soustraction du max pour le signe
    }

    return $decimal;
}

// Fonction recupId pour récuperer l'id du capteur
// @param $grandeur : nom de la grandeur du capteur a recuperer
// 	  $devEui : le devEui pour identifier la carte
// @return : l'id du capteur
function recupId($grandeur, $devEui){
	$req = $pdo->query("SELECT capteur.IdCapteur FROM capteur, possede, carte WHERE carte.devEUI = possede.devEUI 
        AND possede.IdCapteur = capteur.IdCapteur
        AND carte.devEUI = '$devEui'
        AND capteur.Grandeur = '$grandeur'";)

	return $req;
}
?>
