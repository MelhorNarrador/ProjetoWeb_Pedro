#include <WiFi.h>
#include <HTTPClient.h>
#include <TinyGPSPlus.h>
#include <TFT_eSPI.h>
#include <ArduinoJson.h>
#include "secrets.h"

// Animações geradas pelo conversor (na pasta do sketch)
#include "Newborn_Healthy.h"
#include "Grown_Healthy.h"
#include "Newborn_Dry.h"
#include "Grown_Dry.h"
#include "Newborn_Overwatered.h"
#include "Grown_Overwatered.h"
#include "Newborn_Recently_Watered.h"
#include "Grown_Recently_Watered.h"
#include "Newborn_Sensor_Offline.h"
#include "Grown_Sensor_Offline.h"

// --- Configuração ---
const char*   ssid           = WIFI_SSID;
const char*   password       = WIFI_PASSWORD;
const String  DEVICE_CODE    = "FloralQ-Home-002";
const bool    IS_PROFESSIONAL = true;

const char* serverReading  = "http://" SERVER_IP ":8000/Backend/API/insert_reading.php";
const char* serverRegister = "http://" SERVER_IP ":8000/Backend/API/register_device.php";
const char* serverState    = "http://" SERVER_IP ":8000/Backend/API/get_plant_state.php?device_code=";

const int  DRY_VALUE  = 2500;
const int  WET_VALUE  = 700;
const long INTERVAL   = 300000;   // leitura a cada 5 min
const long WATERED_MS = 60000;    // mostra "recently watered" durante 60 s
const int  FRAME_SPEED_MULTIPLIER = 3;   // multiplica os delays dos frames (1 = velocidade original, 2 = metade da velocidade)

// --- Hardware ---
TFT_eSPI    tft = TFT_eSPI();
TinyGPSPlus gps;

// --- Estado da animação ---
enum State { HEALTHY, DRY, OVERWATERED, RECENTLY_WATERED, OFFLINE };

State         currentState        = OFFLINE;
State         previousState       = OFFLINE;
bool          isGrown             = false;
int           currentFrame        = 0;
unsigned long lastFrameTime       = 0;
unsigned long lastRead            = 0;
unsigned long recentlyWateredEnd  = 0;

// Posição centrada da animação (200x200) no ecrã 240x280
#define IMG_W 180
#define IMG_H 180
#define IMG_X 30   // (240 - 180) / 2
#define IMG_Y 50   // (280 - 180) / 2
#define BG_COLOR 0xEF7D  // cream (#f5f5dc em RGB565)

// --- Struct de animação ---
struct Anim {
    const uint16_t* const* frames;
    const uint16_t*        delays;
    int                    frameCount;
};

Anim getAnim() {
    switch (currentState) {
        case DRY:
            return isGrown ? Anim{grown_dry_frames,                    grown_dry_delays,                    GROWN_DRY_FRAME_COUNT}
                           : Anim{newborn_dry_frames,                   newborn_dry_delays,                   NEWBORN_DRY_FRAME_COUNT};
        case OVERWATERED:
            return isGrown ? Anim{grown_overwatered_frames,             grown_overwatered_delays,             GROWN_OVERWATERED_FRAME_COUNT}
                           : Anim{newborn_overwatered_frames,            newborn_overwatered_delays,            NEWBORN_OVERWATERED_FRAME_COUNT};
        case RECENTLY_WATERED:
            return isGrown ? Anim{grown_recently_watered_frames,        grown_recently_watered_delays,        GROWN_RECENTLY_WATERED_FRAME_COUNT}
                           : Anim{newborn_recently_watered_frames,       newborn_recently_watered_delays,       NEWBORN_RECENTLY_WATERED_FRAME_COUNT};
        case OFFLINE:
            return isGrown ? Anim{grown_sensor_offline_frames,           grown_sensor_offline_delays,           GROWN_SENSOR_OFFLINE_FRAME_COUNT}
                           : Anim{newborn_sensor_offline_frames,          newborn_sensor_offline_delays,          NEWBORN_SENSOR_OFFLINE_FRAME_COUNT};
        case HEALTHY:
        default:
            return isGrown ? Anim{grown_healthy_frames,                 grown_healthy_delays,                 GROWN_HEALTHY_FRAME_COUNT}
                           : Anim{newborn_healthy_frames,                newborn_healthy_delays,                NEWBORN_HEALTHY_FRAME_COUNT};
    }
}

void setState(State s, bool grown) {
    if (s == currentState && grown == isGrown) return;
    previousState = currentState;
    currentState  = s;
    isGrown       = grown;
    currentFrame  = 0;
}

