 
#define brocheGirouette A0 //cable vert

void setup() {
  // put your setup code here, to run once:
  Serial.begin(9600);
  pinMode(brocheGirouette, INPUT);
}

void loop() {
  // put your main code here, to run repeatedly:.
  int valGirouette = analogRead(brocheGirouette);
  Serial.println(valGirouette);
  
  if(valGirouette >= 790 && valGirouette <= 792  ){
   Serial.println("Nord");
  }
  else if(valGirouette >= 409 && valGirouette <= 465){
    Serial.println("Nord-Est");
  }
   else if(valGirouette >= 83 && valGirouette <= 94  ){
    Serial.println("Est");
  }
   
  //float Vmesure = (valGirouette/ 1023.0) * 5;
 // Serial.println(valGirouette); 
  
  

  
  delay(2000);

}
