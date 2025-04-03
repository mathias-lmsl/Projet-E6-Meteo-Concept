const int Signal_GBF = A1; // Signal Analogique

void setup() {
  // Configure le port série pour l'exemple
  Serial.begin(9600);

  // Met la broche de signal venant du GBF en entrée
  pinMode(Signal_GBF, INPUT);
}

void loop() {
  // Mesure la durée de l'impulsion haute
  unsigned long etat_haut = pulseIn(Signal_GBF, HIGH);

  // Mesure la durée de l'impulsion basse
  unsigned long etat_bas = pulseIn(Signal_GBF, LOW);

  // Calcul de la periode = etat haut + etat bas
  long periode = etat_bas + etat_haut;

  // Calcul de la frequence = 1 / periode
  long frequence = 1000000 / periode;

  // Affichage des résultats
  Serial.println("Duree etat haut : " + String(etat_haut));
  Serial.println("Duree etat bas : " + String(etat_bas));
  Serial.println("Periode : " + String(periode));
  Serial.println("Frequence : " + String(frequence) + " Hz");
  Serial.println();

  delay(1000);
}
