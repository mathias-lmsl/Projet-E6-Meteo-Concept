#include <math.h>

//********************************************************************************************************************
//***********************************************Version 1.1**********************************************************
//************Documentation available at : www.irrometer.com/200ss.html ******************************************
// Version 1.1 updated 7/21/2023 by Jeremy Sullivan, Irrometer Co Inc.*****************************************************
// Code tested on Arduino UNO R3
// Purpose of this code is to demonstrate valid WM reading code, circuitry and excitation using a voltage divider and "psuedo-ac" method
// This program uses a modified form of Dr. Clint Shock's 1998 calibration equation.
// Sensor to be energized by digital pin 11 or digital pin 5, alternating between HIGH and LOW states

//As a simplified example, this version reads one sensor only and assumes a default temperature of 24C.
//NOTE: the 0.09 excitation time may not be sufficient depending on circuit design, cable lengths, voltage, etc. Increase if necessary to get accurate readings, do not exceed 0.2
//NOTE: this code assumes a 10 bit ADC. If using 12 bit, replace the 1024 in the voltage conversions to 4096

#define num_of_read 1 // number of iterations, each is actually two reads of the sensor (both directions)
const int Rx = 10000;  //fixed resistor attached in series to the sensor and ground...the same value repeated for all WM and Temp Sensor.
const long default_TempC = 24;
const long open_resistance = 35000; //check the open resistance value by replacing sensor with an open and replace the value here...this value might vary slightly with circuit components
const long short_resistance = 200; // similarly check short resistance by shorting the sensor terminals and replace the value here.
const long short_CB = 240, open_CB = 255 ;
const int SupplyV = 5; // Assuming 5V output for SupplyV, this can be measured and replaced with an exact value if required
const float cFactor = 1.1; //correction factor optional for adjusting curve, 1.1 recommended to match IRROMETER devices as well as CS CR1000
int i, j = 0, WM1_CB = 0;
float SenV10K = 0, SenVWM1 = 0, SenVWM2 = 0, ARead_A1 = 0, ARead_A2 = 0, WM_Resistance = 0, WM1_Resistance = 0 ;

void setup()
{
  // initialize serial communications at 9600 bps:
  Serial.begin(9600);
  // initialize the pins, 5 and 11 randomly chosen. In the voltage divider circuit example in figure 1(www.irrometer.com/200ss.html), pin 11 is the "Output Pin" and pin 5 is the "GND".
  // if the direction is reversed, the WM1_Resistance A and B formulas would have to be swapped.
  pinMode(5, OUTPUT);
  pinMode(11, OUTPUT);
  //set both low
  digitalWrite(5, LOW);
  digitalWrite(11, LOW);

  delay(100);   // time in milliseconds, wait 0.1 minute to make sure the OUTPUT is assigned
}

void loop()
{
  
    //Read the first Watermark sensor

    WM1_Resistance = readWMsensor(); // lecture résistance
    WM1_CB = myCBvalue(WM1_Resistance, default_TempC, cFactor);

    //*****************output************************************

    Serial.print("WM1 Resistance(Ohms)= ");
    Serial.print(WM1_Resistance);
    Serial.print("\n");
    Serial.print("WM1(cb/kPa)= ");
    Serial.print(abs(WM1_CB));
    Serial.print("\n");

    delay(30000);
}

//conversion of ohms to CB
int myCBvalue(int res, float TC, float cF) {   //conversion of ohms to CB
  // renvoie la mesure de la mesure d'hygrometrie en cb/kPa ou un code d'erreur:
  //Entre 550 et 35 000 ohms mesuré, renvoie une mesure valide en cb/kPa.
  //Entre 300 et 550ohms, mesure invalide --> code d'erreur 0
  //En dessous de 300ohms, le capteur est considéré comme étant en court-circuit => renvoie 240
  // au-dessus de 35 000 Ohms capteur considéré en circuit ouvert renvoie 255
  
  int WM_CB;
  float resK = res / 1000.0;
  float tempD = 1.00 + 0.018 * (TC - 24.00);




  if (res > 550.00) { //if in the normal calibration range
    if (res > 8000.00) { //above 8k
      WM_CB = (-2.246 - 5.239 * resK * (1 + .018 * (TC - 24.00)) - .06756 * resK * resK * (tempD * tempD)) * cF;
    } else if (res > 1000.00) { //between 1k and 8k
      WM_CB = (-3.213 * resK - 4.093) / (1 - 0.009733 * resK - 0.01205 * (TC)) * cF ;
    } else { //below 1k
      WM_CB = (resK * 23.156 - 12.736) * tempD;
    }
  } else { //below normal range but above short (new, unconditioned sensors)
    if (res > 300.00)  {
      WM_CB = 0.00;
    }
    if (res < 300.00 && res >= short_resistance) { //wire short
      WM_CB = short_CB; //240 is a fault code for sensor terminal short
      Serial.print("Sensor Short WM \n");
    }
  }
  if (res >= open_resistance){ //|| res==0) { > 35 000 Ohms
    WM_CB = open_CB; //255 is a fault code for open circuit or sensor not present
  }
  
    if (res ==0){ 
    WM_CB = open_CB; //255 is a fault code for open circuit or sensor not present
  }
  return WM_CB;
}

//read ADC and get resistance of sensor
float readWMsensor() {  //read ADC and get resistance of sensor

  ARead_A1 = 0;

  digitalWrite(11, HIGH); //Set pin 11 as Vs
  delayMicroseconds(90); //wait 90 micro seconds and take sensor read
  ARead_A1 = analogRead(A1); // read the analog pin and add it to the running total for this direction
  digitalWrite(11, LOW);      //set the excitation voltage to OFF/LOW

  SenVWM1 = ((ARead_A1 / 1024) * SupplyV); //get the average of the readings in the first direction and convert to volts

  double WM_Resistance = (Rx * (SupplyV - SenVWM1) / SenVWM1); //do the voltage divider math, using the Rx variable representing the known resistor
  return WM_Resistance;
}
