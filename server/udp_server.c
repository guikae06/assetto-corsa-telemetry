#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <winsock2.h>
#include "cJSON.h"

#pragma comment(lib, "ws2_32.lib")

#define PORT 9996
#define BUFLEN 2048

void print_time_ms_to_m_s_ms(double time_ms) {
    int total_ms = (int)(time_ms);
    int minutes = total_ms / 60000;
    int seconds = (total_ms % 60000) / 1000;
    int milliseconds = total_ms % 1000;

    printf("%d:%02d:%03d\n", minutes, seconds, milliseconds);
}

int main(void) {
    WSADATA wsa;
    SOCKET sock;
    struct sockaddr_in server, client;
    int slen = sizeof(client);
    char buf[BUFLEN];

    printf("Starting UDP server on port %d...\n", PORT);

    if (WSAStartup(MAKEWORD(2, 2), &wsa) != 0) {
        printf("WSAStartup failed: %d\n", WSAGetLastError());
        return 1;
    }

    if ((sock = socket(AF_INET, SOCK_DGRAM, 0)) == INVALID_SOCKET) {
        printf("Socket creation failed: %d\n", WSAGetLastError());
        return 1;
    }

    server.sin_family = AF_INET;
    server.sin_addr.s_addr = INADDR_ANY;
    server.sin_port = htons(PORT);

    if (bind(sock, (struct sockaddr*)&server, sizeof(server)) == SOCKET_ERROR) {
        printf("Bind failed: %d\n", WSAGetLastError());
        closesocket(sock);
        WSACleanup();
        return 1;
    }

    printf("Waiting for incoming UDP data...\n");

    while (1) {
        memset(buf, 0, BUFLEN);

        int recv_len = recvfrom(sock, buf, BUFLEN - 1, 0, (struct sockaddr*)&client, &slen);
        if (recv_len == SOCKET_ERROR) {
            printf("recvfrom() failed: %d\n", WSAGetLastError());
            continue;
        }

        buf[recv_len] = '\0';

        cJSON* json = cJSON_Parse(buf);
        if (!json) {
            printf("JSON Parse Error!\n");
            continue;
        }

        cJSON* rpm = cJSON_GetObjectItemCaseSensitive(json, "rpm");
        cJSON* SpeedKMH = cJSON_GetObjectItemCaseSensitive(json, "SpeedKMH");
	cJSON* turbo = cJSON_GetObjectItemCaseSensitive(json, "turbo");
        cJSON* gear = cJSON_GetObjectItemCaseSensitive(json, "gear");
        cJSON* throttle = cJSON_GetObjectItemCaseSensitive(json, "throttle");
        cJSON* brake = cJSON_GetObjectItemCaseSensitive(json, "brake");
        cJSON* lapTime = cJSON_GetObjectItemCaseSensitive(json, "LapTime");
        cJSON* lastLap = cJSON_GetObjectItemCaseSensitive(json, "LastLap");
        cJSON* lapCount = cJSON_GetObjectItemCaseSensitive(json, "LapCount");
        cJSON* trackName = cJSON_GetObjectItemCaseSensitive(json, "trackName");
        cJSON* BestLap = cJSON_GetObjectItemCaseSensitive(json, "BestLap");

        if (cJSON_IsNumber(rpm) && cJSON_IsNumber(gear) && cJSON_IsNumber(throttle) &&
            cJSON_IsNumber(brake) && cJSON_IsNumber(SpeedKMH)) {
            printf("RPM: %.2f\nGear: %d\nThrottle: %.2f\nBrake: %.2f\nSpeed: %.2f km/h\n",
                   rpm->valuedouble, gear->valueint, throttle->valuedouble,
                   brake->valuedouble, SpeedKMH->valuedouble);
        }

        if (cJSON_IsNumber(lapTime) && cJSON_IsNumber(lastLap) && cJSON_IsNumber(lapCount)) {
            printf("LapTime: ");
            print_time_ms_to_m_s_ms(lapTime->valuedouble);
            printf("LastLap: ");
            print_time_ms_to_m_s_ms(lastLap->valuedouble);
            printf("LapCount: %d\n", lapCount->valueint);
        }

        if (cJSON_IsNumber(BestLap)) {
            printf("BestLap: ");
            print_time_ms_to_m_s_ms(BestLap->valuedouble);
        } else {
            printf("BestLap: N/A\n");
        }

        if (cJSON_IsString(trackName) && (trackName->valuestring != NULL)) {
            printf("TrackName: %s\n", trackName->valuestring);
        }

        printf("------------------------------------------------------------\n");

        cJSON_Delete(json);
    }

    closesocket(sock);
    WSACleanup();
    return 0;
}
