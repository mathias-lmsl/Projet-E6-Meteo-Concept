#define SPEED_REED_PIN A1  // Définition de la broche analogique A1

volatile unsigned long lastTimeSpeedReed = 0;
volatile unsigned long timeSpeedReed = 0;
volatile unsigned long T = 0;
volatile bool newData = false;  // Flag pour éviter de recalculer à chaque boucle

#define DEBOUNCE_DELAY 5000  // 5 ms en microsecondes (µs)

void setup() {
  // Initialisation de la communication série et de la broche
  Serial.begin(9600);
  pinMode(SPEED_REED_PIN, INPUT);  // La broche A1 est utilisée en mode entrée

  // Initialisation de la dernière valeur de temps
  lastTimeSpeedReed = micros();
}

void loop() {
  // Lecture de la valeur analogique (la broche A1 sera lue)
  int sensorValue = analogRead(SPEED_REED_PIN);

  // Conversion de la valeur analogique en un état (0 ou 1) basé sur un seuil
  bool reedState = (sensorValue < 512);  // Seuil arbitraire de 512 pour détecter l'état bas

  unsigned long currentTime = micros();

  // Vérification de l'état du capteur de manière similaire à une interruption
  if (reedState && (currentTime - lastTimeSpeedReed > DEBOUNCE_DELAY)) {
    // Si l'état est bas et qu'il n'y a pas eu de changement récent
    T = currentTime - lastTimeSpeedReed;
    lastTimeSpeedReed = currentTime;
    timeSpeedReed = currentTime;
    newData = true;  // Marque la disponibilité de nouvelles données
  }

  // Si de nouvelles données sont disponibles, les afficher
  if (newData && T > 0) {
    Serial.print("T (µs) : ");
    Serial.print(T);
    newData = false;  // Réinitialiser le flag pour éviter les doublons
    delay(500);  // Pause pour rendre la sortie lisible
  }
}
