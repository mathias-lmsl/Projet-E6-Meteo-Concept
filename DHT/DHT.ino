
#include <TheThingsNetwork.h>  // Inclut la bibliothèque pour interagir avec The Things Network (TTN)

// Installer d'abord "DHT sensor library" via le gestionnaire de bibliothèques
#include <DHT.h>  // Inclut la bibliothèque pour les capteurs DHT (température et humidité)

// Définir l'AppEUI et l'AppKey pour l'application
const char *appEui = "0004A30B001E6712";  // Identifiant de l'application (AppEUI)
const char *appKey = "3BE8C099BA7541CD3F2B90443FE2BA20";  // Clé d'application (AppKey)

#define loraSerial Serial1  // Déclare le port série utilisé pour la communication LoRa (Serial1)
#define debugSerial Serial  // Déclare le port série utilisé pour les messages de débogage (Serial)

#define freqPlan TTN_FP_EU868  // Définit le plan de fréquence à utiliser (EU868 pour l'Europe)

#define DHTPIN 2  // Définit la broche du capteur DHT (ici la broche 2)


// Choisir le modèle du capteur DHT
//#define DHTTYPE DHT11  // Décommenter pour utiliser le capteur DHT11
//#define DHTTYPE DHT21  // Décommenter pour utiliser le capteur DHT21
#define DHTTYPE DHT22  // Utilisation du capteur DHT22 (décommenté)

// Créer un objet pour le capteur DHT en spécifiant la broche et le modèle
DHT dht(DHTPIN, DHTTYPE);

// Créer un objet pour la connexion à The Things Network (TTN)
TheThingsNetwork ttn(loraSerial, debugSerial, freqPlan);

void setup()
{
  loraSerial.begin(57600);  // Initialise la communication série pour LoRa avec un débit de 57600 bauds
  debugSerial.begin(9600);  // Initialise la communication série pour le débogage avec un débit de 9600 bauds

  // Attendre un maximum de 10 secondes pour l'initialisation du moniteur série
  while (!debugSerial && millis() < 10000)
    ;

  debugSerial.println("-- STATUS");  // Affiche l'état de la connexion
  ttn.showStatus();  // Affiche les informations de statut de la connexion TTN

  debugSerial.println("-- JOIN");  // Demande la connexion au réseau LoRaWAN
  ttn.join(appEui, appKey);  // Se connecte au réseau LoRa avec l'AppEUI et l'AppKey

  dht.begin();  // Initialise le capteur DHT
}

void loop()
{
  debugSerial.println("-- LOOP");  // Indique que nous sommes dans la boucle principale

  // Lire les valeurs du capteur et les multiplier par 100 pour avoir 2 décimales
  uint16_t humidity = dht.readHumidity(false) * 100;  // Lire l'humidité (en pourcentage) et la convertir en entier sur 16 bits

  // false: température en Celsius (par défaut)
  // true: température en Fahrenheit
  uint16_t temperature = dht.readTemperature(false) * 100;  // Lire la température (en °C) et la convertir en entier sur 16 bits

  // Séparer les valeurs des 16 bits en 2 octets de 8 bits pour chaque mesure
  byte payload[4];  // Tableau pour stocker les données à envoyer
  payload[0] = highByte(temperature);  // Byte supérieur de la température
  payload[1] = lowByte(temperature);  // Byte inférieur de la température
  payload[2] = highByte(humidity);  // Byte supérieur de l'humidité
  payload[3] = lowByte(humidity);  // Byte inférieur de l'humidité

  // Afficher les valeurs de température et d'humidité sur le moniteur série pour débogage
  debugSerial.print("Temperature: ");
  debugSerial.println(temperature / 100.0);  // Affiche la température en °C (avec 2 décimales)
  debugSerial.print("Humidity: ");
  debugSerial.println(humidity / 100.0);  // Affiche l'humidité en % (avec 2 décimales)

  // Envoyer les données sous forme de bytes via LoRa
  ttn.sendBytes(payload, sizeof(payload));  // Envoie le tableau de données sur le réseau LoRa

  delay(20000);  // Attendre 20 secondes avant de refaire une lecture et un envoi
}
