#define SPEED_REED_PIN 2      
#define DIRECTION_REED_PIN 3

volatile unsigned long TempsPrecedantA = 0;  
volatile unsigned long DurationA = 0;  

//int TempsDebutG = 0;


void setup() {
  // put your setup code here, to run once:
  Serial.begin(9600);
  pinMode(SPEED_REED_PIN, INPUT_PULLUP);
  
  pinMode(DIRECTION_REED_PIN, INPUT_PULLUP);
  attachInterrupt(SPEED_REED_PIN, PeriodeAnemometre, FALLING);

}

void loop() {
  // put your main code here, to run repeatedly:
  
   //Serial.println("La durée est de: ");
   //Serial.println(DurationA);
  
}


void PeriodeAnemometre(){
  unsigned long currentTime = micros();
  
  if(digitalRead(DIRECTION_REED_PIN) == LOW){
    DurationA = currentTime - TempsPrecedantA;  
    TempsPrecedantA = currentTime; 
  }
  
}
