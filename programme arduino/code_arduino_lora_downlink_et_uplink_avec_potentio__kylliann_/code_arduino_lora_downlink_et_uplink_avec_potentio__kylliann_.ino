#include <TheThingsNetwork.h>
#include <Arduino.h>

// Définition des identifiants TTN
const char *appEui = "0004A30B00216C4C";
const char *appKey = "bf767f6bdd1ed0b4d0e68822be9b4d2d";

#define loraSerial Serial1
#define debugSerial Serial
#define freqPlan TTN_FP_EU868

TheThingsNetwork ttn(loraSerial, debugSerial, freqPlan);
bool sendData = true;
bool dataSent = false;
bool oldSendData = false;

// Pins des potentiomètres
const int potPin1 = A0;  // Potentiomètre 1
const int potPin2 = A5;  // Potentiomètre 2

// Downlink callback
void messageCallback(const uint8_t *payload, size_t size, port_t port) {
  debugSerial.print("Callback activé sur fPort ");
  debugSerial.println(port);

  if (size > 0) {
    debugSerial.print("Payload reçu: ");
    debugSerial.println(payload[0], HEX);

    if (payload[0] == 0x01) {
      sendData = true;
      debugSerial.println("Envoi des uplinks réactivé !");
    } else if (payload[0] == 0x00) {
      sendData = false;
      debugSerial.println("Envoi des uplinks désactivé !");
    } else {
      debugSerial.println("Commande inconnue reçue, pas d'action.");
    }

    debugSerial.print("sendData est maintenant : ");
    debugSerial.println(sendData ? "true" : "false");
  }
}

// Envoi du payload avec les valeurs des potentiomètres
void sendDataToTTN() {
  // Lire les valeurs des potentiomètres
  int potValue1 = analogRead(potPin1);
  int potValue2 = analogRead(potPin2);

  // Convertir les valeurs analogiques en voltage (0V à 5V)
  float voltage1 = potValue1 * (5.0 / 1023.0);
  float voltage2 = potValue2 * (5.0 / 1023.0);

  // Afficher les valeurs des potentiomètres et leur conversion en volts dans le moniteur série
  debugSerial.println("");
  debugSerial.print("Valeur du Potentiomètre 1: ");
  debugSerial.println(potValue1);
  debugSerial.print("Tension du Potentiomètre 1: ");
  debugSerial.println(voltage1, 3);  // Afficher avec 3 décimales
  
  debugSerial.print("Valeur du Potentiomètre 2: ");
  debugSerial.println(potValue2);code 
  debugSerial.print("Tension du Potentiomètre 2: ");
  debugSerial.println(voltage2, 3);  // Afficher avec 3 décimales


  // Convertir les valeurs analogiques en un format adapté pour l'envoi
  byte payload[] = { 
    (byte)(potValue1 >> 8), (byte)(potValue1 & 0xFF),  // Valeur du potentiomètre 1 (2 octets)
    (byte)(potValue2 >> 8), (byte)(potValue2 & 0xFF)   // Valeur du potentiomètre 2 (2 octets)
  };

  const port_t dataPort = 1;

  // Envoyer le payload avec les valeurs des potentiomètres
  int result = ttn.sendBytes(payload, sizeof(payload), dataPort);
  debugSerial.println("Données envoyées avec valeurs des potentiomètres.");
}


// Envoi du ping wakeup (0xAA) sur fPort 2
void sendPingUplink() {
  byte pingPayload[] = { 0xAA };
  const port_t pingPort = 2;
  debugSerial.println();
  int result = ttn.sendBytes(pingPayload, sizeof(pingPayload), pingPort);
}

void setup() {
  loraSerial.begin(57600);
  debugSerial.begin(9600);

  unsigned long startTime = millis();
  while (!debugSerial && millis() - startTime < 10000);

  debugSerial.println("-- STATUS");
  ttn.showStatus();

  debugSerial.println("-- JOIN");
  if (!ttn.join(appEui, appKey)) {
    debugSerial.println("Échec de la connexion TTN !");
  } else {
    debugSerial.println("Connecté à TTN !");
  }

  ttn.onMessage(messageCallback);
  sendData = true;
}

void loop() {
  // Afficher changement d'état
  if (oldSendData != sendData) {
    debugSerial.print("sendData mis à : ");
    debugSerial.println(sendData ? "true" : "false");
    oldSendData = sendData;
  }

  // Si autorisé, envoyer les vraies données (potentiomètres)
  if (sendData) {
    sendDataToTTN();
  }
  // Sinon, envoyer le ping AA sur fPort 2
  else {
    sendPingUplink();
  }

  delay(60000); // Attendre 10 secondes avant prochain cycle
}
