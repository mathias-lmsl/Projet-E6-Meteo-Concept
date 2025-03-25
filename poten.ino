 
 #include <TheThingsNetwork.h>
#include <Arduino.h>

// Set your AppEUI and AppKey
const char *appEui = "0004A30B00216C4C";
const char *appKey = "bf767f6bdd1ed0b4d0e68822be9b4d2d";

#define loraSerial Serial1
#define debugSerial Serial

// Replace REPLACE_ME with TTN_FP_EU868 or TTN_FP_US915
#define freqPlan TTN_FP_EU868

TheThingsNetwork ttn(loraSerial, debugSerial, freqPlan);

void setup()
{
  loraSerial.begin(57600);
  debugSerial.begin(9600);

  // Wait a maximum of 10s for Serial Monitor
  while (!debugSerial && millis() < 10000)
    ;

  debugSerial.println("-- STATUS");
  ttn.showStatus();

  debugSerial.println("-- JOIN");
  ttn.join(appEui, appKey);

}

void loop()
{
  debugSerial.println("-- LOOP");


  int Pot_val = 100;// = analogRead(A0)/* * 5) /1023*/;
  int Pot_val2 = 10;// = analogRead(A5)/* * 5) /1023*/;
  /*Pot_val = 0xa035;*/
  int temp = 50;
  int humidity = 1330;// = 0x00C8;
  Serial.println(Pot_val); /*pour la vitesse du vent*/
  Serial.println(Pot_val2); /*pour la direction du vent*/

  //Changer les valeurs
  temp += random(-5,5);
  humidity += random(-30,30);
  Pot_val1 += random(-10,10)
  Pot_val2 = (Pot_val2 + random(-450,450)) % 360;
 
  // Split both words (16 bits) into 2 bytes of 8
  byte payload[8];
  payload[0] = highByte(temp);
  payload[1] = lowByte(temp);//
  payload[2] = highByte(humidity);
  payload[3] = lowByte(humidity);
  payload[4] = highByte(Pot_val);
  payload[5] = lowByte(Pot_val);//
  payload[6] = highByte(Pot_val2);
  payload[7] = lowByte(Pot_val2);

  debugSerial.print("Potentiomètre: ");
  debugSerial.println(Pot_val);
  debugSerial.print("Potentiomètre 2: ");
  debugSerial.println(Pot_val2);
  //debugSerial.print("Humidity: ");
  //debugSerial.println(humidity);

  ttn.sendBytes(payload, sizeof(payload));

  delay(1800000);
}
