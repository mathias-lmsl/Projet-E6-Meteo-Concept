<?php
// Importation de la bibliothèque phpMQTT pour gérer MQTT
require 'phpMQTT.php';

// Importation de PHPMailer pour l'envoi des emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Charge PHPMailer avec Composer

// ==========================
// 🔹 CONFIGURATION MQTT
// ==========================
$broker = 'mqtt.example.com'; // Adresse du broker MQTT
$port = 1883; // Port standard MQTT
$clientId = 'Client_' . uniqid(); // ID unique du client MQTT
$topic = 'capteurs/#'; // Abonnement à tous les capteurs (wildcard #)

// ==========================
// 🔹 CONNEXION À LA BASE DE DONNÉES (MariaDB)
// ==========================
$host = 'localhost'; // Hôte de la base de données
$dbname = 'mon_systeme'; // Nom de la base de données
$user = 'root'; // Identifiant
$password = 'password'; // Mot de passe

try {
    // Connexion avec PDO en UTF-8
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// ==========================
// 🔹 FONCTION POUR RÉCUPÉRER LES SEUILS D'UN CAPTEUR
// ==========================
function getSeuilCapteur($pdo, $capteur_id) {
    // Requête SQL pour récupérer les seuils et l'email du capteur
    $stmt = $pdo->prepare("SELECT seuil_min, seuil_max, email_alert FROM seuils_capteurs WHERE capteur_id = :capteur_id");
    $stmt->execute(['capteur_id' => $capteur_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ==========================
// 🔹 FONCTION D'ENVOI D'EMAIL D'ALERTE
// ==========================
function envoyerEmail($email, $capteur_id, $valeur, $seuil, $type) {
    $mail = new PHPMailer(true);
    try {
        // Configuration du serveur SMTP de Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; // Remplace par ton email Gmail
        $mail->Password = 'your-app-password';   // Remplace par ton mot de passe d'application Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Destinataires
        $mail->setFrom('your-email@gmail.com', 'Alerte Capteur');
        $mail->addAddress($email); // Email du destinataire

        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = "Alerte : $type pour $capteur_id";
        $mail->Body = "
            Alerte $type détectée sur le capteur <b>$capteur_id</b> !<br>
            Valeur détectée : <b>$valeur</b><br>
            Seuil défini : <b>$seuil</b><br><br>
            Merci de vérifier le système immédiatement.
        ";

        // Envoi de l'email
        $mail->send();
        echo "Email envoyé à $email : $capteur_id - Valeur = $valeur | Seuil = $seuil ($type)\n";
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}\n";
    }
}

// ==========================
// 🔹 CONNEXION AU BROKER MQTT
// ==========================
$mqtt = new Bluerhinos\phpMQTT($broker, $port, $clientId);
if (!$mqtt->connect()) {
    die("Échec de connexion au broker MQTT\n");
}

// Abonnement aux messages des capteurs sur le topic "capteurs/#"
$mqtt->subscribe($topic, 0, function ($topic, $message) use ($pdo) {
    echo "Message reçu sur [$topic] : $message\n";

    // Extraction de l'ID du capteur depuis le topic MQTT
    $topic_parts = explode("/", $topic);
    $capteur_id = end($topic_parts); // Exemple : 'capteurs/temp_capteur1' => 'temp_capteur1'

    // Récupération des seuils et de l'email du capteur depuis la base de données
    $seuilData = getSeuilCapteur($pdo, $capteur_id);

    if ($seuilData) {
        // Conversion des seuils en nombres flottants
        $seuil_min = (float) $seuilData['seuil_min'];
        $seuil_max = (float) $seuilData['seuil_max'];
        $email = $seuilData['email_alert'];
        $valeur = (float) $message;

        // Vérification si la valeur est hors des seuils
        if ($valeur < $seuil_min) {
            echo "ALERTE : Valeur trop basse pour $capteur_id ($valeur < $seuil_min)\n";
            envoyerEmail($email, $capteur_id, $valeur, $seuil_min, "Seuil Minimum Atteint");
        } elseif ($valeur > $seuil_max) {
            echo "ALERTE : Valeur trop haute pour $capteur_id ($valeur > $seuil_max)\n";
            envoyerEmail($email, $capteur_id, $valeur, $seuil_max, "Seuil Maximum Dépassé");
        }
    } else {
        echo "Aucun seuil défini pour le capteur $capteur_id\n";
    }
});

// ==========================
// 🔹 LANCEMENT DE L'ÉCOUTE EN CONTINU
// ==========================
while ($mqtt->proc()) {
    // Boucle infinie pour rester à l'écoute des nouveaux messages MQTT
}
$mqtt->close();
