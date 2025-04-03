#define windSpeedPin 2
//#define windSpeedOutPin 8

#define windDirPin 3
//#define windDirOutPin   9
//#define windDirPin 2    // Sur The Things Uno, INT1 est sur D2

//#define windSpeedINT 0 // INT0
//#define windDirINT 1   // INT1

//#define windSpeedPin 3  // Sur The Things Uno, INT0 est sur D3
#define windSpeedINT 0  // INT0 = D3


#define windDirINT 1    // INT1 = D2

//#define windSpeedOutPin 8
//#define windDirOutPin   9

const unsigned long DEBOUNCE = 10000ul;      // Minimum switch time in microseconds
const unsigned long DIRECTION_OFFSET = 0ul;  // Manual direction offset in degrees, if required
const unsigned long TIMEOUT = 1500000ul;     // Maximum time allowed between speed pulses in microseconds
const unsigned long UPDATE_RATE = 500ul;     // How often to send out NMEA data in milliseconds

volatile unsigned long speedPulse = 0ul;     // Time capture of speed pulse
volatile unsigned long dirPulse = 0ul;       // Time capture of direction pulse
volatile unsigned long speedTime = 0ul;      // Time between speed pulses (microseconds)
volatile unsigned long directionTime = 0ul;  // Time between direction pulses (microseconds)
volatile boolean newData = false;            // New speed pulse received
volatile unsigned long lastUpdate = 0ul;     // Time of last serial output
int windDirection = 0;
volatile boolean ignoreNextReading = false;

boolean debug = true;

void setup()
{
  pinMode(windSpeedOutPin, OUTPUT);
  pinMode(windDirOutPin, OUTPUT);

  Serial.begin(115200, SERIAL_8N1);

  pinMode(windSpeedPin, INPUT_PULLUP);
  attachInterrupt(windSpeedINT, readWindSpeed, FALLING);

  pinMode(windDirPin, INPUT_PULLUP);
  attachInterrupt(windDirINT, readWindDir, FALLING);

  interrupts();
}

void readWindSpeed()
{
  // Despite the interrupt being set to FALLING edge, double check the pin is now LOW
  if (((micros() - speedPulse) > DEBOUNCE) && (digitalRead(windSpeedPin) == LOW))
  {
    // Work out time difference between last pulse and now
    speedTime = micros() - speedPulse;

    // Direction pulse should have occurred after the last speed pulse
    if (dirPulse - speedPulse >= 0) directionTime = dirPulse - speedPulse;

    newData = true;
    speedPulse = micros();    // Capture time of the new speed pulse
  }
}

void readWindDir()
{
  if (((micros() - dirPulse) > DEBOUNCE) && (digitalRead(windDirPin) == LOW))
  {
    dirPulse = micros();        // Capture time of direction pulse
  }
}

void calcWindSpeedAndDir()
{
  unsigned long dirPulse_, speedPulse_;
  unsigned long speedTime_;
  unsigned long directionTime_;
  unsigned long windDirection = 0l, rps = 0l, mph = 0l;

  // Get snapshot of data into local variables. Note: an interrupt could trigger here
  noInterrupts();
  dirPulse_ = dirPulse;
  speedPulse_ = speedPulse;
  speedTime_ = speedTime;
  directionTime_ = directionTime;
  interrupts();

  // Make speed zero, if the pulse delay is too long
  if (micros() - speedPulse_ > TIMEOUT) speedTime_ = 0ul;

  // The following converts revolutions per 100 seconds (rps) to mph x 100
  if (speedTime_ > 0)
  {
    rps = 100000000 / speedTime_;

    if (1 < rps && rps < 323)
    {
      mph = (-1095 * rps * rps + 29318 * rps * 100 - 14120000) / 1000000;
    }
    else if (323 <= rps && rps < 5436)
    {
      mph = (52 * rps * rps + 21980 * rps * 100 + 110910000) / 1000000;
    }
    else if (5436 <= rps && rps < 6633)
    {
      mph = (1104 * rps * rps - 95685 * rps * 100 + 32987000000) / 1000000;
    }

    // Remove the possibility of negative speed
    if (mph < 0l) mph = 0l;

    // only calculate direction if we have wind
    if (mph >= 0)
    {
      // Calculate direction from captured pulse times
      windDirection = ( ( (directionTime_ * 360) / speedTime_) + DIRECTION_OFFSET) % 360;
    }
    else
    {
      mph = 0;
    }

    // Output to Weatherduino
   // analogWrite(windDirOutPin, map(windDirection, 0, 360, 0, 255));

    if (debug)
    {
      Serial.print("speedTime_: "); Serial.println(speedTime_);
      Serial.print("rps: "); Serial.println(rps);
      Serial.print("dir: "); Serial.println(windDirection);
      Serial.print("mph: "); Serial.println(mph);
      Serial.println("");
    }
  }
}

void loop()
{
  int i;
  const unsigned int LOOP_DELAY = 50;
  const unsigned int LOOP_TIME = TIMEOUT / LOOP_DELAY;

  i = 0;
  // If there is new data, process it, otherwise wait for LOOP_TIME to pass
  while ((newData != true) && (i < LOOP_TIME))
  {
    i++;
    delayMicroseconds(LOOP_DELAY);
  }

  calcWindSpeedAndDir();    // Process new data
  newData = false;
}
