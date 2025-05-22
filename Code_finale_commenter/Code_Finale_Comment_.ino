#include <math.h>
#include <TheThingsNetwork.h>
#include <DHT.h>
#include <OneWire.h>

// ==================== CONFIGURATION TTN (LoRa) ====================
// Identifiants pour connecter le module à The Things Network (TTN)
const char *appEui = "0004A30B001E6712";  
const char *appKey = "3BE8C099BA7541CD3F2B90443FE2BA20";
// Identifiants pour connecter le module à Chipstark
//const char *appEui = "0004A30B001E6712";
//const char *appKey = "f6ede1ce4a6d607d1e7c774d224e573f";

// Ports série pour la communication LoRa
#define loraSerial Serial1
#define debugSerial Serial
#define freqPlan TTN_FP_EU868
TheThingsNetwork ttn(loraSerial, debugSerial, freqPlan);

// ==================== CONFIGURATION DHT22 ====================
// Capteur de température et d'humidité
#define DHTPIN 4      // Broche du DHT22
#define DHTTYPE DHT22 // Type du capteur
DHT dht(DHTPIN, DHTTYPE);

// ==================== CONFIGURATION SONDE HYGROMÉTRIQUE ====================
#define NUM_OF_READ 1          // Nombre de lectures pour la moyenne
const int Rx = 10000;          // Résistance de référence en ohms
const float cFactor = 1.1;     // Facteur de calibration
const long OPEN_RESISTANCE = 35000, SHORT_RESISTANCE = 200;
const int SUPPLY_V = 5;        // Tension d'alimentation en volts
float default_TempC;           // Température mesurée
int WM1_CB = 0;           // Capacité en eau du sol
const long short_resistance = 200;
const long open_resistance = 35000;
const long short_CB = 240, open_CB = 255 ;
double WM_Resistance;
//int Vres;

// ==================== CONFIGURATION SONDE TEMPÉRATURE DS18S20 ====================
#define DS18S20_Pin 6 // Broche du capteur de température DS18S20
OneWire ds(DS18S20_Pin);

// ==================== CONFIGURATION ANÉMOMÈTRE ====================
// Broches pour l'anémomètre
#define WIND_SPEED_PIN 2
#define WIND_DIR_PIN 3
#define DEBOUNCE 10000ul       // Anti-rebond en microsecondes
#define TIMEOUT 1500000ul      // Temps d'attente avant timeout
const float FILTER_GAIN = 0.25;
volatile unsigned long speedPulse = 0ul, dirPulse = 0ul;
volatile unsigned long speedTime = 0ul, directionTime = 0ul;
volatile boolean newData = false;
uint16_t dirOut = 0, kmh = 0; // Vitesse et direction du vent
float RPS;

// ==================== CONFIGURATION INITIALE ====================
void setup() {
    Serial.begin(9600);
    loraSerial.begin(57600);
    debugSerial.begin(9600);
    while (!debugSerial && millis() < 10000); // Attente pour la connexion série

    debugSerial.println("-- STATUS");
    ttn.showStatus();
    debugSerial.println("-- JOIN");
    ttn.join(appEui, appKey); // Connexion à TTN

    dht.begin();

    // Configuration des broches pour la sonde d'humidité
    pinMode(5, OUTPUT);
    pinMode(11, OUTPUT);
    digitalWrite(5, LOW);
    digitalWrite(11, LOW);

    // Configuration des interruptions pour l'anémomètre
    pinMode(WIND_SPEED_PIN, INPUT);
    attachInterrupt(digitalPinToInterrupt(WIND_SPEED_PIN), readWindSpeed, FALLING);
    pinMode(WIND_DIR_PIN, INPUT);
    attachInterrupt(digitalPinToInterrupt(WIND_DIR_PIN), readWindDir, FALLING);
    interrupts();
}

// ==================== BOUCLE PRINCIPALE ====================
void loop() {
    if (newData) {
        calcWindSpeedAndDir();
        newData = false;
    }

    // Création du tableau de données à envoyer
    byte payload[7];

    // Lecture des capteurs
    default_TempC = getTemp();
    WM1_CB = abs(myCBvalue(readWMsensor(), default_TempC));

    // Lecture de la température de l'air et conversion en centièmes de degré
    uint16_t temperature = dht.readTemperature(false) * 10;

    // Remplissage du tableau de données
    payload[0] = highByte(temperature);
    payload[1] = lowByte(temperature);
    payload[2] = WM1_CB;
    payload[3] = highByte(kmh);
    payload[4] = lowByte(kmh);
    payload[5] = highByte(dirOut);
    payload[6] = lowByte(dirOut);

    // Affichage des valeurs pour debug
    debugSerial.print("Température air(°c): "); debugSerial.println(temperature / 10.0); /// 100.0);
    debugSerial.print("Température sol(°c): "); debugSerial.println(default_TempC);
    debugSerial.print("Hygrométrie du sol(Cb)= "); debugSerial.println(WM1_CB); /// 1000.0);
    debugSerial.print("Valeur résistance: "); debugSerial.println(WM_Resistance);//Vres);
    debugSerial.print("Vent: "); debugSerial.print(kmh); debugSerial.print(" km/h, Dir: "); debugSerial.println(dirOut);
    debugSerial.print("Vitesse en RPM: "); debugSerial.println(RPS* 60);
    // Envoi des données à TTN
    ttn.sendBytes(payload, sizeof(payload));
    delay(1000); // Pause avant la prochaine lecture
}

