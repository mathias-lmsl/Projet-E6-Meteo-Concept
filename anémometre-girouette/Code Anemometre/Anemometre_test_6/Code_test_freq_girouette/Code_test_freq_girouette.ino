#define SPEED_REED_PIN 2  // Définit la broche du capteur à effet Hall ou du capteur Reed (broche 1)
#define WIND_VANE_PIN 3
volatile unsigned long lastTimeSpeedReed = 0;  // Variable pour stocker l'heure du dernier passage du capteur
volatile unsigned long T = 0; // Variable pour stocker le temps entre les passages du capteur
volatile unsigned long TValide = 0;

volatile unsigned long lastTimeSpeedReedG = 0;  // Variable pour stocker l'heure du dernier passage du capteur
volatile unsigned long TGirou = 0;
volatile unsigned long TGirouValide = 0;
//unsigned long currentTimeA = 0;
unsigned long Diff;
unsigned long DiffValide;

volatile int newData = 0;  // Flag pour indiquer qu'il y a de nouvelles données disponibles

#define DEBOUNCE_DELAY 30000  // Délai anti-rebond de 10ms pour éviter les erreurs dues aux rebonds du signal
#define NUM_MEASUREMENTS 5  // Nombre de mesures à moyenner pour lisser les résultats

float measurements[NUM_MEASUREMENTS] = {0};  // Tableau pour stocker les dernières mesures de fréquence
int index = 0;  // Indice pour parcourir le tableau des mesures

// Fonction d'interruption qui est déclenchée à chaque impulsion du capteur
void speedReedInterrupt() {
 unsigned long currentTimeA = micros();  // Temps actuel en microsecondes
  // Si le délai entre deux impulsions est supérieur au délai anti-rebond, on procède à la mesure
  if (currentTimeA - lastTimeSpeedReed >= DEBOUNCE_DELAY) {
    T = currentTimeA - lastTimeSpeedReed;  // Calcule le temps entre deux impulsions
    
    lastTimeSpeedReed = currentTimeA;  // Met à jour l'heure du dernier passage
    newData = newData + 1;  // Indique qu'il y a de nouvelles données disponibles
    if (Diff > 400){
      TValide = T ;
      newData++;
    }
  }
}

// Fonction d'interruption qui est déclenchée à chaque impulsion de la girouette
void windVaneInterrupt() {
 unsigned long currentTimeG = micros();  // Temps actuel en microsecondes

  // Si le délai entre deux impulsions est supérieur au délai anti-rebond, on procède à la mesure
  if (currentTimeG - lastTimeSpeedReedG > DEBOUNCE_DELAY) {
    TGirou = currentTimeG - lastTimeSpeedReedG;  // Calcule le temps entre deux impulsions
    lastTimeSpeedReedG = currentTimeG;  // Met à jour l'heure du dernier passage
    newData = newData + 1;  // Indique qu'il y a de nouvelles données disponibles
    
    Diff = lastTimeSpeedReedG - lastTimeSpeedReed;
    
    if (Diff > 400){
      DiffValide = Diff ;
      TGirouValide = TGirou ;
      newData++;
    }
    
  }
}





void setup() {
  Serial.begin(9600);  // Initialise la communication série pour l'affichage des résultats
  pinMode(SPEED_REED_PIN, INPUT_PULLUP);  // Définit la broche du capteur comme entrée avec une résistance pull-up
  pinMode(WIND_VANE_PIN, INPUT_PULLUP);
  // Attache une interruption sur la broche du capteur pour appeler la fonction `speedReedInterrupt` à chaque front descendant
  attachInterrupt(digitalPinToInterrupt(SPEED_REED_PIN), speedReedInterrupt, FALLING);
  attachInterrupt(digitalPinToInterrupt(WIND_VANE_PIN), windVaneInterrupt, FALLING);
}

void loop() {
  // Si de nouvelles données sont disponibles et que le temps mesuré est valide (T > 0)
  if (newData > 1) {
    float frequency = 1e6 / float(T); // Calcule la fréquence (impulsions par seconde) à partir du temps entre deux impulsions
    
    // Si la fréquence est raisonnable (inférieure à 100 Hz), on procède à l'enregistrement des données
    if (frequency < 100) {
      measurements[index] = frequency;  // Enregistre la fréquence mesurée dans le tableau
      index = (index + 1) % NUM_MEASUREMENTS; // Avance l'indice dans le tableau, en faisant une boucle lorsque l'indice atteint NUM_MEASUREMENTS
      
      // Calcul de la fréquence moyenne à partir des dernières mesures
      float avgFrequency = 0;
      for (int i = 0; i < NUM_MEASUREMENTS; i++) {
        avgFrequency += measurements[i];  // Somme des fréquences mesurées
      }
      avgFrequency /= NUM_MEASUREMENTS;  // Moyenne des fréquences mesurées

      // Calcul du périmètre de la roue (en utilisant un diamètre de 0,13 mètre)
      float Perimetre = 3.1416 * 0.13;  // Périmètre = pi * diamètre (en mètres)

      // Calcul de la vitesse en multipliant le périmètre par la fréquence
      float Vitesse = Perimetre * avgFrequency;  // Vitesse en mètres par seconde

      float duree = 1 / avgFrequency;
      float lastValidDir;
      if (TValide > 0 && DiffValide > 0) {
      float dir = (float(DiffValide) / float(TValide)) * 360.0;

      dir = fmod(dir, 360.0);  // Utilisation de la fonction fmod pour le calcul modulo
      
      // Si dir est négatif, ajouter 360 pour le ramener dans la plage positive
      if (dir < 0) {
        dir += 360.0;
      }
      
      // Conserver la dernière direction valide si elle a changé
      lastValidDir = dir;

      // Affichage des résultats
      // Affichage des résultats sur le moniteur série
      Serial.print("Fréquence moyenne (Hz) : ");
      Serial.println(avgFrequency);  // Affiche la fréquence moyenne en Hz

      Serial.print("Vitesse (m/s): ");
      Serial.println(Vitesse);  // Affiche la vitesse calculée en mètres par seconde
      Serial.print("\n");
      
      Serial.print("Periode Anemometre (us): ");
      Serial.println(TValide);

      Serial.print("Periode Girouette (us): ");
      Serial.println(TGirouValide);
    
      Serial.print("Difference entre signaux (us): ");
      Serial.println(DiffValide);
      Serial.print("\n");
      
      Serial.print("Direction (°): ");
      Serial.println(dir);
      
    } else {
      
      // Si aucune nouvelle impulsion n'est reçue, conserver la dernière direction valide
      Serial.print("Fréquence moyenne (Hz) : ");
      Serial.println(avgFrequency);  // Affiche la fréquence moyenne en Hz

      Serial.print("Vitesse (m/s): ");
      Serial.println(Vitesse);  // Affiche la vitesse calculée en mètres par seconde
      Serial.print("\n");
      
      Serial.print("Periode Anemometre (us): ");
      Serial.println(TValide);

      Serial.print("Periode Girouette (us): ");
      Serial.println(TGirouValide);
    
      Serial.print("Difference entre signaux (us): ");
      Serial.println(DiffValide);
      Serial.print("\n");
      
      Serial.print("Direction (°): ");
      Serial.println(lastValidDir);
    } 

      
    }
    
    delay(500);  // Attendre 500 ms avant de prendre une nouvelle mesure

    newData = 0;  // Réinitialiser le flag pour indiquer qu'il n'y a plus de nouvelles données
  }
}