// --- Display: desenha o frame atual ---
void drawFrame() {
    Anim anim = getAnim();
    const uint16_t* frameData = (const uint16_t*)pgm_read_ptr(&anim.frames[currentFrame]);
    tft.pushImage(IMG_X, IMG_Y, IMG_W, IMG_H, frameData);
}

// --- HTTP: regista o device no arranque ---
void registarDevice() {
    HTTPClient http;
    http.begin(serverRegister);
    http.addHeader("Content-Type", "application/json");

    String payload = "{";
    payload += "\"device_code\":\"" + DEVICE_CODE + "\",";
    payload += "\"is_professional\":" + String(IS_PROFESSIONAL ? "true" : "false");
    payload += "}";

    int code = http.POST(payload);
    Serial.println("Register: " + String(code));
    http.end();
}

// --- HTTP: envia leitura de humidade + GPS ---
void enviarLeitura() {
    int value   = analogRead(34);
    int percent = map(value, DRY_VALUE, WET_VALUE, 0, 100);
    percent     = constrain(percent, 0, 100);

    Serial.println("Moisture: " + String(percent) + "%");

    String json = "{";
    json += "\"device_code\":\"" + DEVICE_CODE + "\",";
    json += "\"moisture\":" + String(percent);
    if (gps.location.isValid()) {
        json += ",\"latitude\":"  + String(gps.location.lat(), 6);
        json += ",\"longitude\":" + String(gps.location.lng(), 6);
    }
    json += "}";

    HTTPClient http;
    http.begin(serverReading);
    http.addHeader("Content-Type", "application/json");
    int code = http.POST(json);
    Serial.println("Reading: " + String(code));
    http.end();
}

// --- HTTP: pede estado ao backend e atualiza animação ---
void atualizarEstado() {
    HTTPClient http;
    http.begin(String(serverState) + DEVICE_CODE);
    int code = http.GET();

    if (code == 200) {
        StaticJsonDocument<128> doc;
        deserializeJson(doc, http.getString());

        const char* stateStr = doc["state"] | "offline";
        bool grown           = doc["is_grown"] | false;

        State newState;
        if      (strcmp(stateStr, "dry")         == 0) newState = DRY;
        else if (strcmp(stateStr, "overwatered")  == 0) newState = OVERWATERED;
        else if (strcmp(stateStr, "offline")      == 0) newState = OFFLINE;
        else                                             newState = HEALTHY;

        // Transição seco → saudável significa que regou
        if (previousState == DRY && newState == HEALTHY) {
            setState(RECENTLY_WATERED, grown);
            recentlyWateredEnd = millis() + WATERED_MS;
        } else {
            setState(newState, grown);
        }

        Serial.println("State: " + String(stateStr) + " grown=" + String(grown));
    } else {
        Serial.println("State fetch failed: " + String(code));
        setState(OFFLINE, isGrown);
    }

    http.end();
}

// --- Setup ---
void setup() {
    Serial.begin(115200);
    Serial2.begin(9600, SERIAL_8N1, 16, 17);

    // Display
    tft.init();
    tft.setRotation(0);
    tft.setSwapBytes(true);   // RGB565 vem em little-endian do PROGMEM; o display espera big-endian
    tft.fillScreen(BG_COLOR);

    // WiFi
    WiFi.begin(ssid, password);
    Serial.print("Connecting");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nConnected: " + WiFi.localIP().toString());

    registarDevice();

    // Primeira leitura imediata (não espera 5 min)
    enviarLeitura();
    atualizarEstado();
    lastRead = millis();
}

// --- Loop ---
void loop() {
    // GPS: processa dados em background
    while (Serial2.available() > 0) {
        gps.encode(Serial2.read());
    }

    unsigned long now = millis();

    // Leitura periódica a cada 5 min
    if (now - lastRead >= INTERVAL) {
        lastRead = now;
        if (WiFi.status() == WL_CONNECTED) {
            enviarLeitura();
            atualizarEstado();
        } else {
            setState(OFFLINE, isGrown);
        }
    }

    // Após 60 s em recently_watered, volta a healthy
    if (currentState == RECENTLY_WATERED && now >= recentlyWateredEnd) {
        setState(HEALTHY, isGrown);
    }

    // Animação frame a frame (não bloqueia o loop)
    Anim anim = getAnim();
    if (now - lastFrameTime >= anim.delays[currentFrame] * FRAME_SPEED_MULTIPLIER) {
        lastFrameTime = now;
        drawFrame();
        currentFrame = (currentFrame + 1) % anim.frameCount;
    }
}