// ==================== CONVERSION RÉSISTANCE -> CB ====================
int myCBvalue(int res, float TC) {   //conversion of ohms to CB
  int WM_CB;
  float resK = res / 1000.0;
  float tempD = 1.00 + 0.018 * (TC - 24.00);

  if (res > 550.00) { //if in the normal calibration range
    if (res > 8000.00) { //above 8k
      WM_CB = (-2.246 - 5.239 * resK * (1 + .018 * (TC - 24.00)) - .06756 * resK * resK * (tempD * tempD));
    } else if (res > 1000.00) { //between 1k and 8k
      WM_CB = (-3.213 * resK - 4.093) / (1 - 0.009733 * resK - 0.01205 * (TC));
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

// ==================== LECTURE SONDE HYGROMÉTRIQUE ====================
float readWMsensor() {
    float ARead_A1 = 0, ARead_A2 = 0;

    for (int i = 0; i < NUM_OF_READ; i++) {
        digitalWrite(5, HIGH);
        delayMicroseconds(90);
        ARead_A1 += analogRead(A1);
        digitalWrite(5, LOW);
        delay(100);

        digitalWrite(11, HIGH);
        delayMicroseconds(90);
        ARead_A2 += analogRead(A1);
        digitalWrite(11, LOW);
    }

    // Conversion en tension et calcul de la résistance
    float SenVWM1 = (ARead_A1 / 1024.0) * SUPPLY_V / NUM_OF_READ;
    float SenVWM2 = (ARead_A2 / 1024.0) * SUPPLY_V / NUM_OF_READ;
    
    double WM_ResistanceA = (Rx * (SUPPLY_V - SenVWM1) / SenVWM1);
    double WM_ResistanceB = Rx * SenVWM2 / (SUPPLY_V - SenVWM2);  
    WM_Resistance = ((WM_ResistanceA + WM_ResistanceB) / 2);
    //Vres = (Rx * SenVWM1 / (SUPPLY_V - SenVWM1) + Rx * (SUPPLY_V - SenVWM2) / SenVWM2) / 2;
    //return Vres;
    return WM_Resistance;
}

// ==================== LECTURE TEMPÉRATURE DS18S20 ====================
float getTemp() {
    byte data[12], addr[8];
    if (!ds.search(addr)) return -1000;

    ds.reset(); ds.select(addr); ds.write(0x44, 1);
    ds.reset(); ds.select(addr); ds.write(0xBE);

    for (int i = 0; i < 9; i++) data[i] = ds.read();
    ds.reset_search();

    return ((data[1] << 8) | data[0]) / 16.0;
}

// ==================== ANÉMOMÈTRE ====================
void readWindSpeed() {
    if ((micros() - speedPulse) > DEBOUNCE && digitalRead(WIND_SPEED_PIN) == LOW) {
        speedTime = micros() - speedPulse;
        if (dirPulse > speedPulse) directionTime = dirPulse - speedPulse;
        newData = true;
        speedPulse = micros();
    }
}

void readWindDir() {
    if ((micros() - dirPulse) > DEBOUNCE && digitalRead(WIND_DIR_PIN) == LOW) dirPulse = micros();
}

void calcWindSpeedAndDir() {
    if (micros() - speedPulse > TIMEOUT) speedTime = 0;

    RPS = (speedTime > 0) ? 1.0 / (speedTime * 1e-6) : 0.0; // rotation par seconde
    float mph = 0.0;

    if (RPS > 0.010 && RPS <= 3.229) {
        mph = -0.1095 * RPS * RPS + 2.9318 * RPS - 0.1412;
    } else if (RPS > 3.230 && RPS <= 54.362) {
        mph = 0.0052 * RPS * RPS + 2.1980 * RPS + 1.1091;
    } else if (RPS > 54.363 && RPS <= 66.332) {
        mph = 0.1104 * RPS * RPS - 9.5685 * RPS + 329.87;
    } else {
        mph = 0.0;
    }

    kmh = round(mph * 1.6094); // Conversion en km/h, arrondi à l'entier

    if (directionTime < speedTime && speedTime > 0) {
        dirOut = (directionTime * 360 / speedTime) % 360;
    }
}
