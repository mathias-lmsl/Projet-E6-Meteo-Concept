#include <FreqMeasure.h>

void setup() {
  Serial.begin(9600);
  FreqMeasure.begin();
}

double sum = 0;
int count = 0;

void loop() {
  if (FreqMeasure.available()) {
    double period = FreqMeasure.read();  // Récupère la période du signal
    sum += period;
    count++;

    if (count > 30) {  // Moyenne après 30 mesures
      double averagePeriod = sum / count;  
      double frequency = 1.0e6 / averagePeriod;  // Convertir période en fréquence

      Serial.print("Fréquence (Hz) : ");
      Serial.println(frequency);

      sum = 0;
      count = 0;
    }
  }
}
