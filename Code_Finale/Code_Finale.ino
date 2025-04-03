#include <math.h>
#include <TheThingsNetwork.h>  // Inclut la bibliothèque pour interagir avec The Things Network (TTN)
#include <DHT.h>  // Inclut la bibliothèque pour les capteurs DHT (température et humidité)
#include <OneWire.h>

//********************************************************************************************************************
//***********************************************Version 1.1**********************************************************
//************Documentation available at : www.irrometer.com/200ss.html ******************************************
// Version 1.1 updated 7/21/2023 by Jeremy Sullivan, Irrometer Co Inc.*****************************************************
// Code tested on Arduino UNO R3
// Purpose of this code is to demonstrate valid WM reading code, circuitry and excitation using a voltage divider and "psuedo-ac" method
// This program uses a modified form of Dr. Clint Shock's 1998 calibration equation.
// Sensor to be energized by digital pin 11 or digital pin 5, alternating between HIGH and LOW states

//As a simplified example, this version reads one sensor only and assumes a default temperature of 24C.
//NOTE: the 0.09 excitation time may not be sufficient depending on circuit design, cable lengths, voltage, etc. Increase if necessary to get accurate readings, do not exceed 0.2
//NOTE: this code assumes a 10 bit ADC. If using 12 bit, replace the 1024 in the voltage conversions to 4096

                                  //Déclaration transmission loRa
                                  
// Définir l'AppEUI et l'AppKey pour l'application
const char *appEui = "0004A30B001E6712";  // Identifiant de l'application (AppEUI)
const char *appKey = "3BE8C099BA7541CD3F2B90443FE2BA20";  // Clé d'application (AppKey)
//const char *appEui = "0004A30B001E6712";
//const char *appKey = "f6ede1ce4a6d607d1e7c774d224e573f";

#define loraSerial Serial1  // Déclare le port série utilisé pour la communication LoRa (Serial1)
#define debugSerial Serial  // Déclare le port série utilisé pour les messages de débogage (Serial)
#define freqPlan TTN_FP_EU868  // Définit le plan de fréquence à utiliser (EU868 pour l'Europe)
TheThingsNetwork ttn(loraSerial, debugSerial, freqPlan);// Créer un objet pour la connexion à The Things Network (TTN)

                                  //Déclaration DHT22
                                  
#define DHTPIN 4  // Définit la broche du capteur DHT (ici la broche 2)
#define DHTTYPE DHT22  // Utilisation du capteur DHT22 
DHT dht(DHTPIN, DHTTYPE);// Créer un objet pour le capteur DHT en spécifiant la broche et le modèle
                                  
                                  //Déclaration Sonde hygrometrique
                                  
#define num_of_read 1 // number of iterations, each is actually two reads of the sensor (both directions)
const int Rx = 10000;  //fixed resistor attached in series to the sensor and ground...the same value repeated for all WM and Temp Sensor.
float default_TempC;
const long open_resistance = 35000; //check the open resistance value by replacing sensor with an open and replace the value here...this value might vary slightly with circuit components
const long short_resistance = 200; // similarly check short resistance by shorting the sensor terminals and replace the value here.
const long short_CB = 240, open_CB = 255 ;
const int SupplyV = 5; // Assuming 5V output for SupplyV, this can be measured and replaced with an exact value if required
const float cFactor = 1.1; //correction factor optional for adjusting curve, 1.1 recommended to match IRROMETER devices as well as CS CR1000
int i, j = 0;
uint16_t WM1_CB = 0;
float SenV10K = 0, SenVWM1 = 0, SenVWM2 = 0, ARead_A1 = 0, ARead_A2 = 0, WM_Resistance = 0, WM1_Resistance = 0 ;

                                  //Déclaration Sonde température
                                  
int DS18S20_Pin = 6; //DS18S20 Signal pin on digital 2
//Temperature chip i/o
OneWire ds(DS18S20_Pin);  // on digital pin 2

                                  //Déclaration Girouette-Anemometre

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
uint16_t kmh;
                              
