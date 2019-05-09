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
    now = datetime.datetime.now()
    if now.strftime('%d') != dateJour:
        conn = mysql.connector.connect(host="maison.lithium", user="pi", password="66446644", database="solaire_v1")
        cursor = conn.cursor()
        dateJour = now.strftime('%d')
        dateveille = now - datetime.timedelta(1)
        dateveille1 = dateveille.strftime('%Y-%m-%d')
        timestampveille = time.mktime(datetime.datetime.strptime(dateveille1, "%Y-%m-%d").timetuple())
        insertline = "insert into solaire_v1.puissance(timestamp, watt) VALUES('%s', '%s')"
        var = (timestampveille, float(compteur_principal) / 1000.0)
        new_line = insertline % var
        cursor.execute(new_line)
        dateJour = now.strftime('%d')
        compteur_principal = 1
        compteur = 1
        conn.commit()
        conn.close()
    else:
        compteur_principal = compteur_principal + 1
    compteur = compteur + 1
    if compteur == 100:
        conn = mysql.connector.connect(host="maison.lithium", user="pi", password="66446644", database="solaire_v1")
        cursor = conn.cursor()
        timestamp = time.mktime(datetime.datetime.now().timetuple())
        insertline = "insert into solaire_v1.production(timestamp, watt, watt_totale) VALUES('%s',100,'%s')"
        var = (timestamp, float(compteur_principal) / 1000.0)
        new_line = insertline % var
        cursor.execute(new_line)
        compteur = 0
        conn.commit()
        conn.close()


GPIO.setmode(GPIO.BCM)
GPIO.setup(18, GPIO.IN, pull_up_down=GPIO.PUD_UP)
GPIO.add_event_detect(18, GPIO.FALLING, callback=cb_compteur_principal, bouncetime=70)

while True:
    time.sleep(0.1)
