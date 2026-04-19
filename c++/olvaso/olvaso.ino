#include <WiFi.h>
#include <Wire.h>
#include <HTTPClient.h>
#include <Adafruit_PN532.h>
#include <Preferences.h>
#include <time.h>
#include <ArduinoJson.h>

////////////////////////////////////
///////   PIN beállítások   ////////
////////////////////////////////////
const String SSID= "Test12345";
const String PASS= "123456789";

// Jobb oldal
const int buzzer = 5; // piezzo csopogó - narancs
const int lock = 17;  // elektromos zár - feher
const int accessTrue = 18; // zöld
const int accessFalse = 19; // lila
const int SCL_pin = 22; //PN532 - sárga
const int SDA_pin = 23; //PN532 - zöld

// Bal oldal
const int WifiGreen = 25; //zöld
const int WifiYellow = 26; // sarga
const int WifiRed = 27; // lila

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

    void GetData();
      //Leszedi az adatokat és írja ki egyelőre Serial-ra, hogy miket lopkodott le ez a kis galád

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
        digitalWrite(accessFalse, HIGH); // piros led
        tone(buzzer, 250, 500);
        delay(500);
        tone(buzzer, 250, 500);
        digitalWrite(accessFalse, LOW); // piros led
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

////////////////////////////////////
///////         Óra         ////////
////////////////////////////////////
class TimeManager {
  private:
    const char* ntpServer;
    long gmtOffsetSec;
    int daylightOffsetSec;
    bool timeValid;

  public:
    TimeManager(const char* server, long gmtOffset, int daylightOffset) {
      ntpServer = server;
      gmtOffsetSec = gmtOffset;
      daylightOffsetSec = daylightOffset;
      timeValid = false;
    }

    void Begin() {
      configTime(gmtOffsetSec, daylightOffsetSec, ntpServer);

      struct tm timeinfo;
      if (getLocalTime(&timeinfo, 10000)) {
        timeValid = true;
        Serial.println("Ido szinkron sikeres.");
        Serial.println(GetDateTimeString());
      } else {
        timeValid = false;
        Serial.println("Nem sikerult NTP idot szinkronizalni.");
      }
    }

    bool IsValid() {
      return timeValid;
    }

    bool Refresh() {
      struct tm timeinfo;
      if (getLocalTime(&timeinfo, 3000)) {
        timeValid = true;
        return true;
      }

      timeValid = false;
      return false;
    }

    int GetHour() {
      struct tm timeinfo;
      if (!getLocalTime(&timeinfo)) {
        timeValid = false;
        return -1;
      }

      timeValid = true;
      return timeinfo.tm_hour;
    }

    int GetMinute() {
      struct tm timeinfo;
      if (!getLocalTime(&timeinfo)) {
        timeValid = false;
        return -1;
      }

      timeValid = true;
      return timeinfo.tm_min;
    }

    int GetSecond() {
      struct tm timeinfo;
      if (!getLocalTime(&timeinfo)) {
        timeValid = false;
        return -1;
      }

      timeValid = true;
      return timeinfo.tm_sec;
    }

    String GetTimeString() {
      struct tm timeinfo;
      if (!getLocalTime(&timeinfo)) {
        timeValid = false;
        return "";
      }

      timeValid = true;

      char buffer[9];
      strftime(buffer, sizeof(buffer), "%H:%M:%S", &timeinfo);
      return String(buffer);
    }

    String GetDateString() {
      struct tm timeinfo;
      if (!getLocalTime(&timeinfo)) {
        timeValid = false;
        return "";
      }

      timeValid = true;

      char buffer[11];
      strftime(buffer, sizeof(buffer), "%Y-%m-%d", &timeinfo);
      return String(buffer);
    }

    String GetDateTimeString() {
      struct tm timeinfo;
      if (!getLocalTime(&timeinfo)) {
        timeValid = false;
        return "";
      }

      timeValid = true;

      char buffer[20];
      strftime(buffer, sizeof(buffer), "%Y-%m-%d %H:%M:%S", &timeinfo);
      return String(buffer);
    }

    int GetCurrentMinutesOfDay() {
      struct tm timeinfo;
      if (!getLocalTime(&timeinfo)) {
        timeValid = false;
        return -1;
      }

      timeValid = true;
      return timeinfo.tm_hour * 60 + timeinfo.tm_min;
    }

    int TimeStringToMinutes(String value) {
      if (value.length() < 5) {
        return -1;
      }

      int colonIndex = value.indexOf(':');
      if (colonIndex < 0) {
        return -1;
      }

      int hour = value.substring(0, colonIndex).toInt();
      int minute = value.substring(colonIndex + 1).toInt();

      if (hour < 0 || hour > 23 || minute < 0 || minute > 59) {
        return -1;
      }

      return hour * 60 + minute;
    }