void setup()
{
  // initialize serial communications at 9600 bps:
  
                                    //Initialisation du lora
  
  Serial.begin(9600);
  loraSerial.begin(57600);  // Initialise la communication série pour LoRa avec un débit de 57600 bauds
  debugSerial.begin(9600);  // Initialise la communication série pour le débogage avec un débit de 9600 bauds
  // Attendre un maximum de 10 secondes pour l'initialisation du moniteur série
  while (!debugSerial && millis() < 10000);

  debugSerial.println("-- STATUS");  // Affiche l'état de la connexion
  ttn.showStatus();  // Affiche les informations de statut de la connexion TTN

  debugSerial.println("-- JOIN");  // Demande la connexion au réseau LoRaWAN
  ttn.join(appEui, appKey);  // Se connecte au réseau LoRa avec l'AppEUI et l'AppKey

                                      //Initialisation du dht22
                                     
  dht.begin();  // Initialise le capteur DHT

                                      //Initialisation de la sonde
                                      
  // initialize the pins, 5 and 11 randomly chosen. In the voltage divider circuit example in figure 1(www.irrometer.com/200ss.html), pin 11 is the "Output Pin" and pin 5 is the "GND".
  // if the direction is reversed, the WM1_Resistance A and B formulas would have to be swapped.
  pinMode(5, OUTPUT);
  pinMode(11, OUTPUT);
  //set both low
  digitalWrite(5, LOW);
  digitalWrite(11, LOW);

  delay(100);   // time in milliseconds, wait 0.1 minute to make sure the OUTPUT is assigned
  
  //initialisation Girouette-anemometre
  pinMode(LED, OUTPUT);
    Serial.begin(38400); //liaison série 
    Serial.println(VERSION); // version du programme
    pinMode(windSpeedPin, INPUT);
    attachInterrupt(digitalPinToInterrupt(windSpeedPin), readWindSpeed, FALLING);
    pinMode(windDirPin, INPUT);
    attachInterrupt(digitalPinToInterrupt(windDirPin), readWindDir, FALLING);
    interrupts();
}

void loop()
{
  while (j == 0)
  {
    byte payload[8];  // Tableau pour stocker les données à envoyer
    //Partie sonde watermark
    if (newData) {
        calcWindSpeedAndDir();        
        newData = false; // drapeau
    }
    default_TempC = getTemp();
    WM1_Resistance = readWMsensor();
    WM1_CB = myCBvalue(WM1_Resistance, default_TempC, cFactor);

    //Partie DHT22
    // false: température en Celsius (par défaut)
    uint16_t temperature = dht.readTemperature(false) * 100;  // Lire la température (en °C) et la convertir en entier sur 16 bits
  
    // Séparer les valeurs des 16 bits en 2 octets de 8 bits pour chaque mesure
    payload[0] = highByte(temperature);  // Byte supérieur de la température
    payload[1] = lowByte(temperature);  // Byte inférieur de la température
    payload[2] = highByte(abs(WM1_CB));  //Pression centi-bar
    payload[3] = lowByte(abs(WM1_CB));
    payload[4] = highByte(kmh);
    payload[5] = lowByte(kmh);
    payload[6] = highByte(dirOut);
    payload[7] = lowByte(dirOut);
    //*****************output************************************
    // Afficher les valeurs de température et d'humidité sur le moniteur série pour débogage
    debugSerial.print("Temperaturedans l'air: ");
    debugSerial.println(temperature / 100.0);  // Affiche la température en °C (avec 2 décimales)
    Serial.print("Temperature dans le sol:");
    Serial.print(default_TempC);
    Serial.print("\n");
    Serial.print("WM1 Resistance(Ohms)= ");
    Serial.print(WM1_Resistance);
    Serial.print("\n");
    Serial.print("WM1(cb/kPa)= ");
    Serial.print(abs(WM1_CB /1000.0));
    Serial.print("\n");
    Serial.print("Wind Speed: ");
    Serial.print(kmh);
    Serial.print(" km/h, Direction: ");
    Serial.print(dirOut);
    Serial.println(" degrees");
    Serial.print("\n");
    // Envoyer les données sous forme de bytes via LoRa
    ttn.sendBytes(payload, sizeof(payload));  // Envoie le tableau de données sur le réseau LoRa
    delay(1800000);
    //j=1;
  }
}

