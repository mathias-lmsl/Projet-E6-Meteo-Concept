

void setup() {
  // put your setup code here, to run once:
  
  // initialize serial communications at 9600 bps:
  Serial.begin(9600);

  pinMode(11, OUTPUT);
  digitalWrite(11, LOW);
  
  delay(100);   // time in milliseconds, wait 0.1 minute to make sure the OUTPUT is assigned
}

void loop() {
  // put your main code here, to run repeatedly:
  digitalWrite(11, HIGH);
  delayMicroseconds(90);//wait 90 micro seconds and take sensor read
  
  int valeur = analogRead(A1);
  
  digitalWrite(11, LOW);
  
  Serial.println("valeur de tension: ");
  Serial.println(valeur);
  Serial.print("\n");

   delay(2000);
  
}
