#!/usr/bin/python

import RPi.GPIO as GPIO
import time
import datetime
import mysql.connector

compteur_principal = 0.0
compteur=0
dateJour=0


def cb_compteur_principal(channel):
    global compteur
    global compteur_principal
    global dateJour
    log = open("/tmp/log_compteur.log", "a")
    now = datetime.datetime.now()
    heure = now.strftime('%Y-%m-%d %H:%M:%S.%f')
    if now.strftime('%d') != dateJour:
        dateJour = now.strftime('%d')
        compteur_principal = 1
        log.write('Nouveau jour %s \n') % (int(dateJour))
    else:
        compteur_principal = compteur_principal + 1
    compteur = compteur + 1
    if compteur >= 100:
        line = "compteur principal - %s : %s kW\n" % (heure, float(compteur_principal/1000))
        log.write(line)
        # conn = mysql.connector.connect(host="maison.lithium", user="pi", password="66446644", database="XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX")
        # cursor = conn.cursor()
        # compteur = 0
        # var = (sensor, sonde[i][1], sonde[i][2], (float(temp_string) / 1000.0))
        # new_line = (sensor_line) % (var)
        # # print (new_line)
        # cursor.execute(new_line)
        # conn.commit()
        # conn.close()
    log.close()


GPIO.setmode(GPIO.BCM)
GPIO.setup(18, GPIO.IN, pull_up_down=GPIO.PUD_UP)
GPIO.add_event_detect(18, GPIO.FALLING, callback=cb_compteur_principal, bouncetime=70)

while True:
    time.sleep(0.1)
