#include <WiFi.h>
#include <Wire.h>
#include <HTTPClient.h>
#include <Adafruit_PN532.h>
#include <Preferences.h>

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
const int SCL_pin = 22; //PN532 - sárga
const int SDA_pin = 23; //PN532 - zöld

// Bal oldal
const int WifiGreen = 25;
const int WifiYellow = 26;
const int WifiRed = 27;  



static int ID = -1;
static int ROLE = -1;
static bool ACTIVE = false;




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
    int GetNewID(String url) {
      HTTPClient http;

      http.begin(url);
      int httpCode = http.GET();

      if (httpCode != 200) {
        http.end();
        return -1;
      }

      String payload = http.getString();
      http.end();

      payload.trim();
      return payload.toInt();
    }
    
    //Reader GetData{ minden adatot lekér ami a reader osztálynak kell és visszatér egy Reader elemmel.

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
      Wire.begin(SDA_pin, SCL_pin);
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


////////// Prefs külön, hogy a Reader osztály tudjon rá hivatkozni

static Preferences prefs;


////////////////////////////////////
///////   Saját beállítás   ////////
////////////////////////////////////
class Reader {
  static int ID = -1;
  static String NAME = "";
  static bool ACTIVE = false;
  static String FROM = "00:00";
  static String TO = "23:59";
  static int ROLE = -1;

  public:
    Reader() {}
    Reader(String INid, String INname, String INactive, String INfrom, String INto, String INrole){
      ID = INid.toInt();
      NAME = INname;
      ACTIVE = INactive == "1" ? true : false;
      FROM = INfrom;
      TO = INto;
      ROLE = INrole.toInt();
    }
    /////// config Mentése
    void save() {
      prefs.begin("reader", false);

      prefs.putInt("id", id);
      prefs.putString("name", name);
      prefs.putBool("active", active);
      prefs.putString("from", from);
      prefs.putString("to", to);
      prefs.putInt("role", role);

      prefs.end();
    }

    ///// Config Betöltése
    void load() {
      prefs.begin("reader", true);   // true = read-only

      id     = prefs.getInt("id", 0);
      name   = prefs.getString("name", "");
      active = prefs.getBool("active", false);
      from   = prefs.getString("from", "00:00");
      to     = prefs.getString("to", "23:59");
      role   = prefs.getInt("role", 0);

      prefs.end();
    }


    void Logic(){

    }
};

/////// Osztály objektumok létrehozása ////////
static WifiManager wifimanager(SSID,PASS);
static PN pn;
static Reader thisReader;

static String GetIdURL = "http://www.szakdolgozat.robin-mizere.hu/newreader?type=0";
static String GetReaderDataURL = "http://www.szakdolgozat.robin-mizere.hu/readerdata?id="+String(ID);

void setup() {
  Serial.begin(115200);
  delay(1000);
  thisReader.load();

  Serial.println("Pinek beallitasa...");

  pinMode(buzzer, OUTPUT);
  pinMode(accessTrue, OUTPUT); 
  pinMode(accessFalse, OUTPUT);
  pinMode(lock, OUTPUT);
  pinMode(WifiGreen, OUTPUT); 
  pinMode(WifiYellow, OUTPUT); 
  pinMode(WifiRed, OUTPUT); 

  Serial.println("Pinek beallitasa sikeres.");

  
  wifimanager.Begin();
  
  if(ID < 1){
    ID = wifimanager.GetNewID(GetIdURL);
    thisReader.id = ID;
    thisReader.save();
    GetReaderDataURL = "http://www.szakdolgozat.robin-mizere.hu/readerdata?id="+String(ID);
  }
  
  pn.Begin();

}

void loop() {
  if(ACTIVE)

}