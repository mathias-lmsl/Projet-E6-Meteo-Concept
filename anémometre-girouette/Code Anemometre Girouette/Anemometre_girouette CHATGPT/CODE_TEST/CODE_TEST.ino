#define SPEED_REED_PIN 2      
#define DIRECTION_REED_PIN 3  

volatile unsigned long lastTimeSpeedReed = 0;
volatile unsigned long timeSpeedReed = 0;
volatile unsigned long timeDirectionReed = 0;
volatile unsigned long deltaT = 0;
volatile unsigned long T = 0;
volatile bool newData = false;  // Flag pour éviter de recalculer à chaque boucle

const char* directions[] = {
  "N", "NNE", "NE", "ENE", "E", "ESE", "SE", "SSE",
  "S", "SSW", "SW", "WSW", "W", "WNW", "NW", "NNW"
};

// Filtrage anti-rebond et validation
#define DEBOUNCE_DELAY 5000  // 5 ms en microsecondes (µs)

void speedReedInterrupt() {
  unsigned long currentTime = micros();
  if (currentTime - lastTimeSpeedReed > DEBOUNCE_DELAY) {  // Anti-rebond
    T = currentTime - lastTimeSpeedReed;
    lastTimeSpeedReed = currentTime;
    timeSpeedReed = currentTime;
    newData = true;  // Indique qu'une nouvelle donnée est disponible
  }
}

void directionReedInterrupt() {
  unsigned long currentTime = micros();
  if (currentTime - timeSpeedReed > 1000) {  // Vérification du timing pour éviter les erreurs
    timeDirectionReed = currentTime;
    deltaT = timeDirectionReed - timeSpeedReed;
    newData = true;
  }
}

void setup() {
  Serial.begin(9600);
  pinMode(SPEED_REED_PIN, INPUT_PULLUP);
  pinMode(DIRECTION_REED_PIN, INPUT_PULLUP);

  attachInterrupt(digitalPinToInterrupt(SPEED_REED_PIN), speedReedInterrupt, FALLING);
  attachInterrupt(digitalPinToInterrupt(DIRECTION_REED_PIN), directionReedInterrupt, FALLING);
}

void loop() {
  if (newData && T > 0 && deltaT > 0) {  // Vérifie si une nouvelle mesure est disponible
    float direction = (float(deltaT) / float(T)) * 360.0;

    if (direction >= 360.0) direction -= 360.0;
    if (direction < 0.0) direction += 360.0;

    int index = round(direction / 22.5) % 16;

    Serial.print("T (µs) : ");
    Serial.print(T);
    Serial.print(" | Δt (µs) : ");
    Serial.print(deltaT);
    Serial.print(" | Direction (°) : ");
    Serial.print(direction);
    Serial.print(" | Direction cardinale : ");
    Serial.println(directions[index]);

    newData = false;  // Reset le flag pour éviter les doublons
    delay(500);
  }
}
