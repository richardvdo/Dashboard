#!/usr/bin/python

import RPi.GPIO as GPIO
import time
import datetime
import mysql.connector

compteur_principal = 0.0
compteur = 0
dateJour = 0


def cb_compteur_principal(channel):
    global compteur
    global compteur_principal
    global dateJour
    # log = open("/tmp/log_compteur.log", "a")
    now = datetime.datetime.now()
    heure = now.strftime('%Y-%m-%d %H:%M:%S.%f')
    conn = mysql.connector.connect(host="maison.lithium", user="pi", password="66446644", database="solaire_v1")
    cursor = conn.cursor()
    if now.strftime('%d') != dateJour:
        dateJour = now.strftime('%d')
        dateJourComplete = now.strftime('%Y-%m-%d')
        dateVeille = dateJourComplete - datetime.timedelta(1)
        timestampVeille = int(datetime.datetime.strptime(dateVeille, '%Y-%m-%d').strftime("%s"))
        insertline = "insert into puissance.record(timestamp, watt) VALUES('%s', '%s')"
        var = (timestampVeille, float(compteur_principal) / 1000.0)
        new_line = (insertline) % (var)
        print(new_line)
        cursor.execute(new_line)
        compteur_principal = 1
        # log.write('Nouveau jour %s \n') % (int(dateJour))
    else:
        compteur_principal = compteur_principal + 1
    compteur = compteur + 1
    if compteur >= 100:
        # line = "compteur principal - %s : %s kW\n" % (heure, float(compteur_principal/1000))
        # log.write(line)
        timestamp = int(datetime.datetime.strptime(heure, '%Y-%m-%d %H:%M:%S.%f').strftime("%s"))
        insertline = "insert into production.record(timestamp, watt, watt_totale) VALUES('%s',100,'%s')"
        var = (timestamp, float(compteur_principal) / 1000.0)
        new_line = (insertline) % (var)
        print(new_line)
        cursor.execute(new_line)
        compteur = 0
    # log.close()
    conn.commit()
    conn.close(

        GPIO.setmode(GPIO.BCM)
    GPIO.setup(18, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.add_event_detect(18, GPIO.FALLING, callback=cb_compteur_principal, bouncetime=70)


while True:
    time.sleep(0.1)