//conversion of ohms to CB
int myCBvalue(int res, float TC, float cF) {   //conversion of ohms to CB
  int WM_CB;
  float resK = res / 1000.0;
  float tempD = 1.00 + 0.018 * (TC - 24.00);

  if (res > 550.00) { //if in the normal calibration range
    if (res > 8000.00) { //above 8k
      WM_CB = (-2.246 - 5.239 * resK * (1 + .018 * (TC - 24.00)) - .06756 * resK * resK * (tempD * tempD)) * cF;
    } else if (res > 1000.00) { //between 1k and 8k
      WM_CB = (-3.213 * resK - 4.093) / (1 - 0.009733 * resK - 0.01205 * (TC)) * cF ;
    } else { //below 1k
      WM_CB = (resK * 23.156 - 12.736) * tempD;
    }
  } else { //below normal range but above short (new, unconditioned sensors)
    if (res > 300.00)  {
      WM_CB = 0.00;
    }
    if (res < 300.00 && res >= short_resistance) { //wire short
      WM_CB = short_CB; //240 is a fault code for sensor terminal short
      Serial.print("Sensor Short WM \n");
    }
  }
  if (res >= open_resistance || res==0) {

    WM_CB = open_CB; //255 is a fault code for open circuit or sensor not present

  }
  return WM_CB;
}

//read ADC and get resistance of sensor
float readWMsensor() {  //read ADC and get resistance of sensor

  ARead_A1 = 0;
  ARead_A2 = 0;

  for (i = 0; i < num_of_read; i++) //the num_of_read initialized above, controls the number of read successive read loops that is averaged.
  {

    digitalWrite(5, HIGH);   //Set pin 5 as Vs
    delayMicroseconds(90); //wait 90 micro seconds and take sensor read
    ARead_A1 += analogRead(A1); // read the analog pin and add it to the running total for this direction
    digitalWrite(5, LOW);      //set the excitation voltage to OFF/LOW

    delay(100); //0.1 second wait before moving to next channel or switching MUX

    // Now lets swap polarity, pin 5 is already low

    digitalWrite(11, HIGH); //Set pin 11 as Vs
    delayMicroseconds(90); //wait 90 micro seconds and take sensor read
    ARead_A2 += analogRead(A1); // read the analog pin and add it to the running total for this direction
    digitalWrite(11, LOW);      //set the excitation voltage to OFF/LOW
  }

  SenVWM1 = ((ARead_A1 / 1024) * SupplyV) / (num_of_read); //get the average of the readings in the first direction and convert to volts
  SenVWM2 = ((ARead_A2 / 1024) * SupplyV) / (num_of_read); //get the average of the readings in the second direction and convert to volts
  
  double WM_ResistanceA = Rx * (SenVWM1) / (SupplyV - SenVWM1); //do the voltage divider math, using the Rx variable representing the known resistor
  double WM_ResistanceB = Rx * (SupplyV - SenVWM2) / SenVWM2;  // reverse
  double WM_Resistance = ((WM_ResistanceA + WM_ResistanceB) / 2); //average the two directions

  return WM_Resistance;
}

float getTemp(){
  //returns the temperature from one DS18S20 in DEG Celsius

  byte data[12];
  byte addr[8];

  if ( !ds.search(addr)) {
      //no more sensors on chain, reset search
      ds.reset_search();
      return -1000;
  }

  if ( OneWire::crc8( addr, 7) != addr[7]) {
      Serial.println("CRC is not valid!");
      return -1000;
  }

  if ( addr[0] != 0x10 && addr[0] != 0x28) {
      Serial.print("Device is not recognized");
      return -1000;
  }

  ds.reset();
  ds.select(addr);
  ds.write(0x44,1); // start conversion, with parasite power on at the end

  byte present = ds.reset();
  ds.select(addr);
  ds.write(0xBE); // Read Scratchpad


  for (int i = 0; i < 9; i++) { // we need 9 bytes
    data[i] = ds.read();
  }

  ds.reset_search();

  byte MSB = data[1];
  byte LSB = data[0];

  float tempRead = ((MSB << 8) | LSB); //using two's compliment
  float TemperatureSum = tempRead / 16;

  return TemperatureSum;
}

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
    //Serial.print("rps: ");Serial.println(rps);
    kmh = (knotsOut) * 1.852; // Conversion des noeuds en km/h
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
    Serial.println("\n");
}