    bool IsInRange(String from, String to) {
      int current = GetCurrentMinutesOfDay();
      if (current < 0) {
        return false;
      }

      int fromMin = TimeStringToMinutes(from);
      int toMin = TimeStringToMinutes(to);

      if (fromMin < 0 || toMin < 0) {
        return false;
      }

      if (fromMin <= toMin) {
        return current >= fromMin && current <= toMin;
      }

      return current >= fromMin || current <= toMin;
    }
};

////////// Prefs külön, hogy a Reader osztály tudjon rá hivatkozni
static Preferences prefs;

////////////////////////////////////
///////   Saját beállítás   ////////
////////////////////////////////////
class Reader {
  public:
    static int ID;
    static String NAME;
    static bool ACTIVE;
    static String FROM;
    static String TO;
    static int ROLE;

    Reader() {}

    Reader(String INid, String INname, String INactive, String INfrom, String INto, String INrole){
      ID = INid.toInt();
      NAME = INname;
      ACTIVE = INactive == "1" ? true : false;
      FROM = INfrom;
      TO = INto;
      ROLE = INrole.toInt();
    }

    void save() {
      prefs.begin("reader", false);

      prefs.putInt("id", ID);
      prefs.putString("name", NAME);
      prefs.putBool("active", ACTIVE);
      prefs.putString("from", FROM);
      prefs.putString("to", TO);
      prefs.putInt("role", ROLE);

      prefs.end();
    }

    void load() {
      prefs.begin("reader", true);

      ID     = prefs.getInt("id", -1);
      NAME   = prefs.getString("name", "");
      ACTIVE = prefs.getBool("active", false);
      FROM   = prefs.getString("from", "00:00");
      TO     = prefs.getString("to", "23:59");
      ROLE   = prefs.getInt("role", -1);

      prefs.end();
    }

    void Logic() {

    }
};

// Reader statikus adattagok definíciója
int Reader::ID = -1;
String Reader::NAME = "";
bool Reader::ACTIVE = false;
String Reader::FROM = "00:00";
String Reader::TO = "23:59";
int Reader::ROLE = -1;

//////// Linkek
static String GetIdURL = "http://www.szakdolgozat.robin-mizere.hu/newreader?type=0";
static String GetReaderDataURL = "";

/////// Osztály objektumok létrehozása ////////
static WifiManager wifimanager(SSID,PASS);
static PN pn;
static Reader thisReader;
static TimeManager timeManager("pool.ntp.org", 3600, 3600);

void WifiManager::GetData() {
  if (Reader::ID < 1) {
    Serial.println("Ervenytelen Reader ID.");
    return;
  }

  HTTPClient http;

  Serial.println("Getting reader data from server...");
  Serial.println(GetReaderDataURL);

  http.begin(GetReaderDataURL);
  int httpCode = http.GET();

  if (httpCode != 200) {
    Serial.print("HTTP hiba: ");
    Serial.println(httpCode);
    http.end();
    return;
  }

  String payload = http.getString();
  http.end();

  Serial.println("Kapott JSON:");
  Serial.println(payload);

  DynamicJsonDocument doc(768);
  DeserializationError error = deserializeJson(doc, payload);

  if (error) {
    Serial.print("JSON parse hiba: ");
    Serial.println(error.c_str());
    return;
  }

  Reader::ID = doc["id"] | Reader::ID;
  Reader::NAME = doc["name"] | "";
  Reader::ACTIVE = doc["active"] | false;
  Reader::ROLE = doc["role"] | -1;
  Reader::FROM = doc["from"] | "00:00:00";
  Reader::TO = doc["to"] | "23:59:59";

  Serial.println("Reader adatok frissitve:");
  Serial.print("ID: ");
  Serial.println(Reader::ID);
  Serial.print("NAME: ");
  Serial.println(Reader::NAME);
  Serial.print("ACTIVE: ");
  Serial.println(Reader::ACTIVE);
  Serial.print("ROLE: ");
  Serial.println(Reader::ROLE);
  Serial.print("FROM: ");
  Serial.println(Reader::FROM);
  Serial.print("TO: ");
  Serial.println(Reader::TO);
}

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
  timeManager.Begin();
  pn.Begin();

  if (Reader::ID < 1) {
    Reader::ID = wifimanager.GetNewID(GetIdURL);
    thisReader.save();
  }

  GetReaderDataURL = "http://www.szakdolgozat.robin-mizere.hu/readerdata?id=" + String(Reader::ID);

  Serial.println(timeManager.GetDateTimeString());
}

void loop() {
  if (Reader::ACTIVE) {
    wifimanager.GetData();

    if (timeManager.IsInRange(Reader::FROM, Reader::TO)) {
      unsigned long startMs = millis();

      while (millis() - startMs < 300000UL) {
        pn.ReadTag();
      }

      wifimanager.GetData();
    }
    else{
      Serial.println("Out of active time. Waiting...");
      wifimanager.GetData();
      delay(36000);
    }
  }
  else{
    Serial.println("Reader set to inactive. Idling...");
    wifimanager.GetData();
    delay(36000);
  }
}