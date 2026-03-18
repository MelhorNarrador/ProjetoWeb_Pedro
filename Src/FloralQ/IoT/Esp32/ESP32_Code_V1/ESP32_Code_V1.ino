#include <WiFi.h>
#include <HTTPClient.h>
#include "secrets.h"

const char* ssid = WIFI_SSID;
const char* password = WIFI_PASSWORD;

const char* serverReading  = "http://" SERVER_IP ":8000/Backend/API/insert_reading.php";
const char* serverRegister = "http://" SERVER_IP ":8000/Backend/API/register_device.php";

int dryValue = 2500;
int wetValue = 700;

unsigned long lastRead = 0;
const long interval = 300000;

const String DEVICE_CODE = "ESP32_001";
const bool IS_PROFESSIONAL = true;

void registarDevice() {
    HTTPClient http;
    http.begin(serverRegister);
    http.addHeader("Content-Type", "application/json");

    String payload = "{";
    payload += "\"device_code\":\"" + DEVICE_CODE + "\",";
    payload += "\"is_professional\":" + String(IS_PROFESSIONAL ? "true" : "false");
    payload += "}";

    int code = http.POST(payload);

    Serial.print("Register response: ");
    Serial.println(code);

    http.end();
}

void enviarLeitura() {
    int value   = analogRead(34);
    int percent = map(value, dryValue, wetValue, 0, 100);
    percent     = constrain(percent, 0, 100);

    Serial.print("Raw: ");
    Serial.print(value);
    Serial.print(" | Humidity: ");
    Serial.print(percent);
    Serial.println("%");

    String json = "{";
    json += "\"device_code\":\"" + DEVICE_CODE + "\","; 
    json += "\"moisture\":" + String(percent);
    json += "}";

    Serial.print("Sending: ");
    Serial.println(json);

    HTTPClient http;
    http.begin(serverReading);
    http.addHeader("Content-Type", "application/json");

    int code = http.POST(json);

    Serial.print("HTTP Response: ");
    Serial.println(code);

    if (code != 200) {
        Serial.println("Retrying...");
        delay(2000);
        int retryCode = http.POST(json);
        Serial.print("Retry Response: ");
        Serial.println(retryCode);
    }

    http.end();
}

void setup() {
    Serial.begin(115200);

    WiFi.begin(ssid, password);
    Serial.print("Connecting to WiFi");

    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }

    Serial.println("\nConnected!");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
    registarDevice();
}

void loop() {
    unsigned long now = millis();

    if (now - lastRead >= interval) {
        lastRead = now;

        if (WiFi.status() == WL_CONNECTED) {
            enviarLeitura();
        } else {
            Serial.println("WiFi not connected");
        }
    }
}
