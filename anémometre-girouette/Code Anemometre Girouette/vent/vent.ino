/*
  Wind - Simplified Wind Instrument (No NMEA, Wi-Fi, MQTT)
  Based on original work by Tom K
  anémomçtre à coupelles Interrupteur magnétique
  Ultimeter WSF10030P
  arduino mega 2560 + pullup
  M. QUERE
*/
#include <TheThingsNetwork.h>  // Inclut la bibliothèque pour interagir avec The Things Network (TTN)

//Partie transmission loRa
// Définir l'AppEUI et l'AppKey pour l'application 
const char *appEui = "0004A30B00202229";  // Identifiant de l'application (AppEUI)
const char *appKey = "2DC1092ACA77D91CE66B212D544AF8C4";  // Clé d'application (AppKey)
#define loraSerial Serial1  // Déclare le port série utilisé pour la communication LoRa (Serial1)
#define debugSerial Serial  // Déclare le port série utilisé pour les messages de débogage (Serial)
#define freqPlan TTN_FP_EU868  // Définit le plan de fréquence à utiliser (EU868 pour l'Europe)
TheThingsNetwork ttn(loraSerial, debugSerial, freqPlan);// Créer un objet pour la connexion à The Things Network (TTN)

#define VERSION "Wind v3c - RQ"

#define windSpeedPin 2 // jaune
#define windDirPin 3 // vert
#define windSpeedINT 0 // INT0
#define windDirINT 1   // INT1
boolean debugMode = true;  // Mettre à false pour désactiver le debug
int LED = 13;

const unsigned long DEBOUNCE = 10000ul; // temps minimum entre deux interruptionS pour éviter les rebonds parasites
const unsigned long TIMEOUT = 1500000ul; // temps maximal (en microsecondes) entre deux impulsions du capteur de vent. ( absence de vent donc arrêt anémomètre)
const float filterGain = 0.25;

volatile unsigned long speedPulse = 0ul;
volatile unsigned long dirPulse = 0ul;
volatile unsigned long speedTime = 0ul;
volatile unsigned long directionTime = 0ul;
volatile boolean newData = false;

volatile int knotsOut = 0;  // valeur variable en fonction des conditions de vitesse exprimée en noeud
uint16_t dirOut = 0; // valeur variable pour l'orientation du vent

void setup() {
  
    loraSerial.begin(57600);  // Initialise la communication série pour LoRa avec un débit de 57600 bauds
    debugSerial.begin(9600);  // Initialise la communication série pour le débogage avec un débit de 9600 bauds
    // Attendre un maximum de 10 secondes pour l'initialisation du moniteur série
    while (!debugSerial && millis() < 10000);
  
    debugSerial.println("-- STATUS");  // Affiche l'état de la connexion
    ttn.showStatus();  // Affiche les informations de statut de la connexion TTN
  
    debugSerial.println("-- JOIN");  // Demande la connexion au réseau LoRaWAN
    ttn.join(appEui, appKey);  // Se connecte au réseau LoRa avec l'AppEUI et l'AppKey
    
  
    pinMode(LED, OUTPUT);
    Serial.begin(38400); //liaison série 
    Serial.println(VERSION); // version du programme
    pinMode(windSpeedPin, INPUT);
    attachInterrupt(digitalPinToInterrupt(windSpeedPin), readWindSpeed, FALLING);
    pinMode(windDirPin, INPUT);
    attachInterrupt(digitalPinToInterrupt(windDirPin), readWindDir, FALLING);
    interrupts();
}
// on vérifie que le délai est suffisant et que le signal est à l'état bas
// calcul du temps écoulé
void readWindSpeed() {
    if (((micros() - speedPulse) > DEBOUNCE) && (digitalRead(windSpeedPin) == LOW)) {
        speedTime = micros() - speedPulse;
        if (dirPulse - speedPulse >= 0) directionTime = dirPulse - speedPulse;
        newData = true; // drapeau pour indiquer une nouvelle donnée
        speedPulse = micros();
    }
}

void readWindDir() {
    if (((micros() - dirPulse) > DEBOUNCE) && (digitalRead(windDirPin) == LOW)) {
        dirPulse = micros();
    }
}

void calcWindSpeedAndDir() {
    byte payload[4];
    unsigned long dirPulse_, speedPulse_;
    unsigned long speedTime_;
    unsigned long directionTime_;
    long windDirection = 0l, rps = 0l, knots = 0l;
    

    noInterrupts(); // désactiver les interruptions pour éviter toute modification des valeurs avant copie
    dirPulse_ = dirPulse;
    speedPulse_ = speedPulse;
    speedTime_ = speedTime;
    directionTime_ = directionTime;
    interrupts(); // réactiver les implusions

    if (micros() - speedPulse_ > TIMEOUT) speedTime_ = 0ul; // vérification du temps entre les implusions de vitesse et le temps d'attente

    if (speedTime_ > 0) {
        rps = 1000000 / speedTime_; // Calcul de la révolution par seconde 
        // utilisation des équations fournies par le constructeur pour le modèle WSF10030P
        if (rps >= 0.010 && rps < 3.229) {
           knots = (rps * rps * -0.1095) + (2.9318 * rps) - 0.1412;
        } else if (rps >= 3.23 && rps < 54.362) {
           knots = (rps * rps * 0.0052) + (2.19 * rps) + 1.1;
        } else if (rps >= 5436 && rps < 6633) {
           knots = (rps * rps * 0.11) - (9.5685 * rps) + 329.87;
        }

          knotsOut = knots; // enregistre la vitesse en noeud
          // si la direction est valide et que l'implusion de vent est avant celle  de la vitesse, le programme effectue le calcul
          // 

        if (directionTime_ < speedTime_) {
            windDirection = ((directionTime_ * 360) / speedTime_) % 360;
            int delta = ((int)windDirection - dirOut);
            if (delta < -180) delta += 360;
            else if (delta > 180) delta -= 360;
            dirOut = (dirOut + (int)(round(filterGain * delta))) % 360;
            if (dirOut < 0) dirOut += 360;
        }
    } else {
        knotsOut = 0;
    }
    if (debugMode) {
        printDebugData();
    }
    Serial.print("rps: ");Serial.println(rps);
    uint16_t kmh = (knotsOut) * 1.852; // Conversion des noeuds en km/h
    Serial.print("Wind Speed: ");
    Serial.print(kmh);
    Serial.print(" km/h, Direction: ");
    Serial.print(dirOut);
    Serial.println(" degrees");
    payload[0] = highByte(kmh);
    payload[1] = lowByte(kmh);
    payload[2] = highByte(dirOut);
    payload[3] = lowByte(dirOut);
    
    ttn.sendBytes(payload, sizeof(payload));  // Envoie le tableau de données sur le réseau LoRa
}
void printDebugData() {
    Serial.println("=== DEBUG DATA ===");
    Serial.print("Last Speed Pulse (us): "); Serial.println(speedPulse);
    Serial.print("Last Direction Pulse (us): "); Serial.println(dirPulse);
    Serial.print("Time Between Speed Pulses (us): "); Serial.println(speedTime);
    Serial.print("Time Between Direction Pulses (us): "); Serial.println(directionTime);
    Serial.print("Calculated Wind Speed (knots): "); Serial.println(knotsOut);
    Serial.print("Calculated Wind Direction (degrees): "); Serial.println(dirOut);
    Serial.println("===================");
}
void loop() {
    digitalWrite(LED, !digitalRead(LED));
    if (newData) {
        calcWindSpeedAndDir();        
        newData = false; // drapeau
    }
    delay(500);
}

 
