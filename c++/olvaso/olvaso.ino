#include <WiFi.h>
#include <Wire.h>
#include <Adafruit_PN532.h>

////////////////////////////////////
///////   PIN beállítások   ////////
////////////////////////////////////
const String SSID= "Test12345";
const String PASS= "123456789";
// Jobb oldal
const int buzzer = 5; // piezzo csopogó - piros vagy fehér, még nem döntöttem el
const int lock = 17;  // elektromos zár - lila
const int accessTrue = 18; // zöld
const int accessFalse = 19; // piros
const int SCL = 22; //PN532 - sárga
const int SDA = 23; //PN532 - zöld

// Bal oldal
const int WifiGreen = 25;
const int WifiYellow = 26;
const int WifiRed = 27;  


////////////////////////////////////
///////  WIFI beállítások   ////////
////////////////////////////////////

class WifiManager {
  private:
    String ssid;
    String password;

  public:
    WifiManager(String name, String pass) {
      ssid = name;
      password = pass;
    }
    int state;

    void Begin() {
      Serial.println();
      Serial.println("Csatlakozas WiFi-hez...");

      WiFi.mode(WIFI_STA);
      WiFi.begin(ssid.c_str(), password.c_str());

      int tries = 0;

      while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
        tries++;

        if (tries > 40) {
          Serial.println();
          Serial.println("Nem sikerult csatlakozni");
          state = 3;
          return;
        }
      }

      Serial.println();
      Serial.println("Csatlakozva!");
      state = 2;

      Serial.print("IP cim: ");
      Serial.println(WiFi.localIP());

      Serial.print("RSSI: ");
      Serial.println(WiFi.RSSI());

      if (IsConnected()) {
        state = 1;
      }
    }

    bool IsConnected() {
      return WiFi.status() == WL_CONNECTED;
    }

    String GetIpAddress() {
      if (IsConnected()) {
        return WiFi.localIP().toString();
      }
      return "";
    }
};

////////////////////////////////////
///////      Kimenetek      ////////
////////////////////////////////////
class OutputManager{
  public:
    void NetWorkLED(int status);
    void Access(bool success);
    OutputManager(){}

  void NetWorkLED(int status){
    switch(status){
      case 1:
        digitalWrite(WifiGreen, HIGH);
        digitalWrite(WifiYellow, LOW);
        digitalWrite(WifiRed, LOW);
        break;
      case 2:
        digitalWrite(WifiGreen, LOW);
        digitalWrite(WifiYellow, HIGH);
        digitalWrite(WifiRed, LOW);
        break;
      case 3:
        digitalWrite(WifiGreen, LOW);
        digitalWrite(WifiYellow, LOW);
        digitalWrite(WifiRed, HIGH);
        break;
      default:
        digitalWrite(WifiGreen, HIGH);
        digitalWrite(WifiYellow, HIGH);
        digitalWrite(WifiRed, HIGH);
        break;
    }
  }
  void Access(bool success){
    if(success){
      digitalWrite(lock, HIGH); //Zár
      digitalWrite(accessTrue, HIGH); // zöld led
      tone(buzzer, 1000);
      delay(1500);
      digitalWrite(lock, LOW); //Zár
      digitalWrite(accessTrue, LOW); // zöld led
      noTone(buzzer);
    }
    else{
      digitalWrite(lock, LOW); //Zár
      digitalWrite(accessFalse, HIGH); // zöld led
      tone(buzzer, 250, 500);
      delay(500);
      tone(buzzer, 250, 500);
      digitalWrite(accessFalse, LOW); // zöld led
      noTone(buzzer);
    }
  }

};
////////////////////////////////////
///////       PN532         ////////
////////////////////////////////////

class PN {
  private:
    Adafruit_PN532 nfc;

  public:
    PN() : nfc(-1, -1) {}

    void Begin() {
      Wire.begin(SDA, SCL);
      nfc.begin();

      uint32_t versiondata = nfc.getFirmwareVersion();
      if (!versiondata) {
        Serial.println("PN532 nem talalhato.");
        return;
      }

      Serial.println("PN532 csatlakoztatva.");

      nfc.SAMConfig();
      Serial.println("PN532 keszen all olvasasra.");
    }

    String ReadTag() {
      uint8_t uid[] = {0, 0, 0, 0, 0, 0, 0};
      uint8_t uidLength;

      bool success = nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, uid, &uidLength, 50);

      if (!success) {
        return "";
      }

      String result = "";

      for (uint8_t i = 0; i < uidLength; i++) {
        if (uid[i] < 0x10) {
          result += "0";
        }
        result += String(uid[i], HEX);
      }

      result.toUpperCase();
      return result;
    }
};


////////////////////////////////////
///////   Saját beállítás   ////////
////////////////////////////////////
class Reader {
  private:
    int id;
    String name;
    bool active;
    String from;
    String to;
    int role;

  public:
    Reader() {}
    Reader(String INid, String INname, String INactive, String INfrom, String INto, String INrole){
      id = INid.toInt();
      name = INname;
      active = INactive == "1" ? true : false;
      from = INfrom;
      to = INto;
      role = INrole.toInt();
    }

  void GetData(){

  }
  void Logic(){

  }
};

Pn pn;
Reader thisReader;

void setup() {
  Serial.begin(115200);
  delay(1000);


  Serial.println("Pinek beallitasa...");

  pinMode(buzzer, OUTPUT);
  pinMode(accessTrue, OUTPUT); 
  pinMode(accessFalse, OUTPUT);
  pinMode(lock, OUTPUT);
  pinMode(WifiGreen, OUTPUT); 
  pinMode(WifiYellow, OUTPUT); 
  pinMode(WifiRed, OUTPUT); 

  Serial.println("Pinek beallitasa sikeres.");

  WifiManager wifimanager(SSID,PASS);
  wifimanager.Begin();

  pn.Begin();

}

void loop() {

}