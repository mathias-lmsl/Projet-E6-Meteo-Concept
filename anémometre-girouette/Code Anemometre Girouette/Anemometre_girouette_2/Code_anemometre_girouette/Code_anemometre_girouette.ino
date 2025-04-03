#include <limits.h>

// Définition des pins
#define SPEED_REED_PIN 1  
#define WIND_DIRECTION_PIN 2

// Variables pour l'anémomètre
volatile unsigned long lastTimeSpeedReed = 0;
volatile unsigned long T = 0;
volatile bool newData = false;

#define DEBOUNCE_DELAY 10000  // 10 ms pour réduire les erreurs
#define NUM_MEASUREMENTS 5  // Nombre de mesures à moyenner

float measurements[NUM_MEASUREMENTS] = {0};  
int index = 0;

// Variables pour la girouette
volatile unsigned int rotation_took0;
volatile unsigned int rotation_took1;
volatile signed int direction_latency0;
volatile signed int direction_latency1;
volatile unsigned long last_rotation_at = millis();
unsigned long last_report = millis();

// ISR pour la vitesse du vent
void speedReedInterrupt() {
  unsigned long currentTime = micros();
  
  if (currentTime - lastTimeSpeedReed > DEBOUNCE_DELAY) {
    T = currentTime - lastTimeSpeedReed;
    lastTimeSpeedReed = currentTime;
    newData = true;
  }
}

// ISR pour la direction du vent
void isr_direction() {
  unsigned long now = millis();
  unsigned int direction_latency;

  if (now < last_rotation_at)
    direction_latency = now + (ULONG_MAX - last_rotation_at);
  else
    direction_latency = now - last_rotation_at;

  direction_latency0 = direction_latency;
}

void setup() {
  Serial.begin(9600);
  pinMode(SPEED_REED_PIN, INPUT_PULLUP);
  pinMode(WIND_DIRECTION_PIN, INPUT_PULLUP);
  
  attachInterrupt(digitalPinToInterrupt(SPEED_REED_PIN), speedReedInterrupt, RISING);
  attachInterrupt(digitalPinToInterrupt(WIND_DIRECTION_PIN), isr_direction, RISING);
}

float wdir_to_degrees() {
  if (direction_latency0 < 0) return NAN;

  float avg_rotation_time = ((float(rotation_took0) + float(rotation_took1)) / 2.0);
  float phaseshift = float(direction_latency0) / avg_rotation_time;

  if (phaseshift == 0.0 || phaseshift > 0.99) return 360.0;
  return 360.0 * phaseshift;
}

void loop() {
  if (newData && T > 0) {
    float frequency = 1e6 / float(T);
    
    if (frequency < 100) {
      measurements[index] = frequency;
      index = (index + 1) % NUM_MEASUREMENTS;
      
      float avgFrequency = 0;
      for (int i = 0; i < NUM_MEASUREMENTS; i++) {
        avgFrequency += measurements[i];
      }
      avgFrequency /= NUM_MEASUREMENTS;
      
      float Perimetre = 3.1416 * 0.13; 
      float Vitesse = Perimetre * avgFrequency;
      
      Serial.print("Fréquence moyenne (Hz) : ");
      Serial.println(avgFrequency);
      Serial.print("Vitesse (m/s): ");
      Serial.println(Vitesse);
    }
    delay(500);
    newData = false;
  }
  
  float awa = wdir_to_degrees();
  Serial.print("Direction du vent (°): ");
  Serial.println(awa);
  
  delay(1000);
}
