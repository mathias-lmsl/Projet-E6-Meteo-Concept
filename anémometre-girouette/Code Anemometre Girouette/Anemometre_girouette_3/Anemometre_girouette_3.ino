#define SPEED_REED_PIN 1  // Anémomètre sur INT0 
#define WIND_VANE_PIN 2   // Girouette sur INT1

#define DEBOUNCE_DELAY 20000  // 20 ms pour réduire les erreurs
#define NUM_MEASUREMENTS 5  // Nombre de mesures pour lisser les valeurs
int k = 0;
int p =0;

volatile unsigned long lastTimeSpeedReed = 0;
volatile unsigned long lastTimeWindVane = 0;
volatile unsigned long T_anem = 0;
volatile unsigned long T_girouette = 0;
volatile unsigned long T_period_anem = 0; // Période complète de l’anémomètre
volatile long T_diff = 0; // Temps entre T_anem et T_girouette
volatile bool newDataAnem = false;
volatile bool newDataWindVane = false;

float speedMeasurements[NUM_MEASUREMENTS] = {0};  
float angleMeasurements[NUM_MEASUREMENTS] = {0};  
int indexSpeed = 0;
int indexAngle = 0;


void setup() {
  Serial.begin(9600);
  pinMode(SPEED_REED_PIN, INPUT_PULLUP);
  pinMode(WIND_VANE_PIN, INPUT_PULLUP);
  attachInterrupt(digitalPinToInterrupt(SPEED_REED_PIN), speedReedInterrupt, FALLING);  // Correction ici
  attachInterrupt(digitalPinToInterrupt(WIND_VANE_PIN), windVaneInterrupt, FALLING);  // Correction ici
}

void loop() {
  if (newDataAnem) {
    float frequency = 1e6 / float(T_period_anem); // Fréquence en Hz
    float Perimetre = 3.1416 * 0.13;  // Périmètre de l’anémomètre (π × diamètre)
    float Vitesse = Perimetre * frequency;  // Vitesse du vent (m/s)

    speedMeasurements[indexSpeed] = Vitesse;
    indexSpeed = (indexSpeed + 1) % NUM_MEASUREMENTS;
    
    float avgSpeed = 0;
    for (int i = 0; i < NUM_MEASUREMENTS; i++) {
      avgSpeed += speedMeasurements[i];
    }
    avgSpeed /= NUM_MEASUREMENTS;

    Serial.print("Vitesse moyenne du vent (m/s) : ");
    Serial.println(avgSpeed);

    Serial.print("Temps entre interruptions Anémomètre (µs) : ");
    Serial.println(T_anem);
    
    newDataAnem = false;
  }

  if (newDataWindVane && T_period_anem > 0) {
    float angleWind = (float(T_girouette) / float(T_period_anem)) * 360.0;

    // Empêcher un dépassement de 360°
    angleWind = fmod(angleWind, 360.0);

    // Ajustement pour que SUD = 0°
    angleWind = 360.0 - angleWind;
    if (angleWind >= 360.0) angleWind -= 360.0;

    angleMeasurements[indexAngle] = angleWind;
    indexAngle = (indexAngle + 1) % NUM_MEASUREMENTS;

    float avgAngle = 0;
    for (int i = 0; i < NUM_MEASUREMENTS; i++) {
      avgAngle += angleMeasurements[i];
    }
    avgAngle /= NUM_MEASUREMENTS;
if( angleWind <=210 && angleWind >= 150){
    Serial.print("Direction moyenne du vent (degrés) : ");
    Serial.println(angleWind);

   /* Serial.print("Temps entre interruptions Girouette (µs) : ");
    Serial.println(T_girouette);

    // Affichage du temps entre les interruptions de l’anémomètre et de la girouette
    Serial.print("Temps entre T_anem et T_girouette (µs) : ");
    Serial.println(T_diff);*/

    newDataWindVane = false;
  }

  delay(1000);
}
}

void speedReedInterrupt() {
  unsigned long currentTime = micros();
  if (currentTime - lastTimeSpeedReed > DEBOUNCE_DELAY) {
   // currentTime - lastTimeSpeedReed;
    T_anem = currentTime - lastTimeSpeedReed; // Temps entre deux ticks de l’anémomètre
    p = p+1;
    if(p > 2)
    {
    /*
    Serial.println("P=");
    Serial.println(p);
    Serial.println("T_anem=");
    Serial.print(T_anem);*/
    
  }

    lastTimeSpeedReed = currentTime;

    // Mesure d’une période complète
    static unsigned long previousT_anem = 0;
    if (previousT_anem > 0) {
      T_period_anem = (T_anem + previousT_anem) / 2; // Moyenne pour lisser
    }
    previousT_anem = T_anem;
    
    newDataAnem = true;
  }
}

void windVaneInterrupt() {
  unsigned long currentTime = micros();
  
 // if (currentTime - lastTimeWindVane > DEBOUNCE_DELAY) 
    T_girouette = currentTime - lastTimeSpeedReed;  // Temps écoulé depuis le dernier passage de l’anémomètre
    k = k+1;
/*  if(k > 2){
    Serial.println("K=");
    Serial.println(k);
    Serial.println("T_girouette=");
    Serial.println(T_girouette);*/
  
    lastTimeWindVane = currentTime;

    // Calcul du temps entre l’anémomètre et la girouette
    T_diff = T_girouette - T_anem;

    newDataWindVane = true;
  
}
